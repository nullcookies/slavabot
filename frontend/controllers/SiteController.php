<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\UserConfig;
use frontend\models\ContactForm;
use frontend\models\PasswordConfig;
use common\models\User;
use common\models\Location;
use common\models\Category;
use common\models\Priority;
use common\models\Theme;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'repassword'],
                'rules' => [
                    [
                        'actions' => ['signup', 'repassword'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['get'],
                    //'getdata' => ['post']
                ],
            ],
        ];
    }
    public function beforeAction($action)
    {
        if ($action->id == 'getdata') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        $file = 'webhooks.txt';

        $current = file_get_contents($file);

        $current = count(explode("\n", $current))-1;

        return $this->render('index', [
            'location' => Location::find()->all(),
            'category' => Category::find()->all(),
            'priority' => Priority::find()->all(),
            'theme' => Theme::find()->all(),
            'webhooks' => $current
        ]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return Yii::$app->response->redirect(['site/index']);
        }

        $this->layout = 'login';

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return Yii::$app->response->redirect(['site/index']);
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return Yii::$app->response->redirect(['site/login']);
    }

    public function actionGetdata()
    {

        $file = 'webhooks.txt';

        $current = file_get_contents($file);
        if(\Yii::$app->request->isPost){


            $current .= file_get_contents("php://input")."\n";

            file_put_contents($file, $current);

        }else{
            $current = explode("\n", $current);

            foreach ($current as $item) {
                $item = json_decode($item);


                $location = Location::saveReference($item);
                $category = Category::saveReference($item);
                $priority = Priority::saveReference($item);
                $theme = Theme::saveReference($item);

                $res = array(
                    'loc' => $location,
                    'cat' => $category,
                    'pri' => $priority,
                    'theme' => $theme
                );

                //var_dump($res);
            }
            var_dump(count($current));
        }
    }
    public function actionConfig()
    {

        $usermodel = new UserConfig();
        $passwordmodel = new PasswordConfig();

        $active = 'main';

        if(isset(\Yii::$app->request->post('UserConfig')['username'])){
            if ($usermodel->load(\Yii::$app->request->post()) && $usermodel->validate()) {
                if ($usermodel->save()) {
                    return \Yii::$app->response->redirect(['site/index']);
                } else {
                    return \Yii::$app->response->redirect(['site/index']);
                }
            }
        }elseif(isset(\Yii::$app->request->post('PasswordConfig')['password'])){

            $active = 'password';

            if ($passwordmodel->load(\Yii::$app->request->post()) && $passwordmodel->validate()) {
                if ($passwordmodel->save()) {
                    return \Yii::$app->response->redirect(['site/index']);
                } else {
                    return \Yii::$app->response->redirect(['site/index']);
                }
            }
        }

        return $this->render('config', [
            'modelUser' => $usermodel,
            'modelPassword' => $passwordmodel,
            'active' => $active
        ]);

    }

    public function actionNotifications()
    {
        return $this->render('notifications', []);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSignup()
    {
        $this->layout = 'login';
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                return Yii::$app->response->redirect(['site/login']);
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionConfigUser()
    {

        $model = new UserConfig();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->update()) {
                return Yii::$app->response->redirect(['site/login']);
            } else {
                return Yii::$app->response->redirect(['site/login']);
            }
        }

        return $this->render('config', [
            'model' => $model,
        ]);
    }

    public function actionRequestPasswordReset()
    {
        $this->layout = 'login';
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                return Yii::$app->response->redirect(['site/login']);
            } else {
                return Yii::$app->response->redirect(['site/login']);
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        $this->layout = 'login';
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return Yii::$app->response->redirect(['site/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
