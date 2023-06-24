CREATE TABLE equipments (
    id VARCHAR(20) PRIMARY KEY NOT NULL,
    model_id INT UNSIGNED NOT NULL,
    location VARCHAR(30) NOT NULL,
    status VARCHAR(30) NOT NULL,
    parent_id VARCHAR(20),
    extra_params JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT fk_equipment_model FOREIGN KEY (model_id) REFERENCES models(id),
    CONSTRAINT fk_equipment_parent FOREIGN KEY (parent_id) REFERENCES equipments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE equipments_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    equipment_id VARCHAR(20) NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DELIMITER |

CREATE TRIGGER equipments_hist_insert AFTER INSERT ON equipments
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', NEW.id,
        'model_id', NEW.model_id,
        'location', NEW.location,
        'status', NEW.status,
        'parent_id', NEW.parent_id,
        'extra_params', NEW.extra_params,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at
        );
    INSERT INTO equipments_hist(equipment_id, action, changes)
    VALUES (NEW.id, 'insert', @changes);
END;
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER equipments_hist_delete AFTER DELETE ON equipments
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', OLD.id,
        'model_id', OLD.model_id,
        'location', OLD.location,
        'status', OLD.status,
        'parent_id', OLD.parent_id,
        'extra_params', OLD.extra_params,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at
        );
    INSERT INTO equipments_hist(equipment_id, action, changes)
    VALUES (OLD.id, 'insert', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER equipments_hist_update AFTER UPDATE ON equipments
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT();

    -- Check each column for changes and add them to the changes array
    -- Customize this block to include the specific columns you want to track
    IF (NEW.id <> OLD.id) THEN
        SET @changes = JSON_SET(@changes, '$.id', JSON_OBJECT('old', OLD.id, 'new', NEW.id));
    END IF;

    IF (NEW.model_id <> OLD.model_id) THEN
        SET @changes = JSON_SET(@changes, '$.model_id', JSON_OBJECT('old', OLD.model_id, 'new', NEW.model_id));
    END IF;

    IF (NEW.location <> OLD.location) THEN
        SET @changes = JSON_SET(@changes, '$.location', JSON_OBJECT('old', OLD.location, 'new', NEW.location));
    END IF;

    IF (NEW.status <> OLD.status) THEN
        SET @changes = JSON_SET(@changes, '$.status', JSON_OBJECT('old', OLD.status, 'new', NEW.status));
    END IF;

    IF (NEW.parent_id <> OLD.parent_id) THEN
        SET @changes = JSON_SET(@changes, '$.parent_id', JSON_OBJECT('old', OLD.parent_id, 'new', NEW.parent_id));
    END IF;

    IF (NEW.created_at <> OLD.created_at) THEN
        SET @changes = JSON_SET(@changes, '$.created_at', JSON_OBJECT('old', OLD.created_at, 'new', NEW.created_at));
    END IF;

    IF (NEW.updated_at <> OLD.updated_at) THEN
        SET @changes = JSON_SET(@changes, '$.updated_at', JSON_OBJECT('old', OLD.updated_at, 'new', NEW.updated_at));
    END IF;

    INSERT INTO equipments_hist(equipment_id, action, changes)
    VALUES (OLD.id, 'update', @changes);
END
|
DELIMITER ;

