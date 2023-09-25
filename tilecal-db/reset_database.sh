#!/bin/bash
ENVIRONMENT="/eos/user/t/tilep2db/$1/files"
if [ ! -d "$TILE_DB_FILE_DIRECTORY" ]; then
    mkdir -p $TILE_DB_FILE_DIRECTORY
fi

php spark migrate:rollback
php spark migrate
php spark db:seed Cleaner
php spark db:seed InstituteSeeder
php spark db:seed StatusSeeder
php spark db:seed LocationSeeder
php spark db:seed EquipmentTypeSeeder
php spark db:seed EquipmentSeeder
php spark db:seed FileSeeder
php spark db:seed Equipment2FileSeeder