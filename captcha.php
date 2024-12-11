<?php
session_start();

// Generate a random alphanumeric captcha code
$captcha_code = '';
for ($i = 0; $i < 6; $i++) {
    $captcha_code .= chr(rand(48, 57)); // numbers 0-9
    $captcha_code .= chr(rand(65, 90)); // uppercase letters A-Z
    $captcha_code .= chr(rand(97, 122)); // lowercase letters a-z
}
$captcha_code = substr(str_shuffle($captcha_code), 0, 6); // shuffle and take 6 characters
$_SESSION["captcha"] = $captcha_code;

// Generate an image with the captcha code
$im = imagecreate(60, 20);
$bg = imagecolorallocate($im, 255, 255, 255);
$text_color = imagecolorallocate($im, 0, 0, 0);
imagestring($im, 5, 10, 5, $captcha_code, $text_color);
header("Content-type: image/png");
imagepng($im);
imagedestroy($im);
?>