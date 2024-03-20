<?php

namespace Secra\Repositories;

use PDO;
use Secra\Components\DI\Attributes\Provide;
use Secra\Components\DI\Attributes\Singleton;
use Secra\Models\Role;
use Secra\Models\User;

#[Provide(UserRepository::class)]
#[Singleton]
class UserRepository extends BaseRepository
{
  public function getUserById(int $id): User|bool
  {
    $stmt = $this->db->query("SELECT * FROM v_user WHERE user_id = ?", [$id]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
    return $stmt->fetch();
  }

  public function getUserByUsername(string $user_name): User|bool
  {
    $stmt = $this->db->query("SELECT * FROM v_user WHERE user_name = ?", [$user_name]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
    return $stmt->fetch();
  }

  public function getUserByEmail(string $email): User|bool
  {
    $stmt = $this->db->query("SELECT * FROM v_user WHERE email = ?", [$email]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
    return $stmt->fetch();
  }

  public function save(User $user)
  {
    $this->db->query('INSERT INTO users (user_name, password, salt, email) VALUES (?, ?, ?, ?)', [
      $user->user_name,
      $user->password,
      $user->salt,
      $user->email,
    ]);
  }

  public function getRoleByName(string $role_name): Role|bool
  {
    $stmt = $this->db->query('SELECT * FROM roles WHERE role_name = ?', [$role_name]);
    $stmt->setFetchMode(PDO::FETCH_CLASS, Role::class);
    return $stmt->fetch();
  }

  public function assignRole(int $user_id, int $role_id)
  {
    $this->db->query('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)', [$user_id, $role_id]);
  }

  public function update(User $user)
  {
    $this->db->query('UPDATE users SET user_name = ?, password = ?, salt = ?, email = ? WHERE user_id = ?', [
      $user->user_name,
      $user->password,
      $user->salt,
      $user->email,
      $user->user_id,
    ]);
  }

  public function delete(int $id)
  {
    $this->db->query('DELETE FROM users WHERE user_id = ?', [$id]);
  }
}
