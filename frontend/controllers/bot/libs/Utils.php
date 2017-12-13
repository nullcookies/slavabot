<?php
/**
 * Created by PhpStorm.
 * User: shakinm@gmail.com
 * Date: 30.10.2017
 * Time: 22:49
 */

namespace frontend\controllers\bot\libs;

/**
 * Class Utils
 * @package Libs
 */
class Utils
{
    /**
     * @param $number
     * @param array $titles array('комментарий','комментария','комментариев')
     * @return string
     */
   public static function human_plural_form($number, $titles=array('год','лет','лет')){
        $cases = array (2, 0, 1, 1, 1, 2);
        return $number." ".$titles[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
    }

}