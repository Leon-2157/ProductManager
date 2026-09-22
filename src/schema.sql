-- Product Manager Schema
CREATE TABLE IF NOT EXISTS products (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(23)    NOT NULL,
    category VARCHAR(100)   NOT NULL,
    price    DECIMAL(12, 2) NOT NULL CHECK (price > 0),
    stock    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_product_name UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
