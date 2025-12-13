"""
Multi-Tenant Face Enrollment Service.

Handles face enrollment and identification using dynamic tenant database connections.
"""

from typing import Dict, List, Optional

import numpy as np
from fastapi import HTTPException, UploadFile

from services import recognition
from services.database import tenant_manager


def _ensure_single_face_encoding(encoding: List[float]) -> List[float]:
    """Validate encoding is 512-dimensional ArcFace vector."""
    if len(encoding) != 512:
        raise HTTPException(
            status_code=500,
            detail=f"Encoding length mismatch; expected 512-d ArcFace, got {len(encoding)}"
        )
    return encoding


async def enroll_face(
    tenant_id: int,
    user_id: int,
    name: str,
    file: UploadFile,
) -> Dict[str, object]:
    """
    Enroll a new face for a user in a tenant database.
    
    Args:
        tenant_id: Tenant identifier
        user_id: User ID in tenant database (references user_{tenant_id}.id)
        name: Label/name for this enrollment
        file: Image file containing the face
        
    Returns:
        Dict with enrollment details and count
    """
    if not name:
        raise HTTPException(status_code=400, detail="Name is required")
    
    # Validate tenant exists
    config = await tenant_manager.get_tenant_config(tenant_id)
    if not config:
        raise HTTPException(status_code=404, detail=f"Tenant {tenant_id} not found")
    
    # Encode face from image
    encoding = await recognition.encode_image(file)
    validated_encoding = _ensure_single_face_encoding(encoding)
    
    # Store in tenant database
    result = await tenant_manager.add_enrollment(
        tenant_id=tenant_id,
        user_id=user_id,
        label=name,
        face_encoding=validated_encoding,
    )
    
    # Get updated count
    enrollments = await tenant_manager.get_enrollments(tenant_id)
    
    return {
        "stored": result,
        "count": len(enrollments),
        "tenant_id": tenant_id,
    }


async def identify_face(
    tenant_id: int,
    file: UploadFile,
    threshold: float = recognition.DEFAULT_THRESHOLD,
) -> Dict[str, object]:
    """
    Identify a face against all enrollments in a tenant database.
    
    Args:
        tenant_id: Tenant identifier
        file: Image file containing the face to identify
        threshold: Maximum distance threshold for a match
        
    Returns:
        Dict with match result, name, distance, and bounding box
    """
    # Get enrollments from cache/database
    enrollments = await tenant_manager.get_enrollments(tenant_id)
    
    if not enrollments:
        raise HTTPException(
            status_code=404,
            detail=f"No enrollments available for tenant {tenant_id}"
        )
    
    # Encode the input face
    encoding, bbox = await recognition.encode_image_with_box(file)
    source = np.array(encoding, dtype=float)
    
    best_match: Optional[Dict] = None
    best_distance: float = float("inf")
    skipped = 0
    
    for enrollment in enrollments:
        target = np.array(enrollment["encoding"], dtype=float)
        
        if len(target) != len(source):
            skipped += 1
            continue
        
        # Cosine distance
        distance = float(1.0 - float(np.dot(source, target)))
        
        if distance < best_distance:
            best_distance = distance
            best_match = enrollment
    
    if best_distance == float("inf"):
        if skipped:
            raise HTTPException(
                status_code=400,
                detail="No compatible enrollments. Re-enroll faces using current model (512-d).",
            )
        raise HTTPException(
            status_code=404,
            detail=f"No enrollments available for tenant {tenant_id}"
        )
    
    return {
        "match": best_distance <= threshold,
        "name": best_match["label"] if best_match else None,
        "user_id": best_match["user_id"] if best_match else None,
        "enrollment_id": best_match["id"] if best_match else None,
        "distance": best_distance,
        "threshold": threshold,
        "count": len(enrollments),
        "bbox": bbox,
        "tenant_id": tenant_id,
    }


async def list_enrollments(tenant_id: int) -> List[Dict[str, object]]:
    """
    List all enrollments for a tenant.
    
    Args:
        tenant_id: Tenant identifier
        
    Returns:
        List of enrollment records (without encoding data)
    """
    enrollments = await tenant_manager.get_enrollments(tenant_id)
    
    return [
        {
            "id": e["id"],
            "user_id": e["user_id"],
            "name": e["label"],
            "created_at": e["created_at"],
        }
        for e in enrollments
    ]


async def delete_enrollment(tenant_id: int, enrollment_id: int) -> Dict[str, object]:
    """
    Delete an enrollment by ID.
    
    Args:
        tenant_id: Tenant identifier
        enrollment_id: Enrollment ID to delete
        
    Returns:
        Dict with deletion status
    """
    deleted = await tenant_manager.delete_enrollment(tenant_id, enrollment_id)
    
    if not deleted:
        raise HTTPException(status_code=404, detail="Enrollment not found")
    
    enrollments = await tenant_manager.get_enrollments(tenant_id)
    
    return {
        "deleted": enrollment_id,
        "count": len(enrollments),
        "tenant_id": tenant_id,
    }


async def delete_enrollment_by_name(tenant_id: int, name: str) -> Dict[str, object]:
    """
    Delete an enrollment by label/name.
    
    Args:
        tenant_id: Tenant identifier
        name: Enrollment label to delete
        
    Returns:
        Dict with deletion status
    """
    deleted = await tenant_manager.delete_enrollment_by_label(tenant_id, name)
    
    if not deleted:
        raise HTTPException(status_code=404, detail="Enrollment not found")
    
    enrollments = await tenant_manager.get_enrollments(tenant_id)
    
    return {
        "deleted": name,
        "count": len(enrollments),
        "tenant_id": tenant_id,
    }
