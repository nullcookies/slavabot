<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 10.01.2018
 * Time: 15:52
 */

namespace common\commands\command;

use common\models\billing\Payment;
use common\models\User;
use frontend\controllers\bot\libs\Utils;
use Yii;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use Carbon\Carbon;


class SendNotificationCommand extends BaseObject implements SelfHandlingCommand
{
    public $day;

    public function handle($command)
    {
        $day = $command->day;

        Carbon::setLocale('ru');

        $payments = Payment::find()
            ->where(
                ['>', 'expire', Carbon::today()->addDay($day-1)->toDateString()]
            )
            ->andWhere(
                ['<', 'expire', Carbon::today()->addDay($day+1)->toDateString()]
            )
            ->leftJoin(User::tableName(), User::tableName().'.id='.Payment::tableName().'.user_id')
            ->select(Payment::tableName().".user_id, ".User::tableName().".email")
            ->asArray()->all();

        foreach($payments as $user){


            $html = "
                <div>
                    <div>
                        <p>Ваша подписка на сервис заканчивается через ". Utils::human_plural_form($day, ["день", "дня", "дней"]) .".</p>
                    </div>
                    <div>
                    <br>
                </div>
                <div>
                    С уважением,<br>
                    Команда СлаваБот<br>
                </div>
                <div>
                    <span class=\"wmi-callto\">+7 (495) 108-08-19</span>
                </div>
                <div>
                    <a href=\"mailto:support@slavabot.ru\">support@slavabot.ru</a>
                </div>
            ";

            $mail = \Yii::$app->mailer->compose()
                ->setFrom(['noreply@slavabot.ru' => 'SlavaBot'])
                ->setTo($user['email'])
                ->setSubject('SlavaBot | Информирование об истечении оплаты')
                ->setHtmlBody($html)
                ->send();
        }
    }
}