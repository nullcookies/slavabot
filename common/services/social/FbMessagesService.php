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
    protected $fbService;

    protected $fbApi;

    public function __construct()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();

        $this->command = new FrontendNotificationCommand($telegram);

        $this->fbService = new FacebookService();

        $this->fbApi = $this->fbService->init();
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
                    isset($response['picture']['url'])? $response['picture']['url']: ''
                );
            }

            $response = $this->getSenderInfoByPSID($fromId);

            if($response) {
                Logger::info('SENDER INFO FOR: ' . $fromId . ' - ' . json_encode($response));

                SocialDialoguesPeerFb::saveFbPeer(
                    $response['id'],
                    $response['name'],
                    isset($response['picture']['url'])? $response['picture']['url']: ''
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
        $token = $this->users[0]->data->groups->access_token;
        $from = $this->fbService->getRealLink($this->fbApi, $message['sender']['id'], $token);
        $path = trim(parse_url($from['link'], PHP_URL_PATH), '/');
        $peerId = explode('/', $path);
        Logger::info('PEER: ' . json_encode($peerId));


        foreach ($this->users as $user) {
            SocialDialoguesFbMessages::newFbMessage(
                $user->user_id,
                $user->data->groups->id,
                $peerId[1],
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
                $peerId[1],
                $senderName,
                isset($response['picture']['url'])? $response['picture']['url']: null,
                $response['id']
            );
        }

        $text = $this->getMessageForTelegram($message);

        $this->sendToTelegram(
            $senderName,
            $peerId[1],
            $text
        );
    }

    protected function getSenderInfoByPSID($psid)
    {
        $token = $this->users[0]->data->groups->access_token;

        $response = $this->fbService->getUserInfoByPSID($this->fbApi, $psid, $token);

        $response['picture'] = $this->fbService->getPictureByPSID($this->fbApi, $psid, $token);

        return $response;
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