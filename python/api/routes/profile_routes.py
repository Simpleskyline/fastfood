"""
routes/profile_routes.py – View and update profile, order history
"""
from fastapi import APIRouter, Depends, HTTPException
from ..models import UpdateProfileRequest
from ..db import query_one, query_all, execute
from ..auth import get_current_user

router = APIRouter(prefix="/profile", tags=["Profile"])


@router.get("/")
def get_profile(current_user: dict = Depends(get_current_user)):
    user_id = int(current_user["sub"])

    user = query_one(
        """SELECT u.id, u.first_name, u.last_name, u.username, u.email,
                  u.phone, u.location, u.created_at, r.name AS role
           FROM users u
           JOIN roles r ON u.role_id = r.id
           WHERE u.id = %s""",
        (user_id,),
    )
    if not user:
        raise HTTPException(404, "User not found")

    user["created_at"] = str(user["created_at"])
    return {"success": True, "user": user}


@router.put("/")
def update_profile(req: UpdateProfileRequest, current_user: dict = Depends(get_current_user)):
    user_id = int(current_user["sub"])

    fields = []
    values = []

    if req.first_name is not None:
        fields.append("first_name = %s"); values.append(req.first_name)
    if req.last_name is not None:
        fields.append("last_name = %s"); values.append(req.last_name)
    if req.phone is not None:
        fields.append("phone = %s"); values.append(req.phone)
    if req.location is not None:
        fields.append("location = %s"); values.append(req.location)

    if not fields:
        raise HTTPException(400, "No fields to update")

    values.append(user_id)
    execute(
        f"UPDATE users SET {', '.join(fields)} WHERE id = %s",
        tuple(values),
    )
    return {"success": True, "message": "Profile updated"}
