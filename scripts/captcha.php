<?php
//require "logger.php";

if (!isset($_GET['code'])) {
    die;
}
$code = $_GET['code'];

$bgNum = round(mt_rand(1, 3));
// realpath(dirname(__FILE__))
$resPath = dirname(__FILE__) . "/../res/";
$imagePath = $resPath . "captcha_bg" . $bgNum . ".jpg";
$fontPath = $resPath . "addict.ttf";

//header("Pragma: no-cache");

$pic = imagecreatefromjpeg($imagePath);
//header("Content-type: image/jpeg");
$color = imagecolorallocate($pic, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
//imagestring($pic, 4, 12, 1, $code, $color);
imagettftext($pic, 23, mt_rand(-5, 5), 3, 30, $color, $fontPath, $code);

ob_start();
imagejpeg($pic);
$temp = ob_get_clean();
//ob_get_flush();
imagedestroy($pic);

echo base64_encode($temp);