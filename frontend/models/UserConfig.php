<?php
namespace frontend\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class UserConfig extends Model
{
    public $username;
    public $phone;
    public $timezone;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['timezone', 'string'],
            ['phone', 'required'],
            ['phone', 'match', 'pattern' => '/^\+7\s\([0-9]{3}\)\s[0-9]{3}\-[0-9]{2}\-[0-9]{2}$/'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function getUserData()
    {
        $user = self::findModel(\Yii::$app->user->identity->id);

        return array(
            'id' => $user->id,
            'name' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'telegram' => $user->telegram_id > 0 ? true : false
        );
    }

    public function save()
    {
        $model = $this->findModel(\Yii::$app->user->identity->id);

        $model->username = $this->username;
        $model->phone = $this->phone;
        $model->timezone = $this->timezone;


        return $model->save();
    }

    public function saveTelegramID($user_id, $tig)
    {
        $model = self::findModel($user_id);

        $model->telegram_id = $tig;
        return $model->save();
    }

    public function saveTimezone($user_id, $timezone)
    {
        $model = self::findModel($user_id);
        $model->timezone = $timezone;

        return $model->save();
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
