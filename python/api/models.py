"""
models.py – Pydantic request/response models (updated)
"""
from pydantic import BaseModel, EmailStr, field_validator
from typing import Optional


class RegisterRequest(BaseModel):
    first_name:       str
    last_name:        str
    username:         str
    email:            EmailStr
    password:         str
    confirm_password: str
    role:             Optional[str] = "customer"

    @field_validator("password")
    def password_min_length(cls, v):
        if len(v) < 6:
            raise ValueError("Password must be at least 6 characters")
        return v

    @field_validator("confirm_password")
    def passwords_match(cls, v, values):
        if "password" in values.data and v != values.data["password"]:
            raise ValueError("Passwords do not match")
        return v


class LoginRequest(BaseModel):
    email:    EmailStr
    password: str


class ForgotPasswordRequest(BaseModel):
    email: EmailStr


class ResetPasswordRequest(BaseModel):
    token:        str
    new_password: str

    @field_validator("new_password")
    def password_min_length(cls, v):
        if len(v) < 6:
            raise ValueError("Password must be at least 6 characters")
        return v


class TokenResponse(BaseModel):
    success: bool
    token:   str
    user:    dict


class OrderItemIn(BaseModel):
    food_id:  int
    quantity: int = 1
    variant:  Optional[str] = None
    sugar:    Optional[str] = None


class OrderRequest(BaseModel):
    items:            list[OrderItemIn]
    delivery_type:    Optional[str] = "pickup"
    delivery_address: Optional[str] = None
    delivery_lat:     Optional[float] = None
    delivery_lng:     Optional[float] = None
    delivery_distance_km: Optional[float] = None
    delivery_fee:     Optional[float] = 0.0
    notes:            Optional[str]  = None


class PaymentRequest(BaseModel):
    order_id:   int
    method:     str
    amount:     float
    reference:  Optional[str]   = None
    delivery_type: Optional[str] = "pickup"
    delivery_fee:  Optional[float] = 0.0
    delivery_lat:  Optional[float] = None
    delivery_lng:  Optional[float] = None
    delivery_distance_km: Optional[float] = None


class ProfileUpdateRequest(BaseModel):
    first_name: Optional[str] = None
    last_name:  Optional[str] = None
    phone:      Optional[str] = None
    location:   Optional[str] = None
