<?php
if (!isset($_GET['code'])) die;

$code = $_GET['code'];

$bgNum = round(mt_rand(1, 3));
// realpath(dirname(__FILE__))
$resPath = dirname(__FILE__) . "/../res/captcha/";
$imagePath = $resPath . "bg" . $bgNum . ".jpg";
$fontPath = $resPath . "font.ttf";

$pic = imagecreatefromjpeg($imagePath);
$color = imagecolorallocate($pic, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
imagettftext($pic, 23, mt_rand(-5, 5), 3, 30, $color, $fontPath, $code);

ob_start();
imagejpeg($pic);
$temp = ob_get_clean();
imagedestroy($pic);

echo base64_encode($temp);