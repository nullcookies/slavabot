<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            [
                'attribute' => 'username',
                'label' => 'Имя пользователя'
            ],
             'email:email',
            [
                'attribute' => 'telegram_id',
                'label' => 'Статус Telegram',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return \yii\helpers\Html::tag(
                        'span',
                        $model->telegram_id > 0 ? 'Подключен' : 'Не подключен',
                        [
                            'class' => 'label label-' . ($model->telegram_id > 0 ? 'success' : 'danger'),
                        ]
                    );
                },

            ],
            [
                'attribute' => 'dataAccounts',
                'label' => 'Социальные сети',
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {

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

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>
</div>
