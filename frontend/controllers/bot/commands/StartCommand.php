<?php

namespace Longman\TelegramBot\Commands\SystemCommands;


use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Symfony\Component\Yaml\Yaml;
use Libs\SalesBotApi;
use Libs\TelegramWrap;

class StartCommand extends SystemCommand
{
    protected $name = 'start';                      // Your command's name
    protected $description = 'Start command'; // Your command description
    protected $usage = '/start';                    // Usage of your command
    protected $version = '1.0.0';


    public function execute()
    {

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID
        $user_id = $message->getFrom()->getId();

        $SalesBot = new SalesBotApi();
        $arRequest = $SalesBot->getUserAccounts(['tid' => $user_id]);

        $data = ['chat_id' => $chat_id];

        if ( $arRequest == false ) {
            //если пользователь не подключен

            $data = $telConfig->getStartWindow($data);

        } else {

            $data = $telConfig->getMainWindow($data);
        }

        return Request::sendMessage($data);        // Send message!

    }
}