<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Тарифы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariffs-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать новый тариф', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'title',
            'description',
            'cost',
            //'constraints',
            [

                'attribute' => 'displayed',
                'label' => 'Тип тарифа',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $types = [
                        0 => 'Корпоративный',
                        1 => 'Общедоступный'
                    ];

                    return \yii\helpers\Html::tag(
                        'span',
                        $types[$model->displayed],
                        [
                            'class' => 'label label-'.($model->displayed > 0 ? 'success' : 'danger'),
                        ]
                    );
                },
            ],
            [

                'attribute' => 'active',
                'label' => 'Активность',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $types = [
                        0 => 'Тариф отключен',
                        1 => 'Тариф активен'
                    ];

                    return \yii\helpers\Html::tag(
                        'span',
                        $types[$model->active],
                        [
                            'class' => 'label label-'.($model->active > 0 ? 'success' : 'danger'),
                        ]
                    );
                },
            ],
            [

                'attribute' => 'color',
                'label' => 'Фон',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
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
                        $types[$model->color]['name'],
                        [
                            'class' => 'label',
                            'style' => 'background:'.$types[$model->color]['bg'].'; width: 100%; display: block;'
                        ]
                    );

                    return \yii\helpers\Html::tag(
                        'span',
                        $types[$model->active],
                        [
                            'class' => 'label label-'.($model->active > 0 ? 'success' : 'danger'),
                        ]
                    );
                },
            ],
            // 'displayed',
            // 'color',
            // 'sort',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
