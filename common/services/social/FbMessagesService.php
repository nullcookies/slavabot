<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 27.02.18
 * Time: 13:13
 */

namespace common\services\social;


use common\models\rest\Accounts;
use common\models\SocialDialoguesFbComments;
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

            if(isset($item['messaging'])) {
                $this->readMessage($item['messaging'][0]);
            }
            if(isset($item['changes'])) {
                if($item['changes'][0]['value']['item'] == 'comment') {
                    $this->readComment($item['changes'][0]['value'], $item['id'] );
                }
            }
        }
    }

    protected function readComment(array $comment, $fromId)
    {
        $postId = 0;
        if($fromId != $comment['sender_id'] && ($comment['verb'] == 'add' || $comment['verb'] == 'edited')) {
            foreach ($this->users as $user) {
                $exploded = explode('_', $comment['post_id']);
                $postId = $exploded[1];

                $exploded = explode('_', $comment['comment_id']);
                $commentId = $exploded[1];

                $edited = 0;

                if($comment['verb'] == 'edited') {
                    $edited = 1;
                }

                $attaches = [];
                if(isset($comment['photo'])) {
                    $attaches['photo'] = $comment['photo'];
                }
                if(isset($comment['video'])) {
                    $attaches['video'] = $comment['video'];
                }

                SocialDialoguesFbComments::newFbComment(
                    $user->user_id,
                    $user->data->groups->id,
                    $postId,
                    $commentId,
                    isset($comment['message'])? $comment['message']: '',
                    !empty($attaches)? json_encode($attaches): null,
                    $comment['sender_id'],
                    $edited
                );

            }


            $senderName = $comment['sender_id'];

            $response = $this->getSenderInfoByPSID($comment['sender_id']);

            if($response) {
                Logger::info('SENDER INFO FOR: ' . $comment['sender_id'] . ' - ' . json_encode($response));

                $senderName = $response['name'];

                SocialDialoguesPeerFb::saveFbPeer(
                    $response['id'],
                    $senderName,
                    isset($response['cover']['source'])? $response['cover']['source']: ''
                );
            }

            $response = $this->getSenderInfoByPSID($fromId);

            if($response) {
                Logger::info('SENDER INFO FOR: ' . $fromId . ' - ' . json_encode($response));

                SocialDialoguesPeerFb::saveFbPeer(
                    $response['id'],
                    $senderName,
                    isset($response['cover']['source'])? $response['cover']['source']: ''
                );
            }

            $text = $this->getCommentForTelegram($comment);

            $this->sendToTelegram(
                $senderName,
                $fromId,
                $text,
                $postId
            );


        }
    }

    protected function getCommentForTelegram(array $comment)
    {
        $text = isset($comment['message'])? $comment['message']: '';

        $text .= isset($comment['photo'])? "\n".$comment['photo']: '';
        $text .= isset($comment['video'])? "\n".$comment['video']: '';

        return $text;
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

        $response = $this->getSenderInfoByPSID($message['sender']['id']);

        if($response) {
            Logger::info('SENDER INFO FOR: ' . $message['sender']['id'] . ' - ' . json_encode($response));

            $senderName = $response['name'];

            SocialDialoguesPeerFb::saveFbPeer(
                $response['id'],
                $senderName,
                isset($response['cover']['source'])? $response['cover']['source']: null
            );
        }

        $text = $this->getMessageForTelegram($message);

        $this->sendToTelegram(
            $senderName,
            $message['sender']['id'],
            $text
        );
    }

    protected function getSenderInfoByPSID($psid)
    {
        $token = $this->users[0]->data->groups->access_token;

        $fb = $this->fb->init();

        return $response = $this->fb->getUserInfoByPSID($fb, $psid, $token);
    }

    protected function sendToTelegram($senderName, $senderId, $text, $mediaId = 0)
    {
        foreach ($this->users as $user) {
            $this->command->prepareParams([
                'tid' => $user->userValue->telegram_id,
                'message' => $senderName.":\n".$text,
            ]);

            $this->command->execute($senderId, $mediaId);
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