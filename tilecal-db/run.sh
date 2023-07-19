if [ -f .env ]
then
    rm .env
fi

touch .env
echo "CI_ENVIRONMENT = ${ENVIRONMENT}" >> .env
echo "app.baseURL = ${BASE_URL}" >> .env
echo "database.default.hostname = ${DB_HOSTNAME}" >> .env
echo "database.default.database = ${DB_DATABASE}" >> .env
echo "database.default.username = ${DB_USERNAME}" >> .env
echo "database.default.password = ${DB_PASSWORD}" >> .env
echo "database.default.DBDriver = ${DB_DRIVER}" >> .env
echo "database.default.DBPrefix = ${DB_PREFIX}" >> .env
echo "database.default.port = ${DB_PORT}" >> .env

php spark serve --host 0.0.0.0