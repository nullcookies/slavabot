<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Carbon\Carbon;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Libs\TelegramWrap;

/**
 * Generic message command
 *
 * Gets executed when any type of message is sent.
 */
class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method if MySQL is required but not available
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function executeNoDb()
    {
        // Do nothing
        return Request::emptyResponse();
    }

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $user_id = $this->getMessage()->getFrom()->getId();
        $chat_id = $this->getMessage()->getChat()->getId();
        $message = $this->getMessage();

//        $dt=Carbon::createFromTimestampUTC($this->getMessage()->getDate());
//        $data['user_id']=$user_id;
//        $data['chat_id']=$chat_id;
//        $data['text']="Ваше время {$dt->toDayDateTimeString()}";
//        Request::sendMessage($data);

//        if ($message->getText() == "\xF0\x9F\x93\x83 Новый пост") {
//            $conversation = new Conversation($user_id, $chat_id, '/post');
//            if ($conversation->exists()) {
//                $conversation->cancel();
//            }
//
//         return $this->telegram->executeCommand('post');
//        }

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        //переводим все кнопки на страницы по имени комманд без первого слеша
        foreach ($telConfig->config['buttons'] as $button) {
            if ($message->getText() == $button['label']) {
                $conversation = new Conversation($user_id, $chat_id, $button['command']);
                if ($conversation->exists()) {
                    $conversation->cancel();
                }
                return $this->telegram->executeCommand(ltrim($button['command'],'/'));
            }
        }



        //If a conversation is busy, execute the conversation command after handling the message
        $conversation = new Conversation(
            $user_id,
            $chat_id
        );

        //Fetch conversation command if it exists and execute it
        if ($conversation->exists() && ($command = $conversation->getCommand())) {
            return $this->telegram->executeCommand($command);
        }

        return Request::emptyResponse();


    }
}