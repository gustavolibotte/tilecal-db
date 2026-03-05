from utils import get_bearer_token, ENDPOINTS
from locust import HttpUser, task, between

access_token = get_bearer_token()


class TileDbUser(HttpUser):
    wait_time = between(0.5, 2)
    base_headers = {
        'Authorization': f'Bearer {access_token}'
    }

    @task(3)
    def get_equipment_paginated(self):
        self.client.get(
            f"{ENDPOINTS['equipment']}?limit=50&offset=0",
            headers=self.base_headers,
            name="/api/equipment (paginated)"
        )
        
    @task(1)
    def get_equipment_full(self):
        self.client.get(
            ENDPOINTS['equipment'],
            headers=self.base_headers,
            name="/api/equipment (full)"
        )