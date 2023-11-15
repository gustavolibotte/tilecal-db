from itertools import product
import json
import os

barrel_sides = [
    'EBA',
    'EBC',
    'LBA',
    'LBC'
]
n_drawers = 64
tile_modules = [{"name": f'{side}{drawer:02d}'} 
    for side, drawer in product(barrel_sides, range(n_drawers+1))]
output_dir = '/root/tilecal-db/tilecal-db/data'
filename = 'tile_modules.json'

with open(os.path.join(output_dir, filename), 'w') as json_file:
    json.dump(tile_modules, json_file, indent=4)
