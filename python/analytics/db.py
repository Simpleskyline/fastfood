import mysql.connector
from mysql.connector import Error


def get_connection():
    """
    Create and return a MySQL database connection
    """
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="fastfood_db"  # change if different
        )
        return connection

    except Error as e:
        raise Exception(f"MySQL connection failed: {e}")
