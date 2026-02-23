"""
routes/misc_routes.py – Contact form, ping
"""
from fastapi import APIRouter
from ..models import ContactRequest
from ..db import execute

router = APIRouter(tags=["Misc"])


@router.get("/ping")
def ping():
    return {"status": "ok", "service": "Skyline Treats API"}


@router.post("/contact")
def submit_contact(req: ContactRequest):
    execute(
        "INSERT INTO contact_messages (name, email, subject, message) VALUES (%s, %s, %s, %s)",
        (req.name, req.email, req.subject, req.message),
    )
    return {"success": True, "message": "Message received. We'll be in touch!"}
