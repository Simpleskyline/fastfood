"""
routes/payment_routes.py – Process payments
"""
from fastapi import APIRouter, Depends, HTTPException
from ..models import PaymentRequest
from ..db import query_one, execute
from ..auth import get_current_user

router = APIRouter(prefix="/payments", tags=["Payments"])


@router.post("/")
def save_payment(req: PaymentRequest, current_user: dict = Depends(get_current_user)):
    user_id = int(current_user["sub"])

    # Confirm order belongs to this user
    order = query_one(
        "SELECT id, total_amount, status FROM orders WHERE id = %s AND user_id = %s",
        (req.order_id, user_id),
    )
    if not order:
        raise HTTPException(404, "Order not found")

    if order["status"] == "cancelled":
        raise HTTPException(400, "Cannot pay for a cancelled order")

    pay_id = execute(
        """INSERT INTO payments (order_id, method, amount, status, reference)
           VALUES (%s, %s, %s, 'completed', %s)""",
        (req.order_id, req.method.value, req.amount, req.reference),
    )

    # Update order status
    execute(
        "UPDATE orders SET status = 'confirmed' WHERE id = %s",
        (req.order_id,),
    )

    return {
        "success": True,
        "message": f"Payment via {req.method.value} recorded",
        "payment_id": pay_id,
    }
