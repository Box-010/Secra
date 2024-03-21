<?php

namespace Secra\Services;

use Exception;
use GdImage;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Logger\ILogger;
use Secra\Repositories\ImageRepository;

#[Provide(ImageService::class)]
#[Singleton]
class ImageService
{
  #[Inject] private ImageRepository $imageRepository;
  #[Inject] private ILogger $logger;

  private readonly array $allowed_image_types;
  private readonly int $max_image_size;
  private readonly string $image_dir;

  public function __construct()
  {
    $this->allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $this->max_image_size = 10 * 1024 * 1024;
    $this->image_dir = dirname(__DIR__, 2) . '/usr/uploads';
  }

  /**
   * use GD library to create a new image from the uploaded file
   */
  private function new_image_from_file(
    string $image_tmp,
    string $image_type,
  ): GdImage|false
  {
    if ($image_type === 'image/jpeg') {
      return imagecreatefromjpeg($image_tmp);
    } elseif ($image_type === 'image/png') {
      return imagecreatefrompng($image_tmp);
    } elseif ($image_type === 'image/gif') {
      return imagecreatefromgif($image_tmp);
    }
    throw new Exception('Invalid image type');
  }

  private function new_image_name(
    string $image_name,
    string $image_type,
  ): string
  {
    return $image_name . '.' . str_replace('image/', '', $image_type);
  }

  private function write_image(
    GdImage $new_image,
    string  $image_path,
    string  $image_type,
  ): void
  {
    if ($image_type === 'image/jpeg') {
      imagejpeg($new_image, $image_path);
    } elseif ($image_type === 'image/png') {
      imagepng($new_image, $image_path);
    } elseif ($image_type === 'image/gif') {
      imagegif($new_image, $image_path);
    }
  }

  /**
   * @param array $image
   * @return int image id
   * @throws Exception
   */
  public function uploadImage(
    array $image,
  ): int
  {
    $image_tmp = $image['tmp_name'];
    $image_name = $image['name'];
    $image_size = $image['size'];
    $image_type = $image['type'];
    $image_error = $image['error'];

    if ($image_error !== UPLOAD_ERR_OK) {
      match ($image_error) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => throw new Exception('Image size too large'),
        default => throw new Exception('Image upload failed'),
      };
    }

    if (!in_array($image_type, $this->allowed_image_types)) {
      throw new Exception('Invalid image type');
    }

    if ($image_size > $this->max_image_size) {
      throw new Exception('Image size too large');
    }

    // Use GD library to check if the file is an image
    if (!getimagesize($image_tmp)) {
      throw new Exception('Invalid image');
    }

    // Hash the image name to avoid conflicts
    $image_name = uniqid() . "-" . hash('sha256', $image_name);
    $image_name = $this->new_image_name($image_name, $image_type);

    $image_path = $this->image_dir . '/tmp-' . $image_name;
    if (!move_uploaded_file($image_tmp, $image_path)) {
      $this->logger->error('Image upload failed: move_uploaded_file failed');
      throw new Exception('Image upload failed');
    }

    // Create a new image from the uploaded file
    $new_image = $this->new_image_from_file($image_path, $image_type);
    @unlink($image_path);
    if ($new_image === false) {
      $this->logger->error('Image upload failed: new_image_from_file failed');
      throw new Exception('Image upload failed');
    }
    $image_path = $this->image_dir . '/' . $image_name;
    $this->write_image($new_image, $image_path, $image_type);

    return $this->imageRepository->save($image_name);
  }
}