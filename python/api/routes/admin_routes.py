"""
routes/admin_routes.py – Admin stats + analytics (Python-powered)
"""
from fastapi import APIRouter, Depends
from ..db import query_one, query_all
from ..auth import require_admin
from ..analytics.orders_analytics import (
    total_orders, total_revenue, revenue_by_day,
    orders_by_status, top_food_items,
)
from ..analytics.user_analytics import (
    total_users, new_users_last_30_days, top_customers,
)

router = APIRouter(prefix="/admin", tags=["Admin"])


@router.get("/stats")
def get_admin_stats(current_user: dict = Depends(require_admin)):
    """Full dashboard stats for the admin panel."""
    return {
        "success": True,
        "data": {
            "total_orders":          total_orders(),
            "total_revenue":         total_revenue(),
            "total_users":           total_users(),
            "new_users_last_30_days": new_users_last_30_days(),
            "orders_by_status":      orders_by_status(),
            "top_food_items":        top_food_items(limit=10),
            "top_customers":         top_customers(limit=10),
            "revenue_by_day":        revenue_by_day(days=30),
        },
    }


@router.get("/orders")
def get_all_orders(
    status: str = None,
    limit: int = 50,
    offset: int = 0,
    current_user: dict = Depends(require_admin),
):
    where = "WHERE 1=1"
    params: list = []
    if status:
        where += " AND o.status = %s"
        params.append(status)

    orders = query_all(
        f"""SELECT o.id, o.total_amount, o.status, o.created_at,
                   u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            {where}
            ORDER BY o.created_at DESC
            LIMIT %s OFFSET %s""",
        tuple(params) + (limit, offset),
    )
    for o in orders:
        o["created_at"] = str(o["created_at"])
    return {"success": True, "orders": orders}


@router.get("/users")
def get_all_users(current_user: dict = Depends(require_admin)):
    users = query_all(
        """SELECT u.id, u.first_name, u.last_name, u.username, u.email,
                  u.phone, u.is_active, u.created_at, r.name AS role
           FROM users u JOIN roles r ON u.role_id = r.id
           ORDER BY u.created_at DESC"""
    )
    for u in users:
        u["created_at"] = str(u["created_at"])
    return {"success": True, "users": users}
