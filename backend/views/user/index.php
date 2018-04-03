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
            [
                'attribute' => 'tariffValue',
                'label' => 'Тариф',
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {

                    if($model->tariffValue->tariffValue===null){
                        return \yii\helpers\Html::tag(
                            'span',
                            'Нет данных',
                            [
                                'class' => 'label',
                                'style' => 'background:#d9534f; width: 100%; display: block;'
                            ]
                        );
                    }
                    $types = [
                        '' => [
                            'name'=>'По умолчанию',
                            'bg' => '#03a9f4'
                        ],
                        'green-bg' =>
                            [
                                'name'=>'Зеленый',
                                'bg' => '#8bc34a'
                            ],
                        'yellow-bg' =>
                            [
                                'name'=>'Желтый',
                                'bg' => '#ffc107'
                            ],
                        'purple-bg' =>
                            [
                                'name'=>'Пурпурный',
                                'bg' => '#9c27b0'
                            ],
                    ];

                    return \yii\helpers\Html::tag(
                        'span',
                        $model->tariffValue->tariffValue->title,
                        [
                            'class' => 'label',
                            'style' => 'background:'.$types[$model->tariffValue->tariffValue->color]['bg'].'; width: 100%; display: block;'
                        ]
                    );

                },
            ],
            [
                'attribute' => 'tariffValue',
                'label' => 'Остаток',
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {

                    if($model->tariffValue->tariffValue===null){
                        return \yii\helpers\Html::tag(
                            'span',
                            'Нет данных',
                            [
                                'class' => 'label',
                                'style' => 'background:#d9534f; width: 100%; display: block;'
                            ]
                        );
                    }else{
                        //$color = '#d9534f';
                        \Carbon\Carbon::setLocale('ru');
                        $td = \Carbon\Carbon::now()->diff(\Carbon\Carbon::parse($model->tariffValue->expire));

                        $dif = "";

                        if ($td->y > 0) {
                            $dif .= \frontend\controllers\bot\libs\Utils::human_plural_form($td->y, ["год", "года", "лет"]) . " ";
                        }
                        if ($td->m > 0) {
                            $dif .= \frontend\controllers\bot\libs\Utils::human_plural_form($td->m, ["месяц", "месяц", "месяцев"]) . " ";
                        }
                        if ($td->d > 0) {
                            $dif .= \frontend\controllers\bot\libs\Utils::human_plural_form($td->d, ["день", "дня", "дней"]);
                        }
                        if ($td->d == 0 && $td->h > 0) {
                            $dif .= \frontend\controllers\bot\libs\Utils::human_plural_form($td->h, ["час", "часа", "часов"]);
                        }

                        //return $dif;
                        // #d9534f - red
                        return \yii\helpers\Html::tag(
                            'span',
                            $dif,
                            [
                                //'class' => 'label',
                                //'style' => 'background:'.$color.'; width: 100%; display: block;'
                            ]
                        );
                    }
                },
            ],
            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>
</div>
