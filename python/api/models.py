"""
models.py – Pydantic request/response models
"""
from pydantic import BaseModel, EmailStr, field_validator
from typing import Optional, List
from enum import Enum


# ── Auth ──────────────────────────────────────────────────────────────────────

class RegisterRequest(BaseModel):
    first_name: str
    last_name: str
    username: str
    email: EmailStr
    password: str
    confirm_password: str
    role: str = "customer"

    @field_validator("password")
    @classmethod
    def password_length(cls, v):
        if len(v) < 6:
            raise ValueError("Password must be at least 6 characters")
        return v

    @field_validator("confirm_password")
    @classmethod
    def passwords_match(cls, v, info):
        if "password" in info.data and v != info.data["password"]:
            raise ValueError("Passwords do not match")
        return v

    @field_validator("role")
    @classmethod
    def valid_role(cls, v):
        if v not in ("customer", "admin"):
            raise ValueError("Role must be customer or admin")
        return v


class LoginRequest(BaseModel):
    email: EmailStr
    password: str


class TokenResponse(BaseModel):
    success: bool = True
    token: str
    user: dict


# ── Profile ───────────────────────────────────────────────────────────────────

class UpdateProfileRequest(BaseModel):
    first_name: Optional[str] = None
    last_name: Optional[str] = None
    phone: Optional[str] = None
    location: Optional[str] = None


# ── Orders ────────────────────────────────────────────────────────────────────

class CartItem(BaseModel):
    food_id: int
    quantity: int

    @field_validator("quantity")
    @classmethod
    def qty_positive(cls, v):
        if v <= 0:
            raise ValueError("Quantity must be positive")
        return v


class CreateOrderRequest(BaseModel):
    items: List[CartItem]
    notes: Optional[str] = None


# ── Payments ──────────────────────────────────────────────────────────────────

class PaymentMethod(str, Enum):
    mpesa = "M-Pesa"
    card = "Card"
    crypto = "Crypto"


class PaymentRequest(BaseModel):
    order_id: int
    method: PaymentMethod
    amount: float
    reference: Optional[str] = None


# ── Contact ───────────────────────────────────────────────────────────────────

class ContactRequest(BaseModel):
    name: str
    email: EmailStr
    subject: Optional[str] = None
    message: str
