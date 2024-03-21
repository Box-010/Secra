<?php

namespace Secra\Repositories;

use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;

#[Provide(ImageRepository::class)]
#[Singleton]
class ImageRepository extends BaseRepository
{
  public function save(string $imageName): int
  {
    $stmt = $this->db->query(
      "INSERT INTO images (image_path) VALUES (:path)",
      ["path" => $imageName]
    );
    return $this->db->lastInsertId();
  }

  public function get(int $id): string
  {
    $stmt = $this->db->query(
      "SELECT image_path FROM images WHERE image_id = :id",
      ["id" => $id]
    );
    return $stmt->fetchColumn();
  }
}