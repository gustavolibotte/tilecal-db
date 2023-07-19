if [ -f /tmp/.env ]
then
    rm /tmp/.env
fi

touch /tmp/.env
echo "CI_ENVIRONMENT = ${ENVIRONMENT}" >> /tmp/.env
echo "app.baseURL = ${BASE_URL}" >> /tmp/.env
echo "database.default.hostname = ${DB_HOSTNAME}" >> /tmp/.env
echo "database.default.database = ${DB_DATABASE}" >> /tmp/.env
echo "database.default.username = ${DB_USERNAME}" >> /tmp/.env
echo "database.default.password = ${DB_PASSWORD}" >> /tmp/.env
echo "database.default.DBDriver = ${DB_DRIVER}" >> /tmp/.env
echo "database.default.DBPrefix = ${DB_PREFIX}" >> /tmp/.env
echo "database.default.port = ${DB_PORT}" >> /tmp/.env

php spark serve --host 0.0.0.0