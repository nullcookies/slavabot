<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 10.01.2018
 * Time: 15:52
 */

namespace common\commands\command;

use common\models\History;
use common\models\User;
use common\services\StaticConfig;
use frontend\controllers\bot\libs\Utils;
use Yii;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use Carbon\Carbon;
use yii\db\Expression;


class SendPostingNotificationCommand extends BaseObject implements SelfHandlingCommand
{
    public $day;

    public function handle($command)
    {
        $day = $command->day;

        Carbon::setLocale('ru');
        $start = Carbon::today()->addDay($day)->timestamp;


        $res = [];
        $users = History::find()
            ->from(['us' => History::tableName()])
            ->where(
                ['>', 'us.updated_at', $start]
            )
            ->andWhere(
                ['<', 'us.updated_at', $start+86400]
            )
            ->innerJoin( '(select max(sd.id) as `sd_max` FROM `history` `sd` GROUP BY `sd`.`user_id`) as `x`', '`us`.`id`=`x`.`sd_max`')
            ->leftJoin(User::tableName(), User::tableName().'.id=us.user_id')
            ->select("us.user_id, ".User::tableName().".telegram_id, max(`us`.`id`) as `max_id`")
            ->asArray()->all();

        //return $users->createCommand()->rawSql;


        foreach($users as $user){
            if($user['telegram_id']){
                $status = \Yii::$app->commandBus->handle(
                    new SendTelegramNotificationCommand(
                        [
                            'tid' => $user['telegram_id'],
                            'text' => StaticConfig::postsNotifications()
                        ]
                    )
                );
                $res[] = [$user['telegram_id'], $status];
            }
        }

        return $res;
    }
}