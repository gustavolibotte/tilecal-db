CREATE DATABASE tilecal_db;
USE tilecal_db;

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

CREATE TABLE files(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    file_data MEDIUMBLOB NOT NULL, -- approx 16MB
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT unique_file_name UNIQUE(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE files_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    file_id INT UNSIGNED NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DELIMITER |

CREATE TRIGGER files_hist_insert AFTER INSERT ON files
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', NEW.id,
        'name', NEW.name,
        'file_data', NEW.file_data,
        'comments', NEW.comments,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at
    );
    INSERT INTO files_hist(file_id, action, changes)
    VALUES (NEW.id, 'insert', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER files_hist_delete AFTER DELETE ON files
FOR EACH ROW
BEGIN
    SET @changes = JSON_OBJECT(
        'id', OLD.id,
        'name', OLD.name,
        'file_data', OLD.file_data,
        'comments', OLD.comments,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at
    );
    INSERT INTO files_hist(file_id, action, changes)
    VALUES (OLD.id, 'delete', @changes);
END
|
DELIMITER ;


DELIMITER |

CREATE TRIGGER files_hist_update AFTER UPDATE ON files
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

    IF (NEW.file_data <> OLD.file_data) THEN
        SET @changes = JSON_SET(@changes, '$.file_data', JSON_OBJECT('old', OLD.file_data, 'new', NEW.file_data));
    END IF;

    IF (NEW.comments <> OLD.comments) THEN
        SET @changes = JSON_SET(@changes, '$.comments', JSON_OBJECT('old', OLD.comments, 'new', NEW.comments));
    END IF;

    IF (NEW.created_at <> OLD.created_at) THEN
        SET @changes = JSON_SET(@changes, '$.created_at', JSON_OBJECT('old', OLD.created_at, 'new', NEW.created_at));
    END IF;

    IF (NEW.updated_at <> OLD.updated_at) THEN
        SET @changes = JSON_SET(@changes, '$.updated_at', JSON_OBJECT('old', OLD.updated_at, 'new', NEW.updated_at));
    END IF;

    INSERT INTO files_hist(file_id, action, changes)
    VALUES (OLD.id, 'update', @changes);
END
|
DELIMITER ;

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

