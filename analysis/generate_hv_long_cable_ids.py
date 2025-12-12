import json
import os

output_path = os.path.join(os.path.dirname(__file__), '../backend/data/long_cable_hv_ids.json')

def generate_data():
    print("--- Starting HV Divider ID Generation (Long Cable) ---")
    
    # Rule provided:
    # "989 pcs. are of the long cable type. These have the serial numbers: 0020001 – 0020989"
    
    start_id = 20001
    end_id = 20989
    id_list = []

    print(f"Generating IDs from {start_id:07d} to {end_id:07d}...")

    for num in range(start_id, end_id + 1):
        # Format the number with leading zeros to match the 7-character string format (e.g., "0020001")
        formatted_id = f"{num:07d}"
        id_list.append(formatted_id)
        
    print(f"Total IDs generated: {len(id_list)}")

    try:
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        
        with open(output_path, 'w') as f:
            json.dump(id_list, f, indent=4)
            
        print(f"JSON file successfully generated at: {os.path.abspath(output_path)}")
    except Exception as e:
        print(f"Error saving file: {e}")

if __name__ == "__main__":
    generate_data()