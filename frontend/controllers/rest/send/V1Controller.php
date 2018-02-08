<?php
namespace frontend\controllers\rest\send;

use common\models\rest\Accounts;
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

    public static function actionVk($user_id = '', $peer_id = '', $message = '')
    {
        if($user_id == ''){
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

            var_dump($result);
        } catch (\frontend\controllers\bot\libs\VkException $e) {
            echo $e->getMessage();
        }

    }

    public static function actionIg($user_id = '', $peer_id = '', $message = '')
    {
        if($user_id == ''){
            $user_id = Yii::$app->request->post('user_id');
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
            //$ig->setProxy("http://51.15.205.156:3128");
            $ig->login($data->login, $data->password);
            $ig->media->comment($peer_id, $message);

        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}