curl -i -X OPTIONS -H "Origin: http://localhost:3000" \
    -H 'Access-Control-Request-Method: POST' \
    -H 'Access-Control-Request-Headers: Content-Type, Authorization' \
    "http://localhost:8080/api/equipment"