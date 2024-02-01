#!/bin/bash
# set -e
PUBLIC_DIR="backend/public"
DIST_DIR="$PUBLIC_DIR/dist"
if [ -d "$DIST_DIR" ]; then
    echo "Deleting old dir $DIST_DIR"
    rm -r $DIST_DIR
fi
# ls /shrebbles

ASSETS_DIR="$PUBLIC_DIR/assets"
if [ -d "$ASSETS_DIR" ]; then
    echo "Deleting old dir $ASSETS_DIR"
    rm -r $ASSETS_DIR
fi

VIEWS_DIR="backend/app/Views"
cd frontend && npm run build && cd ../
mv $DIST_DIR/index.html $VIEWS_DIR/index.html
mv $DIST_DIR/assets $PUBLIC_DIR
rm -r $DIST_DIR