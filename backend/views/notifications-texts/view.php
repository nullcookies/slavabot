<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\NotificationsTexts */

$this->title = $model->text;
$this->params['breadcrumbs'][] = ['label' => 'Уведомления', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notifications-texts-view">

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
                'attribute' => 'id',
                'label' => 'Идентификатор уведомления'
            ],
            [
                'attribute' => 'dataAccounts',
                'label' => 'Социальные сети',
                'format' => 'raw',

                'value' => function ($model) {

                    $types = [
                        0 => 'Нет данных. Уведомление не активно!',
                        1 => 'Мотивационные'
                    ];

                    return \yii\helpers\Html::tag(
                        'span',
                        $types[$model->type]
                    );

                },

            ],
            [
                'attribute' => 'text',
                'label' => 'Текст уведомления'
            ],
        ],
    ]) ?>

</div>
