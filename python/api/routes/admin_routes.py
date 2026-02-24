"""
routes/admin_routes.py – Full admin analytics + management
"""
from fastapi import APIRouter, HTTPException, Depends
from ..auth import require_admin
from ..db import query_one, query_all, execute
from ..analytics_helper import (
    get_full_stats, get_admin_orders, get_admin_users,
    get_admin_payments, get_admin_messages
)

router = APIRouter(prefix="/admin", tags=["Admin"])


@router.get("/stats")
def get_stats(admin=Depends(require_admin)):
    try:
        data = get_full_stats()
        return {"success": True, "data": data}
    except Exception as e:
        raise HTTPException(500, str(e))


@router.get("/orders")
def get_orders(admin=Depends(require_admin)):
    orders = get_admin_orders()
    return {"success": True, "data": orders}


@router.put("/orders/{order_id}/status")
def update_order_status(order_id: int, body: dict, admin=Depends(require_admin)):
    status = body.get("status")
    valid  = ["pending","confirmed","preparing","ready","out_for_delivery","delivered","cancelled"]
    if status not in valid:
        raise HTTPException(400, "Invalid status")
    execute("UPDATE orders SET status=%s WHERE id=%s", (status, order_id))
    return {"success": True, "message": f"Order {order_id} updated to {status}"}


@router.get("/users")
def get_users(admin=Depends(require_admin)):
    users = get_admin_users()
    return {"success": True, "data": users}


@router.get("/payments")
def get_payments(admin=Depends(require_admin)):
    payments = get_admin_payments()
    return {"success": True, "data": payments}


@router.get("/messages")
def get_messages(admin=Depends(require_admin)):
    msgs = get_admin_messages()
    return {"success": True, "data": msgs}
