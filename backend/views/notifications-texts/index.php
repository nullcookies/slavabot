<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Уведомления';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notifications-texts-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить уведомление', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [

                'attribute' => 'type',
                'label' => 'Тип уведомления',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $types = [
                        0 => 'Нет данных',
                        1 => 'Мотивационные'
                    ];

                    return \yii\helpers\Html::tag(
                        'span',
                        $types[$model->type],
                        [
                            'class' => ($model->type > 0 ? '' : 'label label-danger'),
                        ]
                    );
                },
            ],
            [
                'attribute' => 'text',
                'label' => 'Текст уведомления'
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
