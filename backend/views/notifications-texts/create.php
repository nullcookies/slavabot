<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\NotificationsTexts */

$this->title = 'Создать уведомление';
$this->params['breadcrumbs'][] = ['label' => 'Уведомления', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notifications-texts-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
