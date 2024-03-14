<?php
require_once(dirname(__DIR__) . '/config/admin.php');
require_once(dirname(__DIR__) . '/config/database.php');

// 先创建连接
try {
  $conn = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_ROOT_USER, DB_ROOT_PASS);
  // 设置 PDO 错误模式为异常
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully<br>";
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  exit;
}

// 创建数据库
try {
  $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
  // 使用 exec() ，没有结果返回
  $conn->exec($sql);
  echo "Database created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// 创建 Users 表
try {
  $conn->exec("use " . DB_NAME);
  $sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    salt VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );";
  $conn->exec($sql);
  echo "Table users created successfully<br>";
  // 创建默认管理员
  $randomSalt = bin2hex(random_bytes(32));
  $sql = "INSERT INTO users (user_name, password, salt, email) VALUES ('" . ADMIN_USERNAME . "', '" . password_hash(ADMIN_PASSWORD . $randomSalt, PASSWORD_DEFAULT) . "', '" . $randomSalt . "', '" . ADMIN_EMAIL . "')";
  $conn->exec($sql);
  echo "Default admin created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// 创建用户角色相关表
try {
  $sql = "CREATE TABLE IF NOT EXISTS roles (
    role_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL
  );";
  $conn->exec($sql);
  echo "Table roles created successfully<br>";
  $sql = "CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT UNSIGNED,
    role_id INT UNSIGNED,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
  );";
  $conn->exec($sql);
  echo "Table user_roles created successfully<br>";

  // 创建默认角色
  $sql = "INSERT INTO roles (role_name) VALUES ('user')";
  $conn->exec($sql);
  $sql = "INSERT INTO roles (role_name) VALUES ('admin')";
  $conn->exec($sql);
  echo "Default role created successfully<br>";

  // 分配默认角色
  $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (1, 2)";
  $conn->exec($sql);
  echo "Default role assigned successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// 创建权限表
try {
  $sql = "CREATE TABLE IF NOT EXISTS permissions (
    permission_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(50) UNIQUE NOT NULL
  );";
  $conn->exec($sql);
  echo "Table permissions created successfully<br>";
  $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED,
    permission_id INT UNSIGNED,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)
  );";
  $conn->exec($sql);
  echo "Table role_permissions created successfully<br>";

  // // 创建默认权限
  // $sql = "INSERT INTO permissions (permission_name) VALUES ('posts.create')";
  // $conn->exec($sql);
  // $sql = "INSERT INTO permissions (permission_name) VALUES ('posts.update')";
  // $conn->exec($sql);
  // $sql = "INSERT INTO permissions (permission_name) VALUES ('posts.delete')";
  // $conn->exec($sql);
  // $sql = "INSERT INTO permissions (permission_name) VALUES ('comments.create')";
  // $conn->exec($sql);
  // $sql = "INSERT INTO permissions (permission_name) VALUES ('comments.delete')";
  // $conn->exec($sql);
  // echo "Default permission created successfully<br>";

  // // 分配默认权限
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (1, 1)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (1, 4)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (1, 5)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 1)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 2)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 3)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 4)";
  // $conn->exec($sql);
  // $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 5)";
  // $conn->exec($sql);
  // echo "Default permission assigned successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// 创建 Sessions 表
try {
  $sql = "CREATE TABLE IF NOT EXISTS sessions (
    session_id VARCHAR(255) PRIMARY KEY,
    user_id INT UNSIGNED,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
  );";
  $conn->exec($sql);
  echo "Table sessions created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

try {
  $sql = "CREATE TABLE IF NOT EXISTS posts (
    post_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    nickname TEXT,
    author_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(user_id)
  );";
  $conn->exec($sql);
  echo "Table posts created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

try {
  $sql = "CREATE TABLE IF NOT EXISTS comments (
    comment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    user_id INT UNSIGNED,
    post_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    parent_comment_id INT UNSIGNED,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (post_id) REFERENCES posts(post_id)
  );";
  $conn->exec($sql);
  echo "Table comments created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

try {
  $sql = "CREATE TABLE IF NOT EXISTS likes (
    like_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    likeable_type VARCHAR(20) NOT NULL,
    likeable_id INT UNSIGNED NOT NULL,
    like_type ENUM('like', 'dislike') NOT NULL DEFAULT 'like',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, likeable_type, likeable_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
  );";
  $conn->exec($sql);
  echo "Table likes created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// Images
try {
  $sql = "CREATE TABLE IF NOT EXISTS images (
    image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    imageable_type VARCHAR(20) NOT NULL,
    imageable_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    INDEX (imageable_type, imageable_id)
  );";
  $conn->exec($sql);
  echo "Table images created successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// 创建用户并授权
try {
  $sql = "CREATE USER IF NOT EXISTS '" . DB_USER . "'@'" . DB_HOST . "' IDENTIFIED BY '" . DB_PASS . "'";
  $conn->exec($sql);
  echo "User created successfully<br>";
  $sql = "GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO '" . DB_USER . "'@'" . DB_HOST . "'";
  $conn->exec($sql);
  echo "User granted successfully<br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
  exit;
}

// 断开连接
$conn = null;
echo "Connection closed<br>";
