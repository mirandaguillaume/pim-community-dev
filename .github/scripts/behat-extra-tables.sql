-- Extra tables required for Behat/Cypress tests that may not be created by migrations
-- These are session, configuration, and messenger tables

CREATE TABLE IF NOT EXISTS pim_session (
    sess_id VARBINARY(128) NOT NULL PRIMARY KEY,
    sess_data BLOB NOT NULL,
    sess_time INTEGER UNSIGNED NOT NULL,
    sess_lifetime INTEGER UNSIGNED NOT NULL
) COLLATE utf8mb4_bin, ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS pim_configuration (
    code VARCHAR(128) NOT NULL PRIMARY KEY,
    `values` JSON NOT NULL
) COLLATE utf8mb4_unicode_ci, ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS messenger_messages (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    body LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    headers LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    queue_name VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime)',
    available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime)',
    delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)',
    PRIMARY KEY (id),
    KEY IDX_75EA56E0FB7336F0 (queue_name),
    KEY IDX_75EA56E0E3BD61CE (available_at),
    KEY IDX_75EA56E016BA31DB (delivered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS pim_one_time_task (
    code VARCHAR(100) PRIMARY KEY,
    status VARCHAR(100) NOT NULL,
    start_time DATETIME,
    end_time DATETIME,
    `values` JSON NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
