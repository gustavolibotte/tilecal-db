#!/bin/bash
APP_EOS_DIR="/eos/user/t/tilep2db/${CI_ENVIRONMENT}"
APP_FILES_DIR="${APP_EOS_DIR}/files"
APP_WRITABLE_DIR="${APP_EOS_DIR}/writable"

if [ ! -d "$APP_FILES_DIR" ]; then
    mkdir -p $APP_FILES_DIR
fi

if [ ! -d "$APP_WRITABLE_DIR" ]; then
    mkdir -p $APP_WRITABLE_DIR
    mkdir -p "${APP_WRITABLE_DIR}/cache"
    mkdir -p "${APP_WRITABLE_DIR}/debugbar"
    mkdir -p "${APP_WRITABLE_DIR}/logs"
    mkdir -p "${APP_WRITABLE_DIR}/session"
    mkdir -p "${APP_WRITABLE_DIR}/uploads"
fi
