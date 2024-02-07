#!/bin/bash
if [ $ENVIRONMENT = 'local' ]; then
    echo 'Changing /var/www/html permissions' 
    chmod -R 777 /var/www/html
fi
APP_EOS_DIR="/eos/user/t/tilep2db/${ENVIRONMENT}"
APP_FILES_DIR="${APP_EOS_DIR}/files"
APP_WRITABLE_DIR="${APP_EOS_DIR}/writable"

if [ ! -d "$APP_FILES_DIR" ]; then
    echo "Creating ${APP_FILES_DIR}"
    mkdir -p $APP_FILES_DIR
fi

if [ ! -d "$APP_WRITABLE_DIR" ]; then
    echo "Creating ${APP_WRITABLE_DIR}"
    mkdir -p $APP_WRITABLE_DIR
    
    # Default CI paths
    mkdir -p "${APP_WRITABLE_DIR}/cache"
    mkdir -p "${APP_WRITABLE_DIR}/debugbar"
    mkdir -p "${APP_WRITABLE_DIR}/logs"
    mkdir -p "${APP_WRITABLE_DIR}/session"
    mkdir -p "${APP_WRITABLE_DIR}/uploads"

    # Custom paths
    mkdir -p "${APP_WRITABLE_DIR}/validation_logs"
fi

if [ $ENVIRONMENT = 'local' ]; then
    echo "Changing ${APP_EOS_DIR} permissions" 
    chmod -R 777 $APP_EOS_DIR
fi
