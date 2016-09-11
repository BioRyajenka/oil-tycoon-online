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
    ob_start();
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

function generatePlayerColor() {
    $c1 = [
        "r" => 170,
        "g" => 150,
        "b" => 100
    ];

    function hue($c) {
        $max = $min = $r = $c['r'] / 255;
        $g = $c['g'] / 255;
        $b = $c['b'] / 255;
        if ($g > $max) $max = $g;
        if ($b > $max) $max = $b;
        if ($g < $min) $min = $g;
        if ($b < $min) $min = $b;
        $delta = $max - $min;
        if ($delta == 0) {
            return 0;
        }

        $res = 0;
        if ($r == $max) $res = ($g - $b) / $delta;
        if ($g == $max) $res = 2 + ($b - $r) / $delta;
        if ($b == $max) $res = 4 + ($r - $g) / $delta;

        $res *= 60;
        if ($res < 0) $res += 360;
        return $res;
    }

    function f($x) {
        $x /= 100;
        return max($x, $x * $x);
    }

    function prob($x, $y, $z) {
        return f($x) * f($y) * f($z) * min(f($y * 2) * f($y * 2), 1);
    }

    function calcColorProb($c2) {
        global $c1;
        $r = abs($c1['r'] - $c2['r']);
        $g = abs($c1['g'] - $c2['g']);
        $b = abs($c1['b'] - $c2['b']);

        $x = intval(abs(hue($c1) - hue($c2)));
        $y = max($r, $g, $b);
        $z = min($r, $g, $b);
        return prob($x, $y, $z);
    }

    function generateRandomColor() {
        return [
            "r" => rand(0, 255),
            "g" => rand(0, 255),
            "b" => rand(0, 255)
        ];
    }

    function colorToString($c) {
        return "#" . dechex($c['r']) . dechex($c['g']) . dechex($c['b']);
    }

    while (calcColorProb($color = generateRandomColor()) < .2);
    return colorToString($color);
}