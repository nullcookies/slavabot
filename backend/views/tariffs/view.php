<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\billing\Tariffs */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Тарифы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariffs-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Удалить тариф?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'description',
            'cost',
            //'constraints',
            [
                'attribute' => 'active',
                'label' => 'Активность',
                'format' => 'raw',

                'value' => function ($model) {

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
                'attribute' => 'displayed',
                'format' => 'raw',

                'value' => function ($model) {

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
                'attribute' => 'constraints',
                'format' => 'raw',

                'value' => function ($model) {
                    $str = '';

                    $types = [
                      'filters' => 'Количество фильтров'
                    ];

                    foreach($model->constraints as $constraint => $val){
                        $str.= $types[$constraint].' : '.$val. '<br>';
                    }

                    return $str;


                },

            ],
            [
                'attribute' => 'color',
                'label' => 'Фон',
                'format' => 'raw',

                'value' => function ($model) {

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
                            'style' => 'background:'.$types[$model->color]['bg']
                        ]
                    );

                },

            ],
            'sort',
        ]
    ]) ?>

</div>
