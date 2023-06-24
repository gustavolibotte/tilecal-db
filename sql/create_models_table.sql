CREATE TABLE models (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT unique_model_name UNIQUE(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE models_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_id INT UNSIGNED NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DELIMITER |

CREATE TRIGGER models_hist_insert AFTER INSERT ON models
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', NEW.id,
        'name', NEW.name,
        'description', NEW.description,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at
    );
    INSERT INTO models_hist(model_id, action, changes)
    VALUES (NEW.id, 'insert', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER models_hist_delete AFTER DELETE ON models
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
   		'id', OLD.id,
   		'name', OLD.name,
        'description', OLD.description,
   		'created_at', OLD.created_at,
		'updated_at', OLD.updated_at);
    INSERT INTO models_hist(model_id, action, changes)
    VALUES (OLD.id, 'delete', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER models_hist_update AFTER UPDATE ON models
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT();

    -- Check each column for changes and add them to the changes array
    -- Customize this block to include the specific columns you want to track
    IF (NEW.id <> OLD.id) THEN
        SET @changes = JSON_SET(@changes, '$.id', JSON_OBJECT('old', OLD.id, 'new', NEW.id));
    END IF;
    
    IF (NEW.name <> OLD.name) THEN
        SET @changes = JSON_SET(@changes, '$.name', JSON_OBJECT('old', OLD.name, 'new', NEW.name));
    END IF;

    IF (NEW.description <> OLD.description) THEN
        SET @changes = JSON_SET(@changes, '$.description', JSON_OBJECT('old', OLD.description, 'new', NEW.description));
    END IF;

    IF (NEW.created_at <> OLD.created_at) THEN
        SET @changes = JSON_SET(@changes, '$.created_at', JSON_OBJECT('old', OLD.created_at, 'new', NEW.created_at));
    END IF;

    IF (NEW.updated_at <> OLD.updated_at) THEN
        SET @changes = JSON_SET(@changes, '$.updated_at', JSON_OBJECT('old', OLD.updated_at, 'new', NEW.updated_at));
    END IF;

    INSERT INTO models_hist(model_id, action, changes)
    VALUES (OLD.id, 'update', @changes);
END
|
DELIMITER ;

