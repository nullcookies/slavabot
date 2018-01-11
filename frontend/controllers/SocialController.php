<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\models\Accounts;
use common\models\Instagram;
use common\services\social\FacebookService;
use common\services\social\VkService;

use common\commands\command\AddToAccountsCommand;


class SocialController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['help', 'contact'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['instagram', 'finish-process', 'update-process', 'vk-auth'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'vk-auth' => ['post'],
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => [
                    'instagram',
                    'accounts',
                    'unprocessed',
                    'finish-process',
                    'remove',
                    'update-process',
                    'vk-auth',
                    'check-instagram'
                    ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Генерируем по шаблону ссылку для авторизации FB
     *
     * @param $callback
     * @param string $text
     * @param $id
     * @param string $template
     * @return mixed
     */
    function getFBBtn($callback, $text='', $id, $template='<a href="LINK" id="ID">TEXT</a>'){

        $fb = new FacebookService;

        return self::useTemplate(
            $template,
            [
                '/LINK/',
                '/ID/',
                '/TEXT/'
            ],
            [
                htmlspecialchars($fb->link($callback)),
                $id,
                $text
            ]
        );
    }

    /**
     * Шаблонизация для конопки FB
     *
     * @param $string
     * @param $patterns
     * @param $replacements
     * @return mixed
     */

    function useTemplate($string, $patterns, $replacements){
        return preg_replace($patterns, $replacements, $string);
    }

    /**
     * Обработка и сохранение данных возвращаемых FB
     * Страница социальные сети. После обработки данных нас редиректит на нее же и открывается окно выбора группы FB.
     */

    public function actionFb()
    {
        $fb = new FacebookService;

        if(Accounts::saveReference($fb->process(), 0)){
            Yii::$app->response->redirect('/#/pages/social');
        }
    }

    /**
     * Обработка и сохранение данных возвращаемых FB.
     * Мастер настройки, отличие в том, что тут авторизация FB откроется в новой вкладке и после успешной обработки вкладка закроется.
     */

    public function actionWizardFb()
    {
        $this->layout = '@app/views/layouts/simple.php';

        $fb = new FacebookService;

        if(Accounts::saveReference($fb->process(), 0)){
            return '<script>window.close();</script>';
        }
    }

    /**
     * Возвращаем страницу с аккаунтами пользователя
     *
     * @return string
     */
    public function actionIndex()
    {

        $this->layout = '@app/views/layouts/simple.php';


        return $this->render('social', [
            'accounts'=> Accounts::getAccounts()
        ]);

    }

    /**
     * Авторизаця ВК
     * @return array
     */
    function actionVkAuth()
    {
        $login = Yii::$app->request->post('login');
        $password = Yii::$app->request->post('password');

        $auth = new VkService;
        $res = $auth->init($login, $password);

        if($res['status']){
            $save = Accounts::saveReference($res,  0);

            if($save){
                return [
                    'status' => true,
                ];
            }else{
                return [
                    'status' => false,
                    'error' => 'Get token error'
                ];
            }
        }else{
            return $res;
        }
    }

    /**
     * Удаление аккаунта
     *
     * @return false|int
     */
    public function actionRemove()
    {
        $id = \Yii::$app->request->post('id');
        return Accounts::remove($id);
    }


    /**
     * Сохраняем аккаунт instagram
     *
     * @return array|bool
     */
    public function actionInstagram(){

        $model = new Instagram();

        $model->load(Yii::$app->request->post());

        $res = Yii::$app->commandBus->handle(
            new AddToAccountsCommand(
                [
                    'processed' => 1,
                    'data' => $model
                ]
            )
        );

        return $res;
    }

    /**
     * Устанавливаем активную группу для аккаунта vk/fb
     *
     * @return bool
     */
    public function actionFinishProcess(){
        return Accounts::processAccount(\Yii::$app->request->post());
    }

    /**
     * Обновление аккаунта
     *
     * @return bool
     */
    public function actionUpdateProcess(){
        return Accounts::updateAccount(\Yii::$app->request->post());
    }

    /**
     * Получить аккаунты текущего пользователя
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionAccounts(){
        return Accounts::getAccounts();
    }

    /**
     * Получить аккаунты с невыбранной группой
     *
     * @param string $type
     * @return array|null|\yii\db\ActiveRecord
     */

    public function actionUnprocessed($type = ''){
        if(isset(\Yii::$app->request->post()['type'])){
            $type = \Yii::$app->request->post()['type'];
        }
        return Accounts::getUnprocessedAccounts($type);
    }


}