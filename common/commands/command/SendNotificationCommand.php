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
            ->select(Payment::tableName().".user_id, ".User::tableName().".email, ".User::tableName().".telegram_id")
            ->asArray()->all();

        $text = "Ваша подписка на сервис заканчивается через ". Utils::human_plural_form($day, ["день", "дня", "дней"]);

        foreach($payments as $user){

            \Yii::$app->commandBus->handle(
                new SendTelegramNotificationCommand(
                    [
                        'tid' => $user['telegram_id'],
                        'text' => $text
                    ]
                )
            );

            \Yii::$app->commandBus->handle(
                new SendEmailNotificationCommand(
                    [
                        'email' => $user['email'],
                        'subject' => 'Информирование об истечении оплаты',
                        'text' => $text
                    ]
                )
            );
        }
    }
}