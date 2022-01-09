CREATE DATABASE snakesoar;
USE snakesoar;
CREATE USER 'snakesoar_user'@'localhost' IDENTIFIED BY '9?7!3(2f3c2f';
GRANT ALL PRIVILEGES ON snakesoar.* TO 'snakesoar_user'@'localhost';

CREATE TABLE user(
    user_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(60) NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    last_name VARCHAR(20) NOT NULL,
    role VARCHAR(7) NOT NULL, CHECK(role IN("student", "teacher", "admin")),
    verified BOOLEAN NOT NULL DEFAULT false
);

CREATE TABLE task(
    task_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    title VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    due DATE NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (owner_id) REFERENCES user(user_id)
);

CREATE TABLE `group`(
    group_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    name VARCHAR(50) NOT NULL UNIQUE,
    FOREIGN KEY (owner_id) REFERENCES user(user_id)
);

CREATE TABLE token(
    token VARCHAR(32) NOT NULL PRIMARY KEY,
    type VARCHAR(8) NOT NULL CHECK(type IN("email", "password")),
    user_id INT NOT NULL,
    expires DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);

CREATE TABLE group_member(
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    PRIMARY KEY (user_id, group_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (group_id) REFERENCES `group`(group_id)
);

CREATE TABLE task_recipient(
    task_id INT NOT NULL,
    group_id INT NOT NULL,
    PRIMARY KEY (task_id, group_id),
    FOREIGN KEY (task_id) REFERENCES task(task_id),
    FOREIGN KEY (group_id) REFERENCES `group`(group_id)
);