<?php

namespace Dore\Core\Captcha;

use Dore\Core\Exception\EnvironmentExceptions\NotExistsException;

/**
 * Class Captcha
 * @package Dore\Core\Captcha
 */
class Captcha
{

    /**
     * @var int Image Width
     */
    private $width = 160;

    /**
     * @var int Image Height
     */
    private $height = 50;

    /**
     * @var int The minimum length of Captcha
     */
    private $lenghtMin = 3;

    /**
     * @var int The maximum length of Captcha
     */
    private $lenghtMax = 5;

    /**
     * @var int Default font size
     */
    private $fontSize = 30;

    /**
     * @var int
     */
    private $numberLines = 25;

    /**
     * @var string
     */
    private $dirFonts;

    /**
     * @var string Symbols used in Captcha
     */
    private $letters = '23456789abcdeghkmnpqsuvxyz';

    public function __construct($dirFonts)
    {
        $this->dirFonts = $dirFonts;
    }

    /**
     * @param int $width
     *
     * @return Captcha
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param int $height
     *
     * @return Captcha
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param int $lenghtMin
     *
     * @return Captcha
     */
    public function setLenghtMin($lenghtMin)
    {
        $this->lenghtMin = $lenghtMin;
        return $this;
    }

    /**
     * @param int $lenghtMax
     *
     * @return Captcha
     */
    public function setLenghtMax($lenghtMax)
    {
        $this->lenghtMax = $lenghtMax;
        return $this;
    }

    /**
     * @param int $fontSize
     *
     * @return Captcha
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;
        return $this;
    }

    /**
     * @param int $numberLines
     *
     * @return Captcha
     */
    public function setNumberLines($numberLines)
    {
        $this->numberLines = $numberLines;
        return $this;
    }

    /**
     * @param string $letters
     *
     * @return Captcha
     */
    public function setLetters($letters)
    {
        $this->letters = $letters;
        return $this;
    }


    /**
     * Captcha code generation
     * @return string
     */
    public function generateCode()
    {
        $lenght = mt_rand($this->lenghtMin, $this->lenghtMax);

        do {
            $code = substr(str_shuffle(str_repeat($this->letters, 3)), 0, $lenght);
        } while (preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $code));

        return $code;
    }


    /**
     * Captcha image generation
     *
     * @param $string
     *
     * @return string
     * @throws NotExistsException
     */
    public function generateImage($string)
    {
        if (!extension_loaded('gd')) {
            throw new NotExistsException("It looks like GD is not installed");
        }

        $image = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocate($image, mt_rand(180, 220), mt_rand(180, 220), mt_rand(180, 220)));
        $this->drawText($image, $this->prepareString($string));
        $this->drawLines($image, $this->numberLines);
        ob_start();
        imagepng($image);
        imagedestroy($image);
        return ob_get_clean();
    }

    /**
     * Choosing a random font from the list of available
     * @return string
     */
    private function chooseFont()
    {
        $fontsList = glob($this->dirFonts . '*.ttf');
        $font = basename($fontsList[mt_rand(0, count($fontsList) - 1)]);

        return $this->dirFonts . $font;
    }

    /**
     * @param string $string
     *
     * @return array
     */
    private function prepareString($string)
    {
        return str_split(strtolower($string));
    }

    /**
     * Drawing the lines on the image
     *
     * @param $image
     * @param $number
     */
    private function drawLines(&$image, $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $xStart = mt_rand(0, $this->width - 40);
            $yStart = mt_rand(0, $this->height);
            $xEnd = mt_rand(70, $this->width);
            $yEnd = mt_rand(0, $this->height);
            imageline($image, $xStart, $yStart, $xEnd, $yEnd, imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 170), mt_rand(0, 150)));
        }
    }

    /**
     * Drawing the text on the image
     *
     * @param resource $image
     * @param array    $captcha
     */
    private function drawText(&$image, array $captcha)
    {
        $len = count($captcha);

        for ($i = 0; $i < $len; $i++) {
            $capangle = mt_rand(-25, 25);
            $xPos = (($this->width / 2) / $len) + (($i * 30) + $i);
            $yPos = ($this->height / 2) + (($this->height / 5) + $i);
            $capcolor = imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 170), mt_rand(0, 150));
            imagettftext($image, $this->fontSize, $capangle, $xPos, $yPos, $capcolor, $this->chooseFont(), $captcha[$i]);

        }
    }
}
