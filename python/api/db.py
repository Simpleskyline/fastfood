"""
db.py – MySQL connection pool for FastAPI
"""
import mysql.connector
from mysql.connector.pooling import MySQLConnectionPool
from contextlib import contextmanager
from .config import get_settings

settings = get_settings()

_pool: MySQLConnectionPool | None = None


def get_pool() -> MySQLConnectionPool:
    global _pool
    if _pool is None:
        _pool = MySQLConnectionPool(
            pool_name="skyline_pool",
            pool_size=10,
            host=settings.DB_HOST,
            port=settings.DB_PORT,
            database=settings.DB_NAME,
            user=settings.DB_USER,
            password=settings.DB_PASS,
            charset="utf8mb4",
            collation="utf8mb4_unicode_ci",
            autocommit=False,
            connection_timeout=10,
        )
    return _pool


@contextmanager
def get_db():
    """Context manager: yields a connection, commits on success, rolls back on error."""
    conn = get_pool().get_connection()
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()


def query_one(sql: str, params: tuple = ()) -> dict | None:
    with get_db() as conn:
        cursor = conn.cursor(dictionary=True)
        cursor.execute(sql, params)
        row = cursor.fetchone()
        cursor.close()
        return row


def query_all(sql: str, params: tuple = ()) -> list[dict]:
    with get_db() as conn:
        cursor = conn.cursor(dictionary=True)
        cursor.execute(sql, params)
        rows = cursor.fetchall()
        cursor.close()
        return rows


def execute(sql: str, params: tuple = ()) -> int:
    """Execute INSERT/UPDATE/DELETE. Returns last inserted id or rowcount."""
    with get_db() as conn:
        cursor = conn.cursor()
        cursor.execute(sql, params)
        last_id = cursor.lastrowid
        conn.commit()
        cursor.close()
        return last_id
