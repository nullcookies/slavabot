<?php
namespace frontend\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class PasswordConfig extends Model
{
    public $password;
    public $new_password;
    public $new_password_repeat;
    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['password', 'required'],
            ['password', 'validatePassword'],

            ['new_password', 'required'],
            ['new_password_repeat', 'required'],

            ['new_password', 'string', 'min' => 6, 'max' => 21],
            ['new_password_repeat', 'string', 'min' => 6, 'max' => 21],
            ['new_password_repeat', 'compare', 'compareAttribute'=>'new_password'],

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
    public function save()
    {
        $model = $this->findModel(\Yii::$app->user->identity->id);

        $model->setPassword($this->new_password);

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

    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = $this->findModel(\Yii::$app->user->identity->id);
        }

        return $this->_user;
    }
}
