<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>


<div>
    <p>Перейдите по ссылке для ввода нового пароля:</p>
    <ul>
        <li style="margin-left:15px;" data-processed="cq">
            <div class="password-reset">
                <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
            </div>
        </li>
    </ul>
    <div>Если вы не запрашивали сброс пароля, то проигнорируйте это письмо.</div>
</div>
<div><br></div>
<div>С уважением,<br>Команда СлаваБот<br></div>
<div><span class="wmi-callto">+7 (495) 108-08-19</span></div>
<div><a href="mailto:support@salesbot.ru" target="_blank">support@salesbot.ru</a></div>