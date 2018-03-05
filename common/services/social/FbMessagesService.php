<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 27.02.18
 * Time: 13:13
 */

namespace common\services\social;


use common\models\rest\Accounts;
use common\models\SocialDialoguesFbMessages;
use common\models\SocialDialoguesPeerFb;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\FrontendNotificationCommand;
use frontend\controllers\bot\libs\Logger;
use function React\Promise\all;

class FbMessagesService
{
    /**
     * @var $users Accounts[]
     */
    protected $users;

    /**
     * @var FrontendNotificationCommand
     */
    protected $command;

    /**
     * @var FacebookService
     */
    protected $fb;

    public function __construct()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();

        $this->command = new FrontendNotificationCommand($telegram);

        $this->fb = new FacebookService();
    }

    protected function getUser($pageId)
    {
        $accounts = Accounts::find()->andWhere([
            'fb_page' => $pageId,
            'type' => Accounts::TYPE_FB,
            'status' => 1,
            'processed' => 1
        ])->all();

        if($accounts) {
            foreach ($accounts as $key => $account) {
                $accounts[$key]->data = json_decode($account->data);
            }

            $this->users = $accounts;
        } else {
            throw new \InvalidArgumentException('Пользователи не найдены');
        }

    }

    public function readEntry(array $entry)
    {
        foreach ($entry as $item) {
            $this->getUser($item['id']);

            $this->readMessage($item['messaging'][0]);
        }
    }

    protected function readMessage(array $message)
    {
        foreach ($this->users as $user) {
            SocialDialoguesFbMessages::newFbMessage(
                $user->user_id,
                $user->data->groups->id,
                $message['sender']['id'],
                $message['message']['seq'],
                isset($message['message']['text'])? $message['message']['text']: '',
                isset($message['message']['attachments'])? json_encode($message['message']['attachments']): null
            );
        }

        $senderName = $message['sender']['id'];

        $response = $this->getSenderInfo($message['sender']['id']);

        if($response) {
            Logger::info(json_encode($response));

            $senderName = $response['first_name'] . ' ' . $response['last_name'];

            SocialDialoguesPeerFb::saveFbPeer(
                $response['id'],
                $senderName,
                $response['profile_pic']
            );
        }

        $text = $this->getMessageForTelegram($message);

        $this->sendToTelegram(
            $senderName,
            $message['sender']['id'],
            $text
        );
    }

    protected function getSenderInfo($psid)
    {
        $token = $this->users[0]->data->groups->access_token;

        $fb = $this->fb->init();

        return $response = $this->fb->getUserInfoByPSID($fb, $psid, $token);
    }

    protected function sendToTelegram($senderName, $senderId, $text)
    {
        foreach ($this->users as $user) {
            $this->command->prepareParams([
                'tid' => $user->userValue->telegram_id,
                'message' => $senderName.":\n".$text,
            ]);

            $this->command->execute($senderId);
        }

    }

    protected function getMessageForTelegram(array $message)
    {
        $text = isset($message['message']['text'])? $message['message']['text']: '';

        if(isset($message['message']['attachments'])) {
            foreach ($message['message']['attachments'] as $attach) {
                if($attach['type'] == 'fallback') {
                    $text .= "\n" . $attach['url'];
                } else {
                    $text .= "\n" . $attach['payload']['url'];
                }
            }
        }

        return $text;
    }
}