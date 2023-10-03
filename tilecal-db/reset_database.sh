#!/bin/bash
source setup_directories.sh
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