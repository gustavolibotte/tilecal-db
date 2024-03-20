from utils import get_bearer_token, ENDPOINTS
from locust import HttpUser, task, between

access_token = get_bearer_token()


class TileDbUser(HttpUser):
    wait_time = between(0.5, 2)
    base_headers = {
        'Authorization': f'Bearer {access_token}'
    }

    @task
    def get_equipment(self):
        self.client.get(
            ENDPOINTS['equipment'],
            headers=self.base_headers
            )
