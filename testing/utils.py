import os
import json

ENDPOINTS = {
    'equipment': 'api/equipment',
    'location': 'api/location',
    'institute': 'api/institute',
    'status': 'api/status',
    'detector_location': 'api/detector-location',
    'file': 'api/file',
    'equipment_hist': 'api/equipment-hist',
    'file_hist': 'api/file-hist',
    'file_put': 'api/file-put',
    'user': 'api/user',
    'equipment_type': 'api/equipment-type',
    'equipment_batch': 'api/equipment/batch-register',
    'equipment_assemble': 'api/equipment/assemble',
    'equipment_disassemble': 'api/equipment/disassemble',
    'file_download': 'api/file/download',
    'file_download_hist': 'api/file/download-hist'
}


def get_bearer_token():
    jsonpath = '/root/tilecal-db/testing/access_token.json'
    with open(os.path.join(jsonpath)) as json_file:
        response_json = json.load(json_file)
    return response_json['access_token']


def get_host_endpoint(name: str) -> str:
    return f"{os.getenv('app_baseUrl')}/{ENDPOINTS[name]}"
