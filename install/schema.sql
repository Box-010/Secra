CREATE TABLE IF NOT EXISTS users
(
    user_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_name  VARCHAR(50)  NOT NULL,
    password   VARCHAR(255) NOT NULL,
    salt       VARCHAR(255) NOT NULL,
    email      VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles
(
    role_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name      VARCHAR(50) UNIQUE NOT NULL,
    parent_role_id INT UNSIGNED DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS user_roles
(
    user_id INT UNSIGNED,
    role_id INT UNSIGNED,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users (user_id),
    FOREIGN KEY (role_id) REFERENCES roles (role_id)
);

INSERT INTO roles (role_name)
VALUES ('user');

INSERT INTO roles (role_name, parent_role_id)
VALUES ('admin', 1);

CREATE TABLE IF NOT EXISTS sessions
(
    session_id VARCHAR(255) PRIMARY KEY,
    user_id    INT UNSIGNED,
    issued_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE IF NOT EXISTS posts
(
    post_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content    TEXT NOT NULL,
    nickname   TEXT,
    author_id  INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    image_ids  TEXT      DEFAULT NULL,
    FOREIGN KEY (author_id) REFERENCES users (user_id)
);

CREATE TABLE IF NOT EXISTS comments
(
    comment_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content           TEXT NOT NULL,
    nickname          TEXT         DEFAULT NULL,
    user_id           INT UNSIGNED,
    post_id           INT UNSIGNED,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    parent_comment_id INT UNSIGNED DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (user_id),
    FOREIGN KEY (post_id) REFERENCES posts (post_id)
);

CREATE TABLE IF NOT EXISTS attitudes
(
    attitude_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED,
    attitudeable_type VARCHAR(20)                   NOT NULL,
    attitudeable_id   INT UNSIGNED                  NOT NULL,
    attitude_type     ENUM ('positive', 'negative') NOT NULL DEFAULT 'positive',
    created_at        TIMESTAMP                              DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, attitudeable_type, attitudeable_id),
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE IF NOT EXISTS images
(
    image_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL
);

CREATE VIEW v_user AS
SELECT users.*,
       GROUP_CONCAT(roles.role_name SEPARATOR ',') AS roles,
       (SELECT COUNT(*)
        FROM posts
        WHERE posts.author_id = users.user_id)     AS secret_count
FROM users
         LEFT JOIN user_roles ON users.user_id = user_roles.user_id
         LEFT JOIN roles ON user_roles.role_id = roles.role_id
GROUP BY users.user_id;

CREATE VIEW v_secret AS
SELECT posts.*,
       u.user_id,
       u.user_name,
       u.email                                           AS user_email,
       u.created_at                                      AS user_created_at,
       (SELECT COUNT(*)
        FROM comments
        WHERE comments.post_id = posts.post_id)          AS comment_count,
       (SELECT COUNT(*)
        FROM attitudes
        WHERE attitudes.attitudeable_type = 'secrets'
          AND attitudes.attitude_type = 'positive'
          AND attitudes.attitudeable_id = posts.post_id) AS positive_count,
       (SELECT COUNT(*)
        FROM attitudes
        WHERE attitudes.attitudeable_type = 'secrets'
          AND attitudes.attitude_type = 'negative'
          AND attitudes.attitudeable_id = posts.post_id) AS negative_count
FROM posts
         INNER JOIN users u on posts.author_id = u.user_id;

CREATE VIEW v_comment AS
SELECT comments.*,
       u.user_name,
       u.email                                              AS user_email,
       u.created_at                                         AS user_created_at,
       (SELECT COUNT(*)
        FROM attitudes
        WHERE attitudes.attitudeable_type = 'comments'
          AND attitudes.attitude_type = 'positive'
          AND attitudes.attitudeable_id = comments.post_id) AS positive_count,
       (SELECT COUNT(*)
        FROM attitudes
        WHERE attitudes.attitudeable_type = 'comments'
          AND attitudes.attitude_type = 'negative'
          AND attitudes.attitudeable_id = comments.post_id) AS negative_count,
       (SELECT COUNT(*)
        FROM comments AS c
        WHERE c.post_id = comments.post_id
          AND c.comment_id <= comments.comment_id)          AS floor
FROM comments
         INNER JOIN users u on comments.user_id = u.user_id;