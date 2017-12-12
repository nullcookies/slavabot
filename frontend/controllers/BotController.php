<?php

namespace frontend\controllers;

use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use frontend\controllers\bot\BotSet;
use frontend\controllers\bot\BotUnset;
use frontend\controllers\bot\BotHook;

/**
 * Bot main controller
 *
 * Соотношение папок из бота:
 *
 * /config           => /common/config/bot
 * /commands         => /frontend/controllers/bot/commands
 * /tlg_rbot/app     => /frontend/controllers/bot/
 *
 */

class BotController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'index'
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'set',
                            'unset'
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                    'set' => ['get'],
                    'unset' => ['get'],

                    'hook' => ['post'],
                    //'getdata' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => ['index'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
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


    public function actionIndex()
    {
        return 'Yep!';
    }

    public function actionSet()
    {
        $class = new BotSet();
        return $class->Run();
    }

    public function actionUnset()
    {
        $class = new BotUnset();
        return $class->Run();
    }

    public function actionHook()
    {
        $class = new BotHook();
        return $class->Run();
    }

}