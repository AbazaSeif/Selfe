<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Colorise
 *
 * @author abaza
 */
class Colorise {

    //auxiliary variable
    const increase = 100; //100
    //color reduction ratio
    const color_reduction = 60; //55
    // percentage that colour needs to reach of total
    // pixels for colour to be considered significant
    const threshold_filter = 1;

    //maximum size of the processed image

    private $resize_length = 1000000; //px 150
    //inicalize another variables
    private $image, $image_name = null;
    //inicalize another variables
    private $image_size, $_r, $_g, $_b = array();

    public function Get_Colors($Images, $Show_Table = false) {
        $ImageAnalisy = array(array());
        $this->loadImage($Images);
        $colors = $this->getColorsFromImage();

        if ($Show_Table) {
            echo '<div style="width: 390px; border: 1px solid silver; padding: 10px; float: left; margin: 20px;">';
            $IPath = array_slice(explode(DIRECTORY_SEPARATOR, realpath($Images)), 4);
            $URL = "http://" . $_SERVER['HTTP_HOST'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $IPath);
            echo '<img src="' . $URL . '" width="250px">';
            //Table
            //sort colors
            arsort($colors);
            //count of colors
            $sum = array_sum($colors);

            echo '<table rules=all style="border: 1px solid silver" cellpadding=7>';
            echo '<tr><th colspan=7>most used colors</th></tr>';
            echo '<tr><th></th><th>R</th><th>G</th><th>B</th><th>%</th><th>hex</th><th>Pix</th></tr>';
            foreach ($colors as $id => $color) {
                //calculation ratio of actual color in image
                $percent = round(($color / $sum) * 100, 5);
                //report only colors that have more than two percent
                if ($percent < 2) {
                    break;
                }
                //create html name of color
                $htmlcolor = $this->rgb2html($this->stringToColor($id));
                echo '<tr>';
                echo '<td><div style="float: left; width: 20px; height: 20px; background-color: ' . $htmlcolor . '"></div><td>';
                echo implode('</td><td>', $this->stringToColor($id));
                echo '<td>' . $percent . '%</td>';
                echo '<td>' . $htmlcolor . '</td>';
                echo '<td>' . $color . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
            return true;
        } else {
            $sum = array_sum($colors);
            $index = 0;
            foreach ($colors as $id => $color) {
                //calculation ratio of actual color in image
                $percent = round(($color / $sum) * 100, 5);
                //report only colors that have more than two percent
                if ($percent < 2) {
                    break;
                }
                //create html name of color
                $ImageAnalisy[$index]['html'] = $this->rgb2html($this->stringToColor($id));
                $ImageAnalisy[$index]['RGB'] = implode('|', $this->stringToColor($id));
                $ImageAnalisy[$index]['percent'] = $percent . '%';
                $ImageAnalisy[$index]['pix'] = $color;
                $index++;
            }
            return $ImageAnalisy;
        }
    }

    /**
     * Function load image by image name. Function only set image name into private variable
     * @param string $image_name
     * @return BOOL
     */
    public function loadImage($image_name) {
        if ($this->isImage($image_name)) {
            $this->setImage($image_name);
            return true;
        } else {
            //file isn't image. 
//        throw new ColoriseException('is not an image file');
            return false;
        }
    }

    /**
     * Return colors array from image
     * @return array
     */
    public function getColorsFromImage() {
        //default colors array
        $colors = array();

        //through pixel by pixel
        for ($i = 0; $i < $this->image_size[1]; $i++) {
            for ($j = 0; $j < $this->image_size[0]; $j++) {
                //Get the index of the color of a pixel
                $rgb = imagecolorat($this->getImage(), $j, $i);
                //Get the colors for an index
                $_rgb = imagecolorsforindex($this->getImage(), $rgb);

                //reduce the number of colors
//                $color = $_rgb;
                $color = $this->updateRGB($_rgb);
                //create unique color id for color
                // + self::increase - must be > 100 to create unique string
                $color_id = ( (int) ( (int) $color['red'] + self::increase) . (int) ( $color['green'] + self::increase ) . (int) ( $color['blue'] + self::increase ) );
                //set new color into colors array
                $colors[$color_id] = $colors[$color_id] + 1;
            }
        }
        arsort($colors);
        $ret_array = array();
        $i = 0;
        $threshold = ($this->image_size[1] * $this->image_size[0]) * (self::threshold_filter / self::increase);
        // build the return array of the top results
        foreach ($colors as $index => $count) {
            // make sure the count is high enough to be considered significant
            if ($count >= $threshold) {
                $ret_array[$index] = $count;
                $i++;
            } else {
                break;
            }
        }

        return $ret_array;
        //return colors
//        return ($colors);
    }

    /**
     * Convert string color name to RGB array
     * @param string $id
     */
    public function stringToColor($id) {
        $r = substr($id, 0, 3);
        $g = substr($id, 3, 3);
        $b = substr($id, 6, 3);
        return array($r - self::increase, $g - self::increase, $b - self::increase);
    }

    /**
     * Adjusts the color and reduce the number of color values
     * @param array $_rgb
     */
    private function updateRGB($_rgb) {
        foreach ($_rgb as &$value) {
            //reduction the number of colors in image
            $value = round(($value / self::color_reduction)) * self::color_reduction;
        }
        return $_rgb;
    }

    /**
     * Convert RGB color into html color
     * @param $r
     * @param $g
     * @param $b
     * @return string
     */
    public function rgb2html($r = -1, $g = -1, $b = -1) {
        if (is_array($r) && sizeof($r) == 3) {
            list($r, $g, $b) = $r;
        }

        $r = intval($r);
        $g = intval($g);
        $b = intval($b);

        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color = (strlen($r) < 2 ? '0' : '') . $r;
        $color .= (strlen($g) < 2 ? '0' : '') . $g;
        $color .= (strlen($b) < 2 ? '0' : '') . $b;
        return '#' . $color;
    }

    /**
     * Checks whether the file is an image 
     * @param string $image_name
     * @return BOOL
     */
    private function isImage($image_name) {
        return (bool) ($this->image_size = getimagesize($image_name));
    }

    /**
     * Return image name from private variable
     */
    private function getImageName() {
        return $this->image;
    }

    /**
     * Return image source from private variable
     */
    private function getImage() {
        return $this->image;
    }

    /**
     * Create image source from image name
     * @param string $image_name
     */
    private function setImage($image_name) {
        switch ($this->image_size[2]) {
            case 1: $image = imagecreatefromgif($image_name);
                break;
            case 2: $image = imagecreatefromjpeg($image_name);
                break;
            case 3: $image = imagecreatefrompng($image_name);
                break;
            default: throw new ColoriseException('failed image');
        }
        //resize image
        $this->resizeImage($image);
    }

    /**
     * Resize image to maximum size
     * @param Image source $image
     */
    private function resizeImage($image) {
        //set defautl values
        $width = $height = 0;
        //check image orientation and create new size
        if ($this->image_size[0] > $this->image_size[1]) {
            if ($this->image_size[1] > $this->resize_length) {
                $height = $this->resize_length;
                $width = round($this->image_size[0] / ($this->image_size[1] / $this->resize_length));
            }
        } else {
            if ($this->image_size[0] > $this->resize_length) {
                $width = $this->resize_length;
                $height = round($this->image_size[1] / ($this->image_size[0] / $this->resize_length));
            }
        }

        if (!$width) {
            $width = $this->image_size[0];
            $height = $this->image_size[1];
        }

        //create new image from image source
        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresized($thumb, $image, 0, 0, 0, 0, $width, $height, $this->image_size[0], $this->image_size[1]);

        //setup variables
        $this->image = $thumb;
        $this->image_size = array($width, $height);
    }

    /**
     * Set image name into private variable
     * @param string $image_name
     */
    private function setImageName($image_name) {
        $this->image_name = $image_name;
        $this->setImage($image_name);
    }

}
