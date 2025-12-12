import mysql.connector
import json
import os
import getpass
import sys

# --- CONFIGURATION ---
db_config = {
    'user': 'fenread',               # Read-only user provided
    'host': 'dbod-fenicsdb.cern.ch', # Remote CERN database host
    'port': 5509,                    # Specific port provided
    'database': 'fenicsDB'           # Database name
}

output_path = os.path.join(os.path.dirname(__file__), '../backend/data/long_cable_fenics_ids.json')

def export_data():
    print(f"--- Starting FENICS ID Export from {db_config['host']} ---")
    
    # Password handling
    if 'password' not in db_config:
        try:
            # Prompt for password safely
            db_password = getpass.getpass(prompt=f"Please enter the password for user '{db_config['user']}': ")
            db_config['password'] = db_password
        except Exception as e:
            print(f"Error reading password: {e}")
            return
    else:
        print("Using configured password.")

    conn = None
    try:
        # Connect to Database
        print(f"Connecting to {db_config['host']}:{db_config['port']}...")
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        print("Successfully connected to the remote database.")

        # Select only IDs where type > 0 (rule for long cable)
        query = "SELECT fenics FROM Fenics WHERE type > 0"
        cursor.execute(query)

        # Process results
        rows = cursor.fetchall()
        id_list = [row[0] for row in rows]
        
        print(f"Found {len(id_list)} records with long cables.")

        # Save as JSON
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        
        with open(output_path, 'w') as f:
            json.dump(id_list, f, indent=4)
            
        print(f"JSON file successfully generated at: {os.path.abspath(output_path)}")

    except mysql.connector.Error as err:
        print(f"\n[CONNECTION ERROR] Could not connect to the database.")
        print(f"Error details: {err}")
        print("\nPossible solutions:")
        print("1. Are you connected to the internet?")
        print("2. Do you need a VPN to access 'cern.ch' domains? (Ensure you are on the CERN network or VPN)")
        print("3. Verify if port 5509 is blocked by your firewall.")
        
    except Exception as e:
        print(f"Unexpected error: {e}")
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()
            print("Connection closed.")

if __name__ == "__main__":
    export_data()