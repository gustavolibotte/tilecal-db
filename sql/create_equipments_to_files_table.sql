CREATE TABLE equipments_to_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id VARCHAR(20) NOT NULL,
    file_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT fk_equipments_to_files FOREIGN KEY (equipment_id) REFERENCES equipments(id),
    CONSTRAINT fk_files_to_equipments FOREIGN KEY (file_id) REFERENCES files(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE equipments_to_files_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    ef_id INT UNSIGNED NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DELIMITER |

CREATE TRIGGER equipments_to_files_hist_insert AFTER INSERT ON equipments_to_files
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', NEW.id,
        'equipment_id', NEW.equipment_id,
        'file_id', NEW.file_id,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at
    );
    INSERT INTO equipments_to_files_hist(ef_id, action, changes)
    VALUES (NEW.id, 'insert', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER equipments_to_files_hist_delete AFTER DELETE ON equipments_to_files
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', OLD.id,
        'equipment_id', OLD.equipment_id,
        'file_id', OLD.file_id,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at
    );
    INSERT INTO equipments_to_files_hist(ef_id, action, changes)
    VALUES (OLD.id, 'delete', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER equipments_to_files_hist_update AFTER UPDATE ON equipments_to_files
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT();

    -- Check each column for changes and add them to the changes array
    -- Customize this block to include the specific columns you want to track
    IF (NEW.id <> OLD.id) THEN
        SET @changes = JSON_SET(@changes, '$.id', JSON_OBJECT('old', OLD.id, 'new', NEW.id));
    END IF;

    IF (NEW.equipment_id <> OLD.equipment_id) THEN
        SET @changes = JSON_SET(@changes, '$.equipment_id', JSON_OBJECT('old', OLD.equipment_id, 'new', NEW.equipment_id));
    END IF;

    IF (NEW.file_id <> OLD.file_id) THEN
        SET @changes = JSON_SET(@changes, '$.file_id', JSON_OBJECT('old', OLD.file_id, 'new', NEW.file_id));
    END IF;

    IF (NEW.created_at <> OLD.created_at) THEN
        SET @changes = JSON_SET(@changes, '$.created_at', JSON_OBJECT('old', OLD.created_at, 'new', NEW.created_at));
    END IF;

    IF (NEW.updated_at <> OLD.updated_at) THEN
        SET @changes = JSON_SET(@changes, '$.updated_at', JSON_OBJECT('old', OLD.updated_at, 'new', NEW.updated_at));
    END IF;

    INSERT INTO equipments_to_files_hist(ef_id, action, changes)
    VALUES (OLD.id, 'update', @changes);
END
|
DELIMITER ;

