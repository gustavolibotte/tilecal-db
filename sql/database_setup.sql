CREATE DATABASE tilecal_db;
USE tilecal_db;


CREATE TABLE models (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    formatted_name VARCHAR(50) NOT NULL,
    description TEXT,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT unique_model_name UNIQUE(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE models_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    record_id INT UNSIGNED NOT NULL,
    user VARCHAR(20) NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    made_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE files(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    file_data MEDIUMBLOB NOT NULL, -- approx 16MB
    comments TEXT,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT unique_file_name UNIQUE(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE files_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    user VARCHAR(20) NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    made_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE equipments (
    id VARCHAR(20) PRIMARY KEY NOT NULL,
    model_id INT UNSIGNED NOT NULL,
    location VARCHAR(30) NOT NULL,
    status VARCHAR(30) NOT NULL,
    parent_id VARCHAR(20),
    comments TEXT,
    extra_params JSON,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT fk_equipment_model FOREIGN KEY (model_id) REFERENCES models(id),
    CONSTRAINT fk_equipment_parent FOREIGN KEY (parent_id) REFERENCES equipments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE equipments_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    record_id VARCHAR(20) NOT NULL,
    user VARCHAR(20) NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    made_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE equipments_to_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id VARCHAR(20) NOT NULL,
    file_id INT UNSIGNED NOT NULL,
    CONSTRAINT fk_equipments_to_files FOREIGN KEY (equipment_id) REFERENCES equipments(id),
    CONSTRAINT fk_files_to_equipments FOREIGN KEY (file_id) REFERENCES files(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE equipments_to_files_hist(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    user VARCHAR(20) NOT NULL,
    action ENUM('insert', 'update', 'delete') NOT NULL,
    changes JSON NOT NULL,
    made_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
