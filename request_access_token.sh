if [ -e "access_token.json" ]; then
    rm access_token.json
fi
curl --location --request POST "https://auth.cern.ch/auth/realms/cern/api-access/token" \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'grant_type=client_credentials' \
--data-urlencode "client_id=$tester_clientId" \
--data-urlencode "client_secret=$tester_clientSecret" \
--data-urlencode "audience=$sso_clientId" \
>> access_token.json
