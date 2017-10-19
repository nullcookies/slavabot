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


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
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
            'phone' => $user->phone
        );
    }

    public function save()
    {
        $model = $this->findModel(\Yii::$app->user->identity->id);

        $model->username = $this->username;
        $model->phone = $this->phone;


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
