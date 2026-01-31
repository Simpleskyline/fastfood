import mysql.connector
from analytics.config import DB_CONFIG

def get_connection():
    return mysql.connector.connect(**DB_CONFIG)
