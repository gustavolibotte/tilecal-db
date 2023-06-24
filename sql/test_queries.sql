INSERT INTO models(name) VALUES
('pmt'), -- 1
('pmt_blocker'), -- 2
('hv_divider'), -- 3
('super_drawer'), -- 4
('mini_drawer'), -- 5
('3_in1'), -- 6
('iphone'), -- 7
('fenics'), -- 8
('mainboard'), -- 9
('daughterboard'); -- 10

DELETE FROM models WHERE name='iphone';

UPDATE models
SET name = 'pmt_block'
WHERE name = 'pmt_blocker';

-- Has ids 1 to 10 except 7

INSERT INTO equipments(
    barcode,
    model_id,
    location,
    status,
    parent_id,
    extra_params
) VALUES
('987', 2, '175', 'not assembled', NULL, NULL), -- pmt block
('34578', 10, '175', 'not working', NULL, NULL), -- daughterboard
('123', 1, '175', 'working', NULL, NULL), -- pmt
('321', 6, '175', 'working', NULL, NULL), -- 3_in_1
('789', 3, '175', 'working', NULL, NULL); -- hv_divider

START TRANSACTION;
UPDATE equipments SET
    status='assembled'
WHERE
    barcode='987';
UPDATE equipments SET
    parent_id=1 -- pmt block
WHERE
    barcode in ('123', '321', '789');
COMMIT;

DELETE FROM equipments WHERE barcode='34578' -- deletes daughter board

