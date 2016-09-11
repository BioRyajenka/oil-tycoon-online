<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 07.09.2016
 * Time: 3:17
 */

/**
 * Redirects to another page. Should be called from within <b>head</b> or <b>body</b> tags
 * @param $url
 */
function redirect($url) {
    echo <<<SCRIPT
    <script type="text/javascript">
        window.location.replace("$url");
    </script>
SCRIPT;
}

function pixelsArrayToImageHtmlTag($arr, $imageWidth = null) {
    $width = count($arr[0]);
    $height = count($arr);

    // generating image
    $im = imagecreatetruecolor($width, $height);

    for ($i = 0; $i < $width; $i++) {
        for ($j = 0; $j < $height; $j++) {
            $intensity = $arr[$i][$j];
            $color = imagecolorallocate($im, $intensity, $intensity, $intensity);
            imagesetpixel($im, $i, $j, $color);
        }
    }

    // drawing an image
    ob_start(function ($c) {
        return base64_encode($c);
    });
    imagejpeg($im);
    $data = base64_encode(ob_get_clean());
    imagedestroy($im);

    $res = "<img src='data:image/jpeg;base64,$data'";
    if (!is_null($imageWidth)) {
        $res .= "width='$imageWidth'";
    }
    $res .= ">";
    return $res;
}