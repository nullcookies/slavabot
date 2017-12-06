<?php
namespace common\commands\command;

use Yii;
use common\models\Accounts;
use common\models\Instagram;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\BaseObject;


class AddToAccountsCommand extends BaseObject implements SelfHandlingCommand
{
    /**
     * @var mixed
     */
    public $data;

    /**
     * @param AddToAccountsCommand $command
     * @return bool
     */
    public function handle($command)
    {
        $account = $command->data;

        if(isset($account['id'])){
            $model = Accounts::findOne(['id' => $account['id']]);
        }else{
            $model = new Accounts();
        }

        if($account['type']=='instagram'){
            $acc = Instagram::login($account['data']['login'], $account['data']['password']);

            if(!$acc){
                return [
                    'error' => '<strong>Ошибка аутентификации!</strong> Проверьте правильность логина/пароля или подтвердите вход <a target="_blank" href="https://www.instagram.com">https://www.instagram.com</a>.'
                ];
            }
        }

        $model->user_id = Yii::$app->user->id;
        $model->type = $account['type'];
        $model->data = json_encode($account['data']);
        $model->processed = 1;
        $model->status = 1;

        return $model->save();

    }
}