print(">>> test_db.py started")

import sys
from analytics.db import get_connection

def test_connection():
    print(">>> attempting MySQL connection...")
    
    try:
        conn = get_connection()
        print(">>> ✓ connected successfully!")

        cursor = conn.cursor()
        print(">>> executing SHOW TABLES...")
        
        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()
        
        print(f">>> ✓ found {len(tables)} tables:")
        for table in tables:
            print(f"    - {table[0]}")

        # Test a simple query on one of the tables
        print("\n>>> testing query on 'clients' table...")
        cursor.execute("SELECT COUNT(*) FROM clients")
        count = cursor.fetchone()[0]
        print(f">>> ✓ clients table has {count} rows")

        cursor.close()
        conn.close()
        print("\n>>> ✓ connection closed successfully")
        
    except Exception as e:
        print(f"\n>>> ✗ ERROR occurred:")
        print(f">>> Error type: {type(e).__name__}")
        print(f">>> Error message: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    test_connection()