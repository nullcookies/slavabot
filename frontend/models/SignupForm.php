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
        $html = "
            <div>
                <div>
                    <p>Данные для входа:</p>
                    <ul>
                        <li>Адрес: <a href=\"http://app.slavabot.ru/\"  target=\"_blank\">app.slavabot.ru</a>
                        </li>
                        <li>Email: <b>".$user->email."</b></li>
                        <li>Пароль: <b>".$password."</b></li>
                    </ul>
                </div>
                <div>
                <br>
            </div>
            <div>
                С уважением,<br>
                Команда СлаваБот<br>
            </div>
            <div>
                <span class=\"wmi-callto\">+7 (495) 108-08-19</span>
            </div>
            <div>
                <a href=\"mailto:support@salesbot.ru\">support@salesbot.ru</a>
            </div>
        ";

        \Yii::$app->mailer->compose()
            ->setFrom(['noreply@slavabot.ru' => 'SlavaBot'])
            ->setTo($this->email)
            ->setSubject('SlavaBot | Регистрация')
            ->setHtmlBody($html)
            ->send();

        return $user->save() ? $user : null;
    }
}
