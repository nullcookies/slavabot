<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 10.11.2017
 * Time: 10:10
 */

namespace frontend\controllers\bot\libs;

use common\services\StaticConfig;
use Longman\TelegramBot\Entities\Keyboard;

/**
 * Class TelegramWrap
 * @package Libs
 * Дублирующийся код для выдачи в телеграм
 */
class TelegramWrap
{

    public $config;

    /**
     * TelegramWrap constructor.
     */
    public function __construct()
    {
        //заполняем массив
        $this->config = StaticConfig::configBot('telegram');

        //убираем обратные слеши для вывода emoji
        $this->config['buttons']['email']['label'] = stripcslashes($this->config['buttons']['email']['label']);
        $this->config['buttons']['account']['label'] = stripcslashes($this->config['buttons']['account']['label']);
        $this->config['buttons']['clear']['label'] = stripcslashes($this->config['buttons']['clear']['label']);
        $this->config['buttons']['menu']['label'] = stripcslashes($this->config['buttons']['menu']['label']);
        $this->config['buttons']['repeatcode']['label'] = stripcslashes($this->config['buttons']['repeatcode']['label']);
        $this->config['buttons']['post']['label'] = stripcslashes($this->config['buttons']['post']['label']);
        $this->config['buttons']['settings']['label'] = stripcslashes($this->config['buttons']['settings']['label']);
        $this->config['buttons']['time']['label'] = stripcslashes($this->config['buttons']['time']['label']);
        $this->config['timezones']['active'] = stripcslashes($this->config['timezones']['active']);

    }

    /**
     * Вывод стартового окна
     * @return array
     */
    public function getStartWindow(array $arDate)
    {

        //текст приветствия
        $str = "Добро пожаловать!\n";
        $str .= "Команды:\n";
        //описание для email
        $str .= sprintf("%s - %s.",
            $this->config['buttons']['email']['command'],
            $this->config['buttons']['email']['description']
        );
        $arDate['text'] = $str;

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['email']['label']],
            ]
        );

        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);

        return $arDate;
    }


    /**Вывод окна запроса email
     *
     * @param array $arDate
     *
     * @return array
     */
    public function getEmailWindow(array $arDate)
    {

        //текст приглашения
        $str = "Введите email аккаунта SlavaBot:\n";
        $arDate['text'] = $str;

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );

        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);

        return $arDate;
    }

    public function getWrongEmailWindow(array $arDate, $text = '<email>')
    {
        //текст приглашения
        $str = "Пользователь {$text} не найден.\n";
        $str .= "Введите email аккаунта SlavaBot:\n";
        $arDate['text'] = $str;

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['email']['label']],
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );

        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);

        return $arDate;
    }

    public function getErrorEmailWindow(array $arDate)
    {
        //текст приглашения
        $str = "Ошибка при подключении аккаунта. Попробуйте еще раз.\n";
        $arDate['text'] = $str;

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['email']['label']],
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );

        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);

        return $arDate;
    }


    /**Окно ввода кода
     *
     * @param array $arDate
     *
     * @return array
     */
    public function getCodeWindow(array $arDate, $notes = [])
    {

        //текст приглашения
        $str = "На {$notes['email']} отправлен код активации. Введите его:\n";
        $arDate['text'] = $str;

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['repeatcode']['label']],
                ['text' => $this->config['buttons']['email']['label']],
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );
        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);

        return $arDate;
    }


    public function getCodeWrongWindow(array $arDate)
    {

        //текст приглашения
        $str = "Неправильный код или email.\n";
        $str .= "Введите снова:.\n";
        $arDate['text'] = $str;

//        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['repeatcode']['label']],
                ['text' => $this->config['buttons']['email']['label']],
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );
        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);

        return $arDate;
    }


    /**Выводим главное меню
     *
     * @param array $arDate
     *
     * @return array
     */
    public function getMainWindow(array $arDate, $intro = 'Добро пожаловать. ', $buttons = ['post', 'menu', 'settings'])
    {
        $str = $intro."Выберите ваше действие:\n";

        $keyboardArray = array();

        foreach($buttons as $btn){
            $str .= sprintf("%s - %s.",
                    $this->config['buttons'][$btn]['command'],
                    $this->config['buttons'][$btn]['description']
                ) . "\n";

            $keyboardArray[] = ['text' => $this->config['buttons'][$btn]['label']];
        }

        $arDate['text'] = $str;

        $keyboard = new Keyboard($keyboardArray);

        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);


        return $arDate;
    }

    /**Окно отмены поста
     * @param array $arDate
     *
     * @return array
     */
    public function getPostCancelWindow(array $arDate)
    {

        //текст
        $str = "Отмена создания сообщения.\n";
        $arDate['text'] = $str;

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );
        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);


        return $arDate;

    }


    /*
     * Окно настроек
     */
    public function getSettingsWindow(array $arDate)
    {

        //текст
        $str = "Настройки публикации:\n";
        $str .= sprintf("%s - %s.",
                $this->config['buttons']['time']['command'],
                $this->config['buttons']['time']['description']
            ) . "\n";
        $str .= sprintf("%s - %s.",
                $this->config['buttons']['account']['command'],
                $this->config['buttons']['account']['description']
            ) . "\n";
        $arDate['text'] = $str;

        //кнопки
        $arDate = $this->getSettingsKeyboard($arDate);

        return $arDate;
    }


    /*
     * Клавиатура для настроек
     */
    public function getSettingsKeyboard(array $arDate)
    {

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['time']['label']],
                ['text' => $this->config['buttons']['account']['label']],
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );
        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);


        return $arDate;
    }

    /*
     * Клавиатура для настроек учетной записи
     */
    public function getAccountSettingsKeyboard(array $arDate)
    {

        //кнопки
        $keyboard = new Keyboard(
            [
                ['text' => $this->config['buttons']['clear']['label']],
                ['text' => $this->config['buttons']['menu']['label']],
            ]
        );
        $arDate['reply_markup'] = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true);


        return $arDate;
    }


}