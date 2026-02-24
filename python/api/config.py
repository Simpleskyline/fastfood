"""
config.py – App settings loaded from .env
"""
from functools import lru_cache
from pydantic_settings import BaseSettings
from typing import Optional


class Settings(BaseSettings):
    db_host:     str = "localhost"
    db_port:     int = 3306
    db_name:     str = "fastfood"
    db_user:     str = "root"
    db_pass:     str = ""
    jwt_secret:  str = "change_me_in_production_please"
    jwt_expire_hours: int = 24
    cors_origins: str = "http://localhost:5500,http://127.0.0.1:5500,http://localhost:3000"

    # Google OAuth
    google_client_id:     Optional[str] = None
    google_client_secret: Optional[str] = None
    google_redirect_uri:  str = "http://localhost:8000/api/auth/google/callback"

    # Email (SMTP for password reset)
    smtp_host:  Optional[str] = None
    smtp_port:  int = 587
    smtp_user:  Optional[str] = None
    smtp_pass:  Optional[str] = None
    smtp_from:  Optional[str] = None

    # Frontend URL (for redirect after Google login / password reset links)
    frontend_url: str = "http://localhost:5500"

    @property
    def cors_origins_list(self):
        return [o.strip() for o in self.cors_origins.split(",")]

    class Config:
        env_file = ".env"


@lru_cache
def get_settings():
    return Settings()
