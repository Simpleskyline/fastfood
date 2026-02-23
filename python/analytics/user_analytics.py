"""
analytics/user_analytics.py – User metrics
"""
from ..db import query_all, query_one
import pandas as pd
from datetime import datetime, timedelta


def total_users() -> int:
    row = query_one("SELECT COUNT(*) AS cnt FROM users WHERE is_active = 1")
    return row["cnt"] if row else 0


def new_users_last_30_days() -> int:
    since = (datetime.now() - timedelta(days=30)).strftime("%Y-%m-%d")
    row = query_one(
        "SELECT COUNT(*) AS cnt FROM users WHERE created_at >= %s AND is_active = 1",
        (since,),
    )
    return row["cnt"] if row else 0


def top_customers(limit: int = 10) -> list[dict]:
    rows = query_all(
        """SELECT u.id,
                  CONCAT(u.first_name, ' ', u.last_name) AS name,
                  u.email,
                  COUNT(DISTINCT o.id)               AS total_orders,
                  COALESCE(SUM(o.total_amount), 0)   AS total_spent
           FROM users u
           LEFT JOIN orders o ON o.user_id = u.id AND o.status != 'cancelled'
           GROUP BY u.id, name, u.email
           ORDER BY total_spent DESC
           LIMIT %s""",
        (limit,),
    )
    return [
        {**r, "total_orders": int(r["total_orders"]), "total_spent": float(r["total_spent"])}
        for r in rows
    ]


def signups_by_day(days: int = 30) -> list[dict]:
    since = (datetime.now() - timedelta(days=days)).strftime("%Y-%m-%d")
    rows = query_all(
        """SELECT DATE(created_at) AS signup_date, COUNT(*) AS count
           FROM users WHERE created_at >= %s
           GROUP BY DATE(created_at) ORDER BY signup_date""",
        (since,),
    )
    if not rows:
        return []
    df = pd.DataFrame(rows)
    df["signup_date"] = df["signup_date"].astype(str)
    df["count"] = df["count"].astype(int)
    return df.to_dict(orient="records")
