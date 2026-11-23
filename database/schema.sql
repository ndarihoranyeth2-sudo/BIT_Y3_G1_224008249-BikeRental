-- ============================================================
-- Online Bike Rental System -- schema.sql (MySQL / MariaDB)
-- Author: NDARIHORANYE THIERRY (224008249)
-- Normal form: 3NF (see README section "Database design")
-- ============================================================

CREATE DATABASE IF NOT EXISTS bike_rental
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bike_rental;

DROP TABLE IF EXISTS rentals;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS bikes;
DROP TABLE IF EXISTS users;

-- Staff accounts (login / registration, passwords stored hashed)
CREATE TABLE users (
    user_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The fleet
CREATE TABLE bikes (
    bike_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(20)  NOT NULL UNIQUE,
    model       VARCHAR(80)  NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL CHECK (hourly_rate > 0),
    status      ENUM('available','rented','maintenance') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- People who rent bikes
CREATE TABLE customers (
    customer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    phone       VARCHAR(20)  NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per rental. total_cost is a historical snapshot (rate x billed
-- hours at return time) so later rate changes do not rewrite past invoices.
CREATE TABLE rentals (
    rental_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bike_id     INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    start_time  DATETIME NOT NULL,
    end_time    DATETIME NULL,
    total_cost  DECIMAL(10,2) NULL,
    status      ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_rental_bike
        FOREIGN KEY (bike_id) REFERENCES bikes(bike_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_rental_customer
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for the filters used by the list pages / live search
CREATE INDEX idx_bikes_status     ON bikes(status);          -- status filter
CREATE INDEX idx_rentals_status   ON rentals(status);        -- active/completed filter
CREATE INDEX idx_rentals_bike     ON rentals(bike_id);       -- bike history
CREATE INDEX idx_rentals_customer ON rentals(customer_id);   -- customer history
