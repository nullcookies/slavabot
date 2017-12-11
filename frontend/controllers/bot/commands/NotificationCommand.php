<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 04.12.2017
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class NotificationCommand extends UserCommand
{
    protected $name = 'notification';
    protected $description = 'Уведомления';
    protected $usage = '/notification';
    protected $version = '1.0.0';
    protected $conversation;

    protected $_params;

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $chat_id = $this->_params['tid'];
        $message = $this->_params['message'];

        try {
            $result = Request::sendMessage([
                'chat_id' => $chat_id,
                'user_id' => $chat_id,
                'text' => $message
            ]);
            return $result;
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function prepareParams($_params = [])
    {
        $this->_params = $_params;
    }
}