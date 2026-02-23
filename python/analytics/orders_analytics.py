"""
analytics/orders_analytics.py – Order metrics powered by Python + pandas
"""
from ..db import query_all, query_one
import pandas as pd
from datetime import datetime, timedelta


def total_orders() -> int:
    row = query_one("SELECT COUNT(*) AS cnt FROM orders WHERE status != 'cancelled'")
    return row["cnt"] if row else 0


def total_revenue() -> float:
    row = query_one(
        "SELECT COALESCE(SUM(total_amount), 0) AS rev FROM orders WHERE status NOT IN ('cancelled', 'pending')"
    )
    return float(row["rev"]) if row else 0.0


def orders_by_status() -> list[dict]:
    return query_all(
        "SELECT status, COUNT(*) AS count FROM orders GROUP BY status ORDER BY count DESC"
    )


def top_food_items(limit: int = 5) -> list[dict]:
    rows = query_all(
        """SELECT f.name, c.name AS category,
                  SUM(oi.quantity) AS units_sold,
                  SUM(oi.line_total) AS revenue
           FROM order_items oi
           JOIN food_items f ON oi.food_item_id = f.id
           JOIN categories c ON f.category_id = c.id
           JOIN orders o ON oi.order_id = o.id
           WHERE o.status != 'cancelled'
           GROUP BY f.id, f.name, c.name
           ORDER BY units_sold DESC
           LIMIT %s""",
        (limit,),
    )
    return [
        {**r, "units_sold": int(r["units_sold"]), "revenue": float(r["revenue"])}
        for r in rows
    ]


def revenue_by_day(days: int = 30) -> list[dict]:
    """Return daily revenue for the last N days using pandas for aggregation."""
    since = (datetime.now() - timedelta(days=days)).strftime("%Y-%m-%d")
    rows = query_all(
        """SELECT DATE(created_at) AS order_date, SUM(total_amount) AS revenue,
                  COUNT(*) AS orders
           FROM orders
           WHERE created_at >= %s AND status NOT IN ('cancelled', 'pending')
           GROUP BY DATE(created_at)
           ORDER BY order_date""",
        (since,),
    )
    if not rows:
        return []

    df = pd.DataFrame(rows)
    df["order_date"] = df["order_date"].astype(str)
    df["revenue"] = df["revenue"].astype(float).round(2)
    df["orders"] = df["orders"].astype(int)

    # Fill missing days with 0
    date_range = pd.date_range(start=since, end=datetime.now().date(), freq="D")
    full_df = pd.DataFrame({"order_date": date_range.strftime("%Y-%m-%d")})
    merged = full_df.merge(df, on="order_date", how="left").fillna({"revenue": 0.0, "orders": 0})
    merged["orders"] = merged["orders"].astype(int)

    return merged.to_dict(orient="records")


def average_order_value() -> float:
    row = query_one(
        "SELECT AVG(total_amount) AS aov FROM orders WHERE status NOT IN ('cancelled', 'pending')"
    )
    return round(float(row["aov"] or 0), 2) if row else 0.0
