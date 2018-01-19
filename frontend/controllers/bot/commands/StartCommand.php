<?php

namespace Longman\TelegramBot\Commands\SystemCommands;


use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\TelegramWrap;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

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

            $data_wellcome = $telConfig->getStartWindow($data);
            $data_getEmail = $telConfig->getEmailWindow($data);
            try {
                Request::sendMessage($data_wellcome);
                Request::sendMessage($data_getEmail);
            } catch (TelegramException $e) {
                Logger::error('StartCommand', [
                    'message' => $e->getMessage()
                ]);
            }

        } else {

            $data = $telConfig->getMainWindow($data);

            try {
                Request::sendMessage($data);
            } catch (TelegramException $e) {
                Logger::error('StartCommand', [
                    'message' => $e->getMessage()
                ]);
            }
        }


    }
}