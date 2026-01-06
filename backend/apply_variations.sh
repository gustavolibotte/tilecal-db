#!/bin/bash

# -----------------------------------------------------------------------------
# Script: apply_variations.sh
# Purpose: Automates the migration process for the new component variations
#          (Long Cables/Patch-Panels) and updates slot rules.
# -----------------------------------------------------------------------------

# 1. Setup directories (Consistent with your reset_database.sh)
if [ -f "setup_directories.sh" ]; then
    source setup_directories.sh
fi

echo ">> Starting Component Variations Migration..."

# 2. Run pending migrations
# This will execute:
#   - backend/app/Database/Migrations/2026-01-06-223121_UpdateComponentVariationsTypes.php (Structure)
#   - backend/app/Database/Migrations/2026-01-06-225302_MigrateLongCableEquipments.php (Data)
echo ">> Running Spark Migrations..."
php spark migrate

if [ $? -ne 0 ]; then
    echo "!! Migration Failed. Aborting."
    exit 1
fi

# 3. Update Slot Rules
# The EquipmentTypeSeeder reads 'backend/data/equipment_types.json' and 
# repopulates the 'allowed_type_slots' table.
echo ">> Seeding Equipment Types and Slot Rules..."
php spark db:seed EquipmentTypeSeeder

if [ $? -ne 0 ]; then
    echo "!! Seeding Failed."
    exit 1
fi

echo ">> Success! Database is now up-to-date with component variations."