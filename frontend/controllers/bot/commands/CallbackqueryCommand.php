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

use frontend\controllers\bot\libs\Logger;
use GuzzleHttp\Exception\RequestException;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\NotificationCommand;
use Longman\TelegramBot\Commands\UserCommands\AccountCommand;
use Longman\TelegramBot\Commands\UserCommands\CancelpostCommand;
use Longman\TelegramBot\Commands\UserCommands\AddpostCommand;
use Longman\TelegramBot\Commands\UserCommands\RollbackCommand;
use Longman\TelegramBot\Commands\UserCommands\PostCommand;
use Longman\TelegramBot\Commands\UserCommands\SendpostCommand;
use Longman\TelegramBot\Commands\UserCommands\SettimeCommand;
use Longman\TelegramBot\Commands\UserCommands\SettingsCommand;
use Longman\TelegramBot\Commands\UserCommands\ClearCommand;
use Longman\TelegramBot\Commands\UserCommands\TimeCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.1.1';
    protected $conversation;

    private $commands = [
        'code' => 'code',
        'email' => 'email',
        'post' => 'post',
        'sendpost' => 'sendpost',
        'cancelpost' => 'cancelpost',
        'addpost' => 'addpost',
        'publicpost' => 'publicpost',
        'planpost' => 'planpost',
        'settime' => 'settime',
        'rollback' => 'rollback',
    ];

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {


        Logger::info(__METHOD__, []);

        $update = $this->getUpdate();
        $callback_query = $update->getCallbackQuery();
        $user_id = $callback_query->getFrom()->getId();
        $data = $callback_query->getData();

        $command = explode('_', $data);
        $command = $command[0];




        $cb = $this->getUpdate()->getCallbackQuery();            // Get Message object
        $user = $cb->getFrom();
        $chat = $cb->getMessage()->getChat();
        $chat_id = $chat->getId();
        $user_id = $user->getId();


        Logger::info(__METHOD__, [
            'command' => $command,
            'data' => print_r($data, true)
        ]);

        if ($command == 'answer') {
            $explodedCommand = explode('_', $data);
            $param = $explodedCommand[1];
            $media = $explodedCommand[2].'_'.$explodedCommand[3];
            $this->conversation = new Conversation($user_id, $chat_id, 'notification');
            $notes = &$this->conversation->notes;
            $notes['state'] = 1;
            $notes['debug'] = true;
            $this->conversation->update();

            return (new NotificationCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->execute($param, $media);

        }

        if ($command == '/account') {
            return (new AccountCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }
        if ($command == '/settings') {
            return (new SettingsCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }
        if ($command == '/clear') {
            return (new ClearCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }
        if($command == '/time'){

            return (new TimeCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }


        if ($command == 'publicpost') {
            $this->conversation = new Conversation($user_id, $chat_id, 'post');
            $notes = &$this->conversation->notes;
            $notes['state'] = 2;
            $this->conversation->update();

            return (new PostCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }

        if ($command == 'planpost') {
            $this->conversation = new Conversation($user_id, $chat_id, 'post');
            $notes = &$this->conversation->notes;
            $notes['state'] = 3;
            $this->conversation->update();

            return (new PostCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }

        //установка времени
        if (strpos($command, 'UTC') !== false) {
            return (new SettimeCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->preExecute();
        }


        if (isset($this->commands[$command]) && $this->getTelegram()->getCommandObject($this->commands[$command])) {
            if ($command == 'post') {
                return (new PostCommand($this->telegram,
                    new Update(json_decode($this->update->toJson(), true))))->preExecute();
            }

            if ($command == 'sendpost') {
                return (new SendpostCommand($this->telegram,
                    new Update(json_decode($this->update->toJson(), true))))->preExecute();

            }

            if ($command == 'cancelpost') {
                return (new CancelpostCommand($this->telegram,
                    new Update(json_decode($this->update->toJson(), true))))->preExecute();

            }

            if ($command == 'addpost') {
                return (new AddpostCommand($this->telegram,
                    new Update(json_decode($this->update->toJson(), true))))->preExecute();

            }

        } else {
            $data = [];
            $data['callback_query_id'] = $callback_query->getId();
            $data['text'] = 'Invalid request! ' . $command;
            $data['show_alert'] = true;
            Request::answerCallbackQuery($data);
        }

        //return Request::answerCallbackQuery($data);


    }
}