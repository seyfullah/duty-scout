CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    phone VARCHAR(20),
    group_id INT,
    password VARCHAR(255)
);

CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    captain_id INT
);

CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date DATE,
    sabah INT,
    ogle INT,
    ikindi INT,
    aksam INT,
    yatsi INT
);

-- Veritabanı karakter setini değiştir
ALTER DATABASE pusulade_yigitler CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabloları ve sütunları değiştir
ALTER TABLE groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sütunlar için (gerekirse)
ALTER TABLE groups MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;