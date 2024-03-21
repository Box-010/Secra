<?php

namespace Secra\Controllers;

use Exception;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;
use Secra\Arch\Router\Attributes\Controller;
use Secra\Arch\Router\Attributes\Post;
use Secra\Arch\Router\BaseController;
use Secra\Services\ImageService;

#[Provide(ImageController::class)]
#[Singleton]
#[Controller('/images')]
class ImageController extends BaseController
{
  #[Inject] private ImageService $imageService;

  #[Post]
  public function uploadImage()
  {
    if (!isset($_FILES['image'])) {
      $this->json(['success' => false, 'message' => 'No image uploaded']);
      return;
    }
    $image = $_FILES['image'];
    try {
      $imageId = $this->imageService->uploadImage($image);
      $this->json(['success' => true, 'message' => 'Image uploaded', 'imageId' => $imageId], 201);
    } catch (Exception $e) {
      $this->json(['success' => false, 'message' => $e->getMessage()]);
    }
  }
}