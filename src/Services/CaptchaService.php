<?php

namespace Secra\Services;

use GdImage;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;

#[Provide(CaptchaService::class)]
#[Singleton]
class CaptchaService
{
  private function postRequest(
    string $url,
    array  $postData,
  ): array
  {
    $data = http_build_query($postData);
    $options = [
      'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $data,
        'timeout' => 10
      ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    preg_match('/([0-9])\d+/', $http_response_header[0], $matches);
    $responseCode = intval($matches[0]);
    if ($responseCode != 200) {
      return [
        "success" => true,
        "message" => "request geetest api fail"
      ];
    } else {
      $data = json_decode($result, true);
      if ($data["status"] == "success") {
        if ($data["result"] == "success") {
          return [
            "success" => true,
            "message" => "validate success"
          ];
        } else {
          return [
            "success" => false,
            "message" => $data["reason"]
          ];
        }
      } else {
        return [
          "success" => false,
          "message" => $data["msg"]
        ];
      }
    }
  }

  public function validateGeeTest4(
    string  $captchaId,
    string  $captchaKey,
    ?string $lotNumber,
    ?string $passToken,
    ?string $genTime,
    ?string $captchaOutput,
    string  $apiServer = "http://gcaptcha4.geetest.com"
  ): bool
  {
    if (!$lotNumber || !$passToken || !$genTime || !$captchaOutput) {
      return false;
    }
    $sign_token = hash_hmac('sha256', $lotNumber, $captchaKey);
    $query = [
      "lot_number" => $lotNumber,
      "pass_token" => $passToken,
      "gen_time" => $genTime,
      "captcha_output" => $captchaOutput,
      "sign_token" => $sign_token
    ];
    $url = sprintf($apiServer . "/validate" . "?captcha_id=%s", $captchaId);
    return $this->postRequest($url, $query)["success"];
  }

  /**
   * @param string $captchaId
   * @return GdImage captcha image
   */
  private function generateCaptchaImage(
    string $captchaId
  ): GdImage
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    $image = imagecreatetruecolor(100, 30);
    //将背景设置为白色的
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    //将白色铺满地图
    imagefill($image, 0, 0, $bgColor);
    $captchaCode = "";
    for ($i = 0; $i < 4; $i++) {
      $fontSize = 6;
      $fontColor = imagecolorallocate($image, rand(20, 100), rand(30, 100), rand(10, 200));
      $str = "123456789abcdefghjkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";//给出一个字符串，用于生成随机验证码
      $fontContent = substr($str, rand(0, strlen($str)), 1);//每次截取一个字符
      $captchaCode .= $fontContent;//拼接
      $x = ($i * 100 / 4) + rand(5, 10);
      $y = rand(5, 10);
      imagestring($image, $fontSize, $x, $y, $fontContent, $fontColor);
    }
    $_SESSION['captchaCode_' . $captchaId] = $captchaCode;
    for ($i = 0; $i < 200; $i++) {
      $pointColor = imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));
      imagesetpixel($image, rand(1, 99), rand(1, 29), $pointColor);
    }
    for ($i = 0; $i < 3; $i++) {
      $lineColor = imagecolorallocate($image, rand(80, 220), rand(80, 220), rand(80, 220));
      imageline($image, rand(1, 99), rand(1, 29), rand(1, 99), rand(1, 29), $lineColor);
    }
    return $image;
  }

  public function showCaptcha(
    string $captchaId
  ): void
  {
    $image = $this->generateCaptchaImage($captchaId);
    imagepng($image);
  }

  public function generateCaptchaBase64(
    string $captchaId
  ): string
  {
    ob_start();
    $this->showCaptcha($captchaId);
    $image_data = ob_get_contents();
    ob_end_clean();
    return 'data:image/png;base64,' . base64_encode($image_data);
  }

  public function validateCaptcha(
    string $captchaId,
    string $captchaCode
  ): bool
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    if (isset($_SESSION['captchaCode_' . $captchaId]) && strtolower($captchaCode) == strtolower($_SESSION['captchaCode_' . $captchaId])) {
      return true;
    } else {
      return false;
    }
  }
}