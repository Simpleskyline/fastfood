from python.analytics.db import get_connection


def total_orders():
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("SELECT COUNT(*) FROM orders")
    result = cursor.fetchone()[0]

    cursor.close()
    conn.close()
    return result


def total_revenue():
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("SELECT SUM(total_amount) FROM orders")
    result = cursor.fetchone()[0] or 0

    cursor.close()
    conn.close()
    return result


if __name__ == "__main__":
    print("🧾 Total orders:", total_orders())
    print("💰 Total revenue:", total_revenue())
