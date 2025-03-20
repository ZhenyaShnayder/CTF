create database if not exists mysql_db;

use mysql_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Таблица для хранения сессий (cookie)
CREATE TABLE IF NOT EXISTS cookie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cookie VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица для хранения секретных слов
CREATE TABLE IF NOT EXISTS secrets (
    number INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    secret_word VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);
