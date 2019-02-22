<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\components
 * @category   CategoryName
 */

namespace lispa\amos\core\components;

use yii\base\Component;
use yii\log\Logger;

/**
 * All the utilities methods about the images
 * 
 * Add the components in the file configuration common/config/main.php
 * 
 * ```php
 * 'components' => [
 * //...
 *  'imageUtility' => [
 *           'class' => 'lispa\amos\core\components\ImageUtility', 
 *           ],
 * //...
 * ]
 * /...
 * ```
 *
 * Can use it in this mode
 * ```php
 * //The $model that have the method getAvatarUrl or similar (getAvatarUrl have to return an image's URL)
 * Yii::$app->imageUtility->getRoundImage($model)
 * ```
 * 
 */
class ImageUtility extends Component {

    private $width;
    private $height;
    private $marginLeft;
    public $moltiplicator = 144;
    public $divisor = 2;
    public $marginAdded = 20;
    public $classHeight = 'full-height';
    public $classWidth = 'full-width';
    public $classSquare = 'square-img';
    public $sizeAvatar = 'original';
    public $methodGetImageUrl = 'getAvatarUrl';

    /**
     * This method return the attributes (class, margin-top and margin-left) of the $model's image
     * @param object $model Object that have the method getAvatarUrl or similar (getAvatarUrl have to return an image's URL)
     * @return array The array with the value of class, margin-left and margin-top
     */
    public function getRoundImage($model) {
        $newProperty = ['class' => $this->classSquare, 'margin-left' => 0, 'margin-top' => 0];

//        $marginLeft = $marginTop = 0;
//        if (method_exists($model, $this->methodGetImageUrl)) {
//            try {
//                list($width, $height, $type, $attr) = getimagesize(\Yii::$app->urlManager->createAbsoluteUrl($model->{$this->methodGetImageUrl}($this->sizeAvatar)));
//                bcscale(10);
//                $newProperty['width'] = $width;
//                $newProperty['height'] = $height;
//
//                if ($width > $height) {             //horizontal image
//                    $newProperty['class'] = $this->classHeight;
//                    $newProperty['width'] = bcdiv(bcmul($this->moltiplicator, $width), $height);
//                    $newProperty['margin-left'] = -((($width-$height)/$width)*100);
//                    //-(bcdiv($newProperty['width'], $this->divisor));
//                } elseif ($width < $height) {       //vertical image
//                    $newProperty['class'] = $this->classWidth;
//                    $newProperty['height'] = bcdiv(bcmul($this->moltiplicator, $height), $width);
//                    $newProperty['margin-top'] = -((($height-$width)/$height)*100);
//                    //bcadd(-(bcdiv($newProperty['height'], $this->divisor)), $this->marginAdded);
//                } else {
//                    if ($width == $height) {                         //square image
//                        $newProperty['class'] = $this->classSquare;
//                    }
//                }
//            }catch (\Exception $e){
//                \Yii::getLogger()->log($e->getMessage(), Logger::LEVEL_ERROR);
//            }
//        }
        return $newProperty;
    }

    /**
     * This method return the attributes class of the $model's image
     * @param object $model Object that have the method getAvatarUrl or similar (getAvatarUrl have to return an image's URL)
     * @return array The array with the value of class
     */
    public function getRoundRelativeImage($model) {
        $newProperty = ['class' => ''];

        if (method_exists($model, $this->methodGetImageUrl)) {
            try{
                list($width, $height, $type, $attr) = getimagesize(\Yii::$app->urlManager->createAbsoluteUrl($model->{$this->methodGetImageUrl}($this->sizeAvatar)));
                bcscale(10);
                $newProperty['widht'] = $width;
                $newProperty['height'] = $height;

                if ($width > $height) {
                    $newProperty['class'] = $this->classHeight;
                } elseif ($width < $height) {
                    $newProperty['class'] = $this->classWidth;
                }else{
                    $newProperty['class'] = $this->classSquare;
                }
            } catch (\Exception $e){
                \Yii::getLogger()->log($e->getMessage(), Logger::LEVEL_ERROR);
            }
        }
        return $newProperty;
    }

    /**
     * This method return the attributes (class, margin-top and margin-left) of the $model's image - used for HORIZONTAL CONTAINER
     * @param object $model Object that have the method getAvatarUrl or similar (getAvatarUrl have to return an image's URL)
     * @return array The array with the value of class, margin-left and margin-top
     */
    public function getHorizontalImage($model) {
        $newProperty = ['class' => '', 'margin-left' => 0, 'margin-top' => 0];

        $marginLeft = $marginTop = 0;
        if (method_exists($model, $this->methodGetImageUrl)) {
            try{
                list($width, $height, $type, $attr) = getimagesize(\Yii::$app->urlManager->createAbsoluteUrl($model->{$this->methodGetImageUrl}($this->sizeAvatar)));
                bcscale(10);
                $newProperty['width'] = $width;
                $newProperty['height'] = $height;
                if ($width > $height) {
                    $newProperty['class'] = $this->classHeight;
    //                $newProperty['width'] = bcdiv(bcmul($this->moltiplicator, $width), $height);
    //                $newProperty['margin-left'] = -(bcdiv($newProperty['width'], $this->divisor));
                } elseif ($width < $height) {
                    $newProperty['class'] = $this->classWidth;
                    $newProperty['height'] = bcdiv(bcmul($this->moltiplicator, $height), $width);
                    $newProperty['margin-top'] = bcadd(-(bcdiv($newProperty['height'], $this->divisor)), $this->marginAdded);
                }else{
                    $newProperty['class'] = $this->classSquare;
                }
            } catch (\Exception $e){
                \Yii::getLogger()->log($e->getMessage(), Logger::LEVEL_ERROR);
            }
        }

        return $newProperty;
    }

}
