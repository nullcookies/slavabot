<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 09:16
 *
 * Команда для получения постов из sm.mlg.ru
 *
 */

namespace common\commands\command;
use Carbon\Carbon;
use common\models\Filters;
use common\models\User;
use frontend\controllers\bot\commands\PostNotificationCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use frontend\controllers\bot\Bot;


class FilterNotificationCommand extends BaseObject implements SelfHandlingCommand
{

    /**
     * Объект поста
     * @var
     */
    public $item;
    public $telegram;
    protected function GetCommand()
    {
        $bot = new Bot();
        $this->telegram = $bot->GetTelegram();
        //return $telegram->getCommandObject('postNotification');
    }

    public function handle($command)
    {
        $item = $command->item;
        $filters = Filters::checkFilters($item);
        if($filters){
            foreach($filters as $filter){

//                try{
                    $time = Carbon::createFromTimestamp($item->published_at, User::getTZ($filter['user_id']))->format('H:i');

//                }catch (\Exception $e) {
//                    $time=  $e->getMessage();
//                }

                //try{
                    $str = "Новый пост по фильтру \"".$filter['name']."\":\n\n" . $item->author_name . ', ' . $item->dataBlog->aBlogHost . ', ' . $time . "\n\n" . strip_tags($item['post_content']);

                    $str.= "\n\n";

                if($item->dataCountry->aid!=0 && $item->dataRegion!=0 && $item->dataCity!=0){
                    $res = '';

                    if($item->dataCountry->aid!=0){

                        if($item->dataCountry->aType!=''){
                            $res .= $item->dataCountry->aType.' ';
                        }

                        $res.= $item->dataCountry->aName.', ';
                    }

                    if($item->dataRegion->aid!=0 && $item->dataRegion->aName!=$item->dataCity->aName){

                        $res.= $item->dataRegion->aName.' ';
                        if($item->dataRegion->aType!=''){
                            $res .= $item->dataRegion->aType.', ';
                        }
                    }

                    if($item->dataCity->aid!=0){

                        if($item->dataCity->aType!=''){
                            $res .= $item->dataCity->aType.' ';
                        }

                        $res.= $item->dataCity->aName;
                    }else{
                        $res = substr($res, 0, -2);
                    }

                    $str.= $res;
                }


//                }catch (\Exception $e) {
//                    $str =  $e->getMessage();
//                }
                try {
                    $this->GetCommand();

                    $command = new PostNotificationCommand($this->telegram);

                    $command->prepareParams([
                        'tid' => User::getTID($filter['user_id']),
                        'message' => $str,
                    ]);

                    $result = $command->execute($item['id'], $item['post_url']);

                    //return $result;
                }
                catch (\Exception $e) {
                    return $e->getMessage();
                }
            }


        }




    }

}