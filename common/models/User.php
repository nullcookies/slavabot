<?php
namespace common\models;

use common\commands\command\SendPostingNotificationCommand;
use Yii;
use common\models\Accounts;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use common\models\billing\Payment;
use Carbon\Carbon;
use frontend\controllers\bot\libs\Utils;
use common\commands\command\SendNotificationCommand;


/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $temp_password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $timezone
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'salesbot_user';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }
    public function getTariffValue()
    {
        return $this->hasOne(Payment::className(), ['user_id' => 'id'])->where(['active' => 1]);

    }


    public function getDataAccounts()
    {
        return $this->hasMany(Accounts::className(), [
            'user_id' => 'id',
        ])->select(['type'])->asArray(true);

    }

    public function fields(){
        return [
            'id',
            'username',
            'password_hash',
            'temp_password_hash',
            'email',
            'auth_key',
            'status',
            'timezone',
            'tariff' => 'tariffValue'
        ];
    }

    static function getUsers()
    {
        return self::find()->orderBy(['id' => SORT_DESC]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
 * Finds user by telegram_id
 *
 * @param string $tig
 * @return static|null
 */
    public static function findByTIG($tid)
    {
        return static::findOne(['telegram_id' => $tid, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findByID($id)
    {
        return User::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function setAuth($id)
    {
        $model = User::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
        $model->authorized = 1;
        $model->save(false);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function generatePassword($length = 32) {

        $randomString = Yii::$app->getSecurity()->generateRandomString($length);

        return $randomString;

    }

    /**
     *  Генерируем четырехзначный код интеграции
     */

    public function generetaCode()
    {
        return rand(1000, 9999);
    }


    /**
     * Validates code
     *
     * @param string $code code to validate
     * @return bool if code provided is valid for current user
     */

    public function validateCode($code)
    {
        if(!$code || !$this->temp_password_hash){
            return false;
        }
        return Yii::$app->security->validatePassword($code, $this->temp_password_hash);
    }

    /**
     * Задаем пользователю хэш сгенерированного кода интеграции
     * @param $code
     */

    public function setCode($code)
    {
        $this->temp_password_hash = Yii::$app->security->generatePasswordHash($code);
    }

    public function clearTempCode()
    {
        return $this->temp_password_hash = null;
    }

    /**
     * Отправляем код интеграции пользователю на почту
     *
     */

    public function SendTemporaryPassword($id)
    {

        $user = self::findByID($id);

        $code = $user->generetaCode();

        $user->setCode($code);

        $html = "
            <div>
                <div>
                    <p>Данные для интеграции с Telegram:</p>
                    <ul>
                        <li>Код подтверждения: <b>" . $code . "</b></li>
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
                <a href=\"mailto:support@slavabot.ru\">support@slavabot.ru</a>
            </div>
        ";

        $mail = \Yii::$app->mailer->compose()
            ->setFrom(['noreply@slavabot.ru' => 'SlavaBot'])
            ->setTo($user->email)
            ->setSubject('SlavaBot | Код подтверждения интеграции')
            ->setHtmlBody($html)
            ->send();

        return [
            'mail' => $mail,
            'user' =>$user->save()
        ];
    }

    /**
     * Очистка кода интеграции
     *
     * @param $id
     * @return bool
     */

    public function clearCode($id){
        $user = self::findByID($id);
        $user->clearTempCode();
        return $user->save();
    }

    static function getUser()
    {
        $user = self::findByID(\Yii::$app->user->identity->id);


        return array(
            'id' => $user->id,
            'name' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'telegram' => $user->telegram_id > 0 ? true : false,
            'tariff' => $user->tariffValue,
        );
    }

    static function expireToString()
    {
        $user = self::findByID(\Yii::$app->user->identity->id);

        Carbon::setLocale('ru');
        $td = Carbon::now()->diff(\Carbon\Carbon::parse($user->tariffValue->expire));

        $dif = "";

        if ($td->y > 0) {
            $dif .= Utils::human_plural_form($td->y, ["год", "года", "лет"]) . " ";
        }
        if ($td->m > 0) {
            $dif .= Utils::human_plural_form($td->m, ["месяц", "месяц", "месяцев"]) . " ";
        }
        if ($td->d > 0) {
            $dif .= Utils::human_plural_form($td->d, ["день", "дня", "дней"]);
        }
        if ($td->d == 0 && $td->h > 0) {
            $dif .= Utils::human_plural_form($td->h, ["час", "часа", "часов"]);
        }

        return $dif;
    }

    static function expired()
    {
        return true;
    }

    static function currentTariff()
    {
        return \Yii::$app->user->identity->tariffValue;
    }

    static function notification($day)
    {
        return \Yii::$app->commandBus->handle(
            new SendNotificationCommand(
                [
                    'day' => $day
                ]
            )
        );
    }

    static function postingNotification($day)
    {
        return \Yii::$app->commandBus->handle(
            new SendPostingNotificationCommand(
                [
                    'day' => $day
                ]
            )
        );
    }

    public static function getCustomerPhone(){
        return \Yii::$app->user->identity->phone;
    }

    public static function getCustomerEmail(){
        return \Yii::$app->user->identity->email;
    }

    public static function getCustomerId(){
        return \Yii::$app->user->identity->id;
    }
}
