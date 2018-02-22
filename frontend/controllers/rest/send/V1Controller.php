<?php
namespace frontend\controllers\rest\send;

use common\models\rest\Accounts;
use common\models\SocialDialoguesInstagram;
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
            if($data->access_token != $data->group_access_token) {
                $options['from_group'] = $data->group_id;
            }
            $vk->api('wall.createComment', $options);

        } catch (\frontend\controllers\bot\libs\VkException $e) {
            echo $e->getMessage() . PHP_EOL;
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