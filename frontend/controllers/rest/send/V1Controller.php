<?php
namespace frontend\controllers\rest\send;

use common\models\rest\Accounts;
use common\models\SocialDialoguesFbComments;
use common\models\SocialDialoguesFbMessages;
use common\models\SocialDialoguesInstagram;
use common\models\SocialDialoguesVkComments;
use common\services\social\FacebookService;
use frontend\controllers\bot\libs\Logger;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Created by PhpStorm.
 * User: igor
 * Date: 25.01.18
 * Time: 11:02
 */

class V1Controller extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'new-event'
                ],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'vk'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'new-event' => ['post']
                ],
            ],
//
//            'access' => [
//                'class' => AccessControl::className(),
//                'only' => [
//                    'vk'
//                ],
//                'rules' => [
//                    [
//                        'actions' => [
//                            'vk'
//                        ],
//                        'allow' => true,
//                        'roles' => ['?'],
//                    ],
//                ],
//            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'vk' => ['post']
                ],
            ],
        ];
    }

    /**
     * Отключаем проверку токена
     *
     * @param \yii\base\Action $action
     * @return bool
     */

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public static function actionVkMessage($user_id = '', $peer_id = '', $message = '')
    {
        if($user_id == '') {
            $user_id = Yii::$app->request->post('user_id');
            $peer_id = Yii::$app->request->post('peer_id');
            $message = Yii::$app->request->post('message');
        }


        if(!$account = Accounts::getByUserId($user_id, Accounts::TYPE_VK)) {
            throw new \InvalidArgumentException('Аккаунт не найден');
        }

        $data = json_decode($account->data);
        $group_access_token = $data->groups->access_token;

        $vk = new \frontend\controllers\bot\libs\Vk([
            'access_token' => $group_access_token
        ]);

        try {
            $result = $vk->api('messages.send', [
                'peer_id' => $peer_id,
                'message' => $message
            ]);

        } catch (\frontend\controllers\bot\libs\VkException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

    }

    public static function actionFbMessage($user_id = '', $peer_id = '', $message = '')
    {
        if($user_id == '') {
            $user_id = Yii::$app->request->post('user_id');
            $peer_id = Yii::$app->request->post('peer_id');
            $message = Yii::$app->request->post('message');
        }


        if(!$account = Accounts::getByUserId($user_id, Accounts::TYPE_FB)) {
            throw new \InvalidArgumentException('Аккаунт не найден');
        }

        $data = json_decode($account->data);
        $group_access_token = $data->groups->access_token;

        $accountId = $data->groups->id;

        try {
            $fbService = new FacebookService();

            $fbApi = $fbService->init();

            $result = $fbService->sendMessage($fbApi, $peer_id, $message, $group_access_token);

            $messageId = 0;

            SocialDialoguesFbMessages::newFbMessage(
                $user_id, $accountId, $peer_id, $messageId, $message, null,
                SocialDialoguesFbMessages::DIRECTION_OUTBOX
            );

        } catch (\Exception $e) {
            echo 'error: ' . $e->getMessage();
            Logger::info('error: ' . $e->getMessage());
            exit;
        }

    }

    public static function actionFbComment($user_id = '', $peer_id = '', $media_id = '', $message = '')
    {
        if ($user_id == '') {
            $user_id = Yii::$app->request->post('user_id');
            $media_id = Yii::$app->request->post('media_id');
            $peer_id = Yii::$app->request->post('peer_id');
            $message = Yii::$app->request->post('message');
        }


        if (!$account = Accounts::getByUserId($user_id, Accounts::TYPE_FB)) {
            throw new \InvalidArgumentException('Аккаунт не найден');
        }

        $data = json_decode($account->data);
        $group_access_token = $data->groups->access_token;

        $accountId = $data->groups->id;

        $postId = $peer_id .'_'.$media_id;

        try {
            $fbService = new FacebookService();

            $fbApi = $fbService->init();

            $result = $fbService->sendComment($fbApi, $postId, $message, $group_access_token);
            $result = $result->getGraphNode()->asArray();

            $commentId = explode('_', $result['id']);
            $commentId = $commentId[1];

            SocialDialoguesFbComments::newFbComment(
                $user_id, $accountId, $media_id, $commentId, $message, null, $peer_id, 0,
                SocialDialoguesFbComments::DIRECTION_OUTBOX
            );

        } catch (\Exception $e) {
            echo 'error: ' . $e->getMessage();
            Logger::info('error: ' . $e->getMessage());
            exit;
        }
    }

    /**
     * Комментарий вКонтакте
     *
     * @param string $user_id
     * @param string $peer_id
     * @param string $media_id
     * @param string $message
     */
    public static function actionVkComment($user_id = '', $peer_id = '', $media_id = '', $message = '')
    {
        if($user_id == '') {
            $user_id = Yii::$app->request->post('user_id');
            $media_id = Yii::$app->request->post('media_id');
            $peer_id = Yii::$app->request->post('peer_id');
            $message = Yii::$app->request->post('message');
        }


        if(!$account = Accounts::getByUserId($user_id, Accounts::TYPE_VK)) {
            throw new \InvalidArgumentException('Аккаунт не найден');
        }

        $data = json_decode($account->data);
        $access_token = $data->access_token;


        $vk = new \frontend\controllers\bot\libs\Vk([
            'access_token' => $access_token
        ]);

        try {
            $options = [
                'owner_id' => $peer_id,
                'post_id' => $media_id,
                'message' => $message
            ];
            if($data->user_id != $data->groups->id) {
                $options['from_group'] = $data->groups->id;
            }
            $result = $vk->api('wall.createComment', $options);

            $model = SocialDialoguesVkComments::newVkComment(
                $user_id,
                $peer_id,
                $media_id,
                $result['comment_id'],
                $message,
                null,
                $peer_id,
                null,
                SocialDialoguesVkComments::DIRECTION_OUTBOX
            );

        } catch (\frontend\controllers\bot\libs\VkException $e) {
            Logger::info($peer_id . ' - ' . $e->getMessage());
        }

    }

    public static function actionIgComment($user_id = '', $peer_id = '', $media_id = '', $message = '')
    {
        if($user_id == '') {
            $user_id = Yii::$app->request->post('user_id');
            $media_id = Yii::$app->request->post('media_id');
            $peer_id = Yii::$app->request->post('peer_id');
            $message = Yii::$app->request->post('message');
        }

        if(!$account = \common\models\Accounts::getByUserId($user_id, 'instagram')) {
            throw new \InvalidArgumentException('Аккаунт не найден');
        }
        $fields = $account->fields();
        $data = $fields['data']();

        try {
            $ig = new \InstagramAPI\Instagram(false, false);
            $ig->login($data->login, $data->password);
            $ig->media->comment($media_id, $message);

            SocialDialoguesInstagram::newIgComment(
                $user_id,
                $ig->account_id,
                $media_id,
                0,
                $message,
                $peer_id,
                SocialDialoguesInstagram::DIRECTION_OUTBOX
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}