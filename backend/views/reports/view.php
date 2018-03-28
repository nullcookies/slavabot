<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Reports */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Выгрузки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reports-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Удалить?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'mlg_id',
                'label' => 'Идентификатор отчета'
            ],
            [
                'attribute' => 'title',
                'label' => 'Название выгрузки'
            ],
            [
                'attribute' => 'active',
                'label' => 'Активность',
                'format' => 'raw',

                'value' => function ($model) {

                    $types = [
                        0 => 'Выгрузка отключена',
                        1 => 'Выгрузка активна'
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
        ],
    ]) ?>

</div>
