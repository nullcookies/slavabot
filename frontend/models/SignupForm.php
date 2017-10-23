<?php
namespace frontend\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $full_name;
    public $email;
    public $phone;
    public $password;
    public $usr_password_repeat;
    public $terms_cond = true;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

//            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['phone', 'required'],
            ['phone', 'match', 'pattern' => '/^\+7\s\([0-9]{3}\)\s[0-9]{3}\-[0-9]{2}\-[0-9]{2}$/'],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],
            ['terms_cond', 'boolean'],
            ['terms_cond', 'required', 'requiredValue' => true],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();

        $password = $user->generatePassword(6);

        $user->username = $this->username;
        $user->email = $this->email;
        $user->phone = $this->phone;

        $user->setPassword($password);
        $user->generateAuthKey();

        \Yii::$app->mailer->compose()
            ->setFrom('admin@salesbot.ru')
            ->setTo($this->email)
            ->setSubject('Регистрация')
            ->setHtmlBody('<b>Логин:</b> '.$user->email.'<br><b>Пароль:</b> '.$password.'')
            ->send();

        return $user->save() ? $user : null;
    }
}
