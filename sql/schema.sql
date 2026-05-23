CREATE DATABASE IF NOT EXISTS lv4_glazba
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE lv4_glazba;

CREATE TABLE IF NOT EXISTS korisnici (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    korisnicko_ime  VARCHAR(50)  NOT NULL UNIQUE,
    lozinka         VARCHAR(255) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pjesme (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    naslov       VARCHAR(150) NOT NULL,
    autor        VARCHAR(150) NOT NULL,
    zanr         VARCHAR(80)  NOT NULL,
    bpm          SMALLINT     NOT NULL,
    godina       SMALLINT     NOT NULL,
    raspolozenje VARCHAR(80)  NOT NULL,
    trajanje     VARCHAR(10)  DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS playlista (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    korisnik_id INT NOT NULL,
    pjesma_id   INT NOT NULL,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY no_dupe (korisnik_id, pjesma_id),
    FOREIGN KEY (korisnik_id) REFERENCES korisnici(id) ON DELETE CASCADE,
    FOREIGN KEY (pjesma_id)   REFERENCES pjesme(id)    ON DELETE CASCADE
);
