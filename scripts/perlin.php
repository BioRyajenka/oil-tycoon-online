<?php
/*TODO: namespace MapGenerator;

use InvalidArgumentException;
use LogicException;
use SplFixedArray;*/

require "util.php";

/**
 * Class PerlinNoiseGenerator
 * Originally developed by A1essandro (https://github.com/A1essandro/perlin-noise-generator/tree/master)
 */
class PerlinNoiseGenerator {
    /**
     * @var \SplFixedArray[]
     */
    private $terra;
    private $persistence;
    private $size;

    const SIZE = 'size';
    const PERSISTENCE = 'persistence';
    const GRAIN = 'grain';

    /**
     * @param array $options
     *
     * @return \SplFixedArray[]
     */
    public function generate(array $options = array()) {
        $this->setOptions($options);
        $this->initTerra();

        // from 1 to be less smooth
        for ($k = 1; $k < $this->getOctaves(); $k++) {
            $this->octave($k);
        }

        return $this->terra;
    }

    public function getResult() {
        return $this->terra;
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options) {
        if (array_key_exists(static::SIZE, $options)) {
            $this->setSize($options[static::SIZE]);
        }
        if (array_key_exists(static::PERSISTENCE, $options)) {
            $this->setPersistence($options[static::PERSISTENCE]);
        }
        if (array_key_exists(static::GRAIN, $options)) {
            $this->setGrain($options[static::GRAIN]);
        }
    }

    private $grain;

    private function setGrain($grain) {
        $this->grain = $grain;
    }

    private function octave($octave) {
        if (isset($this->grain)) {
            $octave = $this->grain;
        }
        $freq = pow(2, $octave);
        $amp = pow($this->persistence, $octave);

        $n = $m = $freq + 1;

        $arr = array();
        for ($j = 0; $j < $m; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $arr[$j][$i] = $this->random() * $amp;
            }
        }

        $nx = $this->size / ($n - 1);
        $ny = $this->size / ($m - 1);

        for ($ky = 0; $ky < $this->size; $ky++) {
            for ($kx = 0; $kx < $this->size; $kx++) {
                $i = (int)($kx / $nx);
                $j = (int)($ky / $ny);

                $dx0 = $kx - $i * $nx;
                $dx1 = $nx - $dx0;
                $dy0 = $ky - $j * $ny;
                $dy1 = $ny - $dy0;

                $z = ($arr[$j][$i] * $dx1 * $dy1
                        + $arr[$j][$i + 1] * $dx0 * $dy1
                        + $arr[$j + 1][$i] * $dx1 * $dy0
                        + $arr[$j + 1][$i + 1] * $dx0 * $dy0)
                    / ($nx * $ny);

                $this->terra[$ky][$kx] += $z;
            }
        }
    }

    /**
     * terra array initialization
     */
    private function initTerra() {
        if (!$this->persistence) {
            throw new LogicException('Persistence must be set');
        }

        if (!$this->size) {
            throw new LogicException('Size must be set');
        }

        $this->terra = new SplFixedArray($this->size);
        for ($y = 0; $y < $this->size; $y++) {
            $this->terra[$y] = new SplFixedArray($this->size);
            for ($x = 0; $x < $this->size; $x++) {
                $this->terra[$y][$x] = 0;
            }
        }
    }

    /**
     * Getting random float from 0 to 1
     *
     * @return float
     */
    private function random() {
        return mt_rand() / mt_getrandmax();
    }

    private function getOctaves() {
        return (int)log($this->size, 2);
    }

    /**
     * @param int $size
     */
    private function setSize($size) {
        if (!is_int($size)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Sizes must be int , %s given", gettype($size)
                )
            );
        }

        $this->size = $size;
    }

    /**
     * @param float $persistence
     */
    private function setPersistence($persistence) {
        if (!is_numeric($persistence)) {
            throw new InvalidArgumentException(sprintf("persistence must be numeric, %s given", gettype($persistence)));
        }

        $this->persistence = $persistence;
    }

    public function getMaxValue() {
        $res = 0;
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                $res = max($res, $this->terra[$i][$j]);
            }
        }
        return $res;
    }

    public function getSummaryValue() {
        $res = 0;
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                $res += $this->terra[$i][$j];
            }
        }
        return $res;
    }

    public function toImageHtmlTag($imageWidth = "10%") {
        $max = $this->getMaxValue();
        $temp = array();
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                $temp[$i][$j] = min($this->terra[$i][$j] / $max * 256, 255);
            }
        }

        return pixelsArrayToImageHtmlTag($temp, $imageWidth);
    }
}

/*$generator = new PerlinNoiseGenerator();
$generator->generate([
    PerlinNoiseGenerator::SIZE => 15,
    PerlinNoiseGenerator::PERSISTENCE => 1,
    PerlinNoiseGenerator::GRAIN => 3
]);
$generator->printImage();*/