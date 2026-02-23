"""
routes/order_routes.py – Create order, fetch my orders
"""
from fastapi import APIRouter, Depends, HTTPException
from ..models import CreateOrderRequest
from ..db import query_one, query_all, get_db
from ..auth import get_current_user
import json

router = APIRouter(prefix="/orders", tags=["Orders"])


@router.post("/")
def create_order(req: CreateOrderRequest, current_user: dict = Depends(get_current_user)):
    user_id = int(current_user["sub"])

    if not req.items:
        raise HTTPException(400, "Cart is empty")

    with get_db() as conn:
        cursor = conn.cursor(dictionary=True)

        total = 0.0
        validated = []

        # Validate every item against DB (never trust frontend price)
        for item in req.items:
            cursor.execute(
                "SELECT id, name, price FROM food_items WHERE id = %s AND is_active = 1",
                (item.food_id,),
            )
            food = cursor.fetchone()
            if not food:
                raise HTTPException(400, f"Food item {item.food_id} not found or unavailable")

            line_total = float(food["price"]) * item.quantity
            total += line_total
            validated.append({**food, "quantity": item.quantity, "line_total": line_total})

        # Create order
        cursor.execute(
            "INSERT INTO orders (user_id, total_amount, status, notes) VALUES (%s, %s, 'pending', %s)",
            (user_id, total, req.notes),
        )
        order_id = cursor.lastrowid

        # Insert line items
        for v in validated:
            cursor.execute(
                "INSERT INTO order_items (order_id, food_item_id, quantity, unit_price) VALUES (%s, %s, %s, %s)",
                (order_id, v["id"], v["quantity"], v["price"]),
            )

        conn.commit()
        cursor.close()

    return {
        "success": True,
        "message": "Order placed successfully",
        "order_id": order_id,
        "total": round(total, 2),
    }


@router.get("/my")
def get_my_orders(current_user: dict = Depends(get_current_user)):
    user_id = int(current_user["sub"])

    orders = query_all(
        """SELECT id, total_amount, status, notes, created_at
           FROM orders WHERE user_id = %s ORDER BY created_at DESC""",
        (user_id,),
    )

    for order in orders:
        order["items"] = query_all(
            """SELECT oi.quantity, oi.unit_price, oi.line_total, f.name
               FROM order_items oi
               JOIN food_items f ON oi.food_item_id = f.id
               WHERE oi.order_id = %s""",
            (order["id"],),
        )
        # Serialize datetime
        order["created_at"] = str(order["created_at"])

    return {"success": True, "orders": orders}
