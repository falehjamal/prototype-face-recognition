"""
Face Recognition Service - Multi-Tenant API

FastAPI application for face recognition with multi-tenant database support.
"""

from contextlib import asynccontextmanager

from fastapi import Depends, FastAPI, File, Form, UploadFile
from fastapi.middleware.cors import CORSMiddleware

from models.recognition_request import FaceCompareRequest
from services import recognition
from services import enrollment
from services.database import tenant_manager


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan handler - initialize and cleanup resources."""
    # Startup: Initialize database connections
    await tenant_manager.initialize()
    yield
    # Shutdown: Close all connections
    await tenant_manager.close()


app = FastAPI(
    title="Face Recognition Service",
    description="Multi-tenant face recognition API with Redis caching",
    version="2.0.0",
    lifespan=lifespan,
)

# Allow all origins (CORS)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
    expose_headers=["*"],
)


# =========================================
# Basic Face Operations (No Database)
# =========================================

@app.post("/encode")
async def encode(file: UploadFile = File(...)):
    """Encode a face image to a 512-dimensional vector."""
    encoding = await recognition.encode_image(file)
    return {"encoding": encoding}


@app.post("/compare")
async def compare(
    file: UploadFile = File(...),
    payload: FaceCompareRequest = Depends(FaceCompareRequest.as_form),
):
    """Compare a face against a provided encoding."""
    return await recognition.compare_face(file, payload.encoding, payload.threshold)


@app.post("/detect")
async def detect(file: UploadFile = File(...)):
    """Detect faces in an image and return bounding boxes."""
    faces = await recognition.detect_faces(file)
    return {"faces": faces}


# =========================================
# Multi-Tenant Enrollment Operations
# =========================================

@app.post("/enroll")
async def enroll(
    tenant_id: int = Form(...),
    user_id: int = Form(...),
    name: str = Form(...),
    file: UploadFile = File(...),
):
    """
    Enroll a new face for a user in a tenant database.
    
    - **tenant_id**: Tenant identifier
    - **user_id**: User ID in tenant database (references user_{tenant_id}.id)
    - **name**: Label/name for this enrollment
    - **file**: Image file containing the face
    """
    return await enrollment.enroll_face(tenant_id, user_id, name, file)


@app.post("/identify")
async def identify(
    tenant_id: int = Form(...),
    file: UploadFile = File(...),
    threshold: float = Form(recognition.DEFAULT_THRESHOLD),
):
    """
    Identify a face against all enrollments in a tenant database.
    
    - **tenant_id**: Tenant identifier
    - **file**: Image file containing the face to identify
    - **threshold**: Maximum distance threshold for a match (default: 0.6)
    """
    return await enrollment.identify_face(tenant_id, file, threshold)


@app.get("/enrollments/{tenant_id}")
async def get_enrollments(tenant_id: int):
    """
    List all enrollments for a tenant.
    
    - **tenant_id**: Tenant identifier
    """
    data = await enrollment.list_enrollments(tenant_id)
    return {"enrollments": data, "tenant_id": tenant_id}


@app.delete("/enrollments/{tenant_id}/{enrollment_id}")
async def delete_enrollment_by_id(tenant_id: int, enrollment_id: int):
    """
    Delete an enrollment by ID.
    
    - **tenant_id**: Tenant identifier
    - **enrollment_id**: Enrollment ID to delete
    """
    return await enrollment.delete_enrollment(tenant_id, enrollment_id)


@app.delete("/enrollments/{tenant_id}/name/{name}")
async def delete_enrollment_by_name(tenant_id: int, name: str):
    """
    Delete an enrollment by label/name.
    
    - **tenant_id**: Tenant identifier
    - **name**: Enrollment label to delete
    """
    return await enrollment.delete_enrollment_by_name(tenant_id, name)


# =========================================
# Health Check
# =========================================

@app.get("/health")
async def health():
    """Health check endpoint."""
    return {"status": "ok", "version": "2.0.0"}
