import mysql.connector
from config import DB_CONFIG # Ensure config.py is in the same folder

def get_connection():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return None
def close_connection(conn):
    if conn:
        conn.close()    