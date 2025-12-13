"""
Configuration loader for Face Recognition Service.

Loads environment variables from .env file.
"""

import os
from pathlib import Path
from typing import Optional

from dotenv import load_dotenv

# Load .env file from project root
ENV_PATH = Path(__file__).resolve().parent.parent / ".env"
load_dotenv(ENV_PATH)


class Settings:
    """Application settings loaded from environment variables."""
    
    # Gateway Database
    DB_HOST: str = os.getenv("DB_HOST", "127.0.0.1")
    DB_PORT: int = int(os.getenv("DB_PORT", "3306"))
    DB_DATABASE: str = os.getenv("DB_DATABASE", "sekolah_gateway")
    DB_USERNAME: str = os.getenv("DB_USERNAME", "root")
    DB_PASSWORD: str = os.getenv("DB_PASSWORD", "")
    
    # Redis Cache
    REDIS_HOST: str = os.getenv("REDIS_HOST", "127.0.0.1")
    REDIS_PORT: int = int(os.getenv("REDIS_PORT", "6379"))
    REDIS_DB: int = int(os.getenv("REDIS_DB", "0"))
    REDIS_PASSWORD: Optional[str] = os.getenv("REDIS_PASSWORD") or None
    
    # Cache TTL (seconds)
    TENANT_CACHE_TTL: int = int(os.getenv("TENANT_CACHE_TTL", "300"))  # 5 minutes
    ENCODING_CACHE_TTL: int = int(os.getenv("ENCODING_CACHE_TTL", "60"))  # 1 minute


settings = Settings()
