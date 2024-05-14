import requests
import os

# Obtaining the access token
url = "https://auth.cern.ch/auth/realms/cern/api-access/token"

payload = {
    'grant_type': 'client_credentials',
    'client_id': os.environ['tester_clientId'],
    'client_secret': os.environ['tester_clientSecret'],
    'audience': os.environ['sso_clientId']
}

headers = {
    'Content-Type': 'application/x-www-form-urlencoded'
}

response = requests.post(url, headers=headers, data=payload)

with open('access_token.json', 'w') as f:
    f.write(response.text)

# Using the access token to get the data
access_token = response.json()['access_token']
print(f'Using acess_token:\n{access_token}')
app_url = 'https://tilecal-db-dev.app.cern.ch/'

headers = {
    'Authorization': f'Bearer {access_token}',
    'Content-Type': 'application/json'
}

response = requests.get(
    app_url + "api/equipment",
    headers=headers
)

with open('equipment.json', 'w') as f:
    f.write(response.text)
