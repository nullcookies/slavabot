<?php
/**
 * Created by PhpStorm.
 * User: shakinm@gmail.com
 * Date: 24.10.2017
 * Time: 9:45
 */

namespace frontend\controllers\bot\libs;


/**
 * Class Files
 * @package Libs
 */
class Files
{

    /**
     * Проверка на существование файлов
     *
     * @param array $paths
     * @return bool
     */
    public static function exists(array $paths)
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                unset($paths);
                unset($path);
               return false;
            }
        }
        return true;
    }

    public static function WaitExists ($paths, $i=0) {

        if (!Files::exists($paths) && $i<3) {
            Files::WaitExists($paths,$i++);
            sleep(15);
        }
        elseif ($i>=15) {
            return false;
        }
        else {
            return true;
        }


        return true;
    }
}