<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 18.04.2018
 * Time: 13:00
 *
 * Команда для удаления устаревших постов.
 *
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

    /**
     * Вызываем обработчик
     *
     * @param $command
     * @return int|string
     */

    public function handle($command)
    {
        $period = $command->period;

        try {

            $timestamp = Carbon::now()
                ->subDay($period)
                ->timestamp;

            $condition = $this->condition($timestamp);

            return $this->deleteOldPosts($condition);
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Удаляем посты подходящие по условию
     *
     * @param $condition (array) - условие фильтрации
     * @return int
     */

    public function deleteOldPosts($condition){
        return Webhooks::deleteAll($condition);
    }

    /**
     * Получаем количество постов подходящих по условию
     *
     * Функция для логирования/отладки
     *
     * @param $condition (array) - условие фильтрации
     * @return int
     */

    public function getCountOldPosts($condition){
        return Webhooks::find()->where($condition)->count();
    }

    /**
     * Возвращаем сформированный массив условий для поиска.
     * Выбираем все посты, которые не были добавлены в избранное и были созданны раньше, чем $timestamp
     *
     * @param $timestamp
     * @return array
     */

    public function condition($timestamp){
        return
            ['AND',
                ['not in', 'id', FavoritesPosts::find()->select('post_id')],
                'published_at <'. $timestamp
            ];
    }

}