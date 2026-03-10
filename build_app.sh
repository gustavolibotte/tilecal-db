#!/bin/bash
set -e

# Install backend dependencies (use install instead of update)
cd backend
composer install
if [ $ENVIRONMENT = 'local' ]; then
    source setup_directories.sh
fi
cd ../

# Clean old distribution files
PUBLIC_DIR="backend/public"
DIST_DIR="$PUBLIC_DIR/dist"
if [ -d "$DIST_DIR" ]; then
    echo "Deleting old dir $DIST_DIR"
    rm -r $DIST_DIR
fi

ASSETS_DIR="$PUBLIC_DIR/assets"
if [ -d "$ASSETS_DIR" ]; then
    echo "Deleting old dir $ASSETS_DIR"
    rm -r $ASSETS_DIR
fi

VIEWS_DIR="backend/app/Views"

# Build React app
cd frontend
npm install 
npm run build 
cd ../

# Move build artifacts
mv $DIST_DIR/index.html $VIEWS_DIR/index.php
mv $DIST_DIR/assets $PUBLIC_DIR
rm -r $DIST_DIR