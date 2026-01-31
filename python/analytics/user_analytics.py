from python.analytics.db import get_connection


def total_users():
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("SELECT COUNT(*) FROM users")
    result = cursor.fetchone()[0]

    cursor.close()
    conn.close()
    return result


if __name__ == "__main__":
    print("👤 Total users:", total_users())
