<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 10:00
 */

namespace common\commands\command;
use common\models\FavoritesPosts;
use common\models\Webhooks;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use Carbon\Carbon;


class ClearPostsCommand extends BaseObject implements SelfHandlingCommand
{
    public $period;


    public function handle($command)
    {
        $period = $command->period;

        try {

            $timestamp = Carbon::now()
                ->subDay($period)
                ->timestamp;

            $condition = $this->condition($timestamp);

            return $this->getOldPosts($condition);
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getOldPosts($condition){
        return Webhooks::deleteAll($condition);
    }

    public function getCountOldPosts($condition){
        return Webhooks::find()->where($condition)->count();
    }

    public function condition($timestamp){
        return
            ['AND',
                ['not in', 'id', FavoritesPosts::find()->select('post_id')],
                'published_at <'. $timestamp
            ];
    }

}