<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'id',
                'label' => 'Идентификатор пользователя'
            ],
            [
                'attribute' => 'telegram_id',
                'label' => 'Статус Telegram',
                'format' => 'raw',
                'value' => \yii\helpers\Html::tag(
                    'span',
                    $model->telegram_id > 0 ? 'Подключен' : 'Не подключен',
                    [
                        'class' => 'label label-' . ($model->telegram_id > 0 ? 'success' : 'danger'),
                    ]
                )

            ],
            [
                'attribute' => 'username',
                'label' => 'Имя пользователя'
            ],
            'email:email',
            [
                'attribute' => 'phone',
                'label' => 'Телефон'
            ],
            [
                'attribute' => 'timezone',
                'label' => 'Часовой пояс'
            ],
            [
                'attribute' => 'dataAccounts',
                'label' => 'Социальные сети',
                'format' => 'raw',

                'value' => function ($model) {

                    $socials = [
                        "instagram",
                        "vkontakte",
                        "facebook"
                    ];

                    $str = "";

                    $accounts = \yii\helpers\ArrayHelper::getColumn(
                        $model->dataAccounts,
                        'type'
                    );

                    foreach($socials as $social){
                        $str .= \yii\helpers\Html::tag(
                            'span',
                            $social ,
                            [
                                'class' => 'label label-' . (in_array($social, $accounts) ? 'success' : 'danger'),
                                'style'=> 'margin-right:10px;'
                            ]
                        );
                    }
                    return $str;

                },

            ],
            //'telegram_id',
            //'temp_password_hash',
            //'authorized',
        ],
    ]) ?>

</div>
