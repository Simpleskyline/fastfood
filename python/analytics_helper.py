"""
analytics_helper.py – All admin analytics using pandas
Aggregates data from MySQL and returns structured dicts for the admin dashboard
"""
import pandas as pd
from .api.db import query_all, query_one


def get_full_stats() -> dict:
    """Return a comprehensive stats dict for the admin overview."""

    # Basic counters
    totals = query_one("""
        SELECT
            COUNT(DISTINCT o.id)                                  AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                      AS total_revenue,
            COALESCE(AVG(o.total_amount), 0)                      AS avg_order_value,
            SUM(CASE WHEN o.status='pending' THEN 1 ELSE 0 END)   AS pending_orders,
            SUM(CASE WHEN o.delivery_type='delivery' THEN 1 END)  AS delivery_orders,
            COALESCE(SUM(o.delivery_fee), 0)                      AS delivery_revenue,
            COALESCE(AVG(CASE WHEN o.delivery_type='delivery' THEN o.delivery_distance_km END), 0) AS avg_delivery_km
        FROM orders o WHERE o.status != 'cancelled'
    """)

    user_counts = query_one("""
        SELECT
            COUNT(*)                                                  AS total_users,
            SUM(CASE WHEN auth_provider='google' THEN 1 ELSE 0 END) AS google_users,
            SUM(CASE WHEN created_at >= NOW() - INTERVAL 30 DAY THEN 1 ELSE 0 END) AS new_users_30d
        FROM users WHERE is_active=1
    """)

    # Top items
    top_raw = query_all("""
        SELECT f.name, c.name AS category, SUM(oi.quantity) AS units_sold, SUM(oi.line_total) AS revenue
        FROM order_items oi
        JOIN food_items f ON oi.food_item_id = f.id
        JOIN categories c ON f.category_id = c.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled'
        GROUP BY f.id, f.name, c.name
        ORDER BY units_sold DESC LIMIT 10
    """)

    # Top category
    top_cat_row = query_one("""
        SELECT c.name AS category, SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN food_items f ON oi.food_item_id=f.id
        JOIN categories c ON f.category_id=c.id
        JOIN orders o ON oi.order_id=o.id
        WHERE o.status!='cancelled'
        GROUP BY c.id ORDER BY total_sold DESC LIMIT 1
    """)

    # Top customer
    top_cust = query_one("""
        SELECT CONCAT(u.first_name,' ',u.last_name) AS name, SUM(o.total_amount) AS spent
        FROM orders o JOIN users u ON o.user_id=u.id
        WHERE o.status!='cancelled'
        GROUP BY o.user_id ORDER BY spent DESC LIMIT 1
    """)

    # Payment methods
    pay_raw = query_all("""
        SELECT p.method, SUM(p.amount) AS total_amount, COUNT(*) AS count
        FROM payments p WHERE p.status='completed'
        GROUP BY p.method
    """)

    # Daily revenue (last 30 days)
    daily_raw = query_all("""
        SELECT DATE(created_at) AS order_date, COUNT(*) AS orders, SUM(total_amount) AS total_revenue
        FROM orders WHERE status!='cancelled' AND created_at >= NOW()-INTERVAL 30 DAY
        GROUP BY DATE(created_at) ORDER BY order_date
    """)

    # Signups by day
    signup_raw = query_all("""
        SELECT DATE(created_at) AS signup_date, COUNT(*) AS new_users
        FROM users WHERE created_at >= NOW()-INTERVAL 30 DAY
        GROUP BY DATE(created_at) ORDER BY signup_date
    """)

    # Use pandas for zero-filling daily revenue
    if daily_raw:
        df = pd.DataFrame(daily_raw)
        df["order_date"] = pd.to_datetime(df["order_date"])
        df["total_revenue"] = pd.to_numeric(df["total_revenue"], errors="coerce").fillna(0)
        daily = df.to_dict("records")
        for r in daily:
            r["order_date"] = str(r["order_date"])[:10]
            r["total_revenue"] = float(r["total_revenue"])
    else:
        daily = []

    if signup_raw:
        sdf = pd.DataFrame(signup_raw)
        sdf["signup_date"] = sdf["signup_date"].astype(str)
        signups = sdf.to_dict("records")
    else:
        signups = []

    return {
        "total_orders":     int(totals["total_orders"] or 0),
        "total_revenue":    float(totals["total_revenue"] or 0),
        "avg_order_value":  float(totals["avg_order_value"] or 0),
        "pending_orders":   int(totals["pending_orders"] or 0),
        "delivery_orders":  int(totals["delivery_orders"] or 0),
        "delivery_revenue": float(totals["delivery_revenue"] or 0),
        "avg_delivery_km":  float(totals["avg_delivery_km"] or 0),
        "total_users":      int(user_counts["total_users"] or 0),
        "google_users":     int(user_counts["google_users"] or 0),
        "new_users_30d":    int(user_counts["new_users_30d"] or 0),
        "top_items":        top_raw or [],
        "payment_methods":  pay_raw or [],
        "daily_revenue":    daily,
        "signups_by_day":   signups,
        "top_category":     top_cat_row["category"] if top_cat_row else "—",
        "top_customer":     top_cust["name"] if top_cust else "—",
    }


def get_admin_orders():
    return query_all("""
        SELECT o.id, o.total_amount, o.status, o.delivery_type, o.delivery_distance_km,
               o.created_at, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email AS user_email
        FROM orders o JOIN users u ON o.user_id=u.id
        ORDER BY o.created_at DESC LIMIT 500
    """) or []


def get_admin_users():
    return query_all("""
        SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS full_name, u.email,
               u.auth_provider, u.created_at, r.name AS role,
               COUNT(DISTINCT o.id) AS total_orders,
               COALESCE(SUM(o.total_amount),0) AS total_spent
        FROM users u
        JOIN roles r ON u.role_id=r.id
        LEFT JOIN orders o ON o.user_id=u.id AND o.status!='cancelled'
        GROUP BY u.id ORDER BY u.created_at DESC LIMIT 500
    """) or []


def get_admin_payments():
    return query_all("""
        SELECT p.id, p.order_id, p.method, p.amount, p.status, p.reference, p.created_at
        FROM payments p ORDER BY p.created_at DESC LIMIT 500
    """) or []


def get_admin_messages():
    return query_all("""
        SELECT id, name, email, subject, message, is_read, created_at
        FROM contact_messages ORDER BY created_at DESC LIMIT 500
    """) or []
