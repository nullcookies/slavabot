<?php
namespace common\models\billing;

use common\models\User;
use yii\db\ActiveRecord;
use common\models\billing\Tariffs;
use common\services\StaticConfig;
use Carbon\Carbon;
use frontend\controllers\bot\libs\Utils;

class Payment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'slava_payment';
    }

    /**
     * @inheritdoc
     */

    public function getTariffValue()
    {
        return $this->hasOne(Tariffs::className(), ['id' => 'tariff_id'])->where(['active' => 1]);
    }

    public function fields(){
        return [
            'order_id'=>'id',
            'id' => function(){
                return $this->tariffValue->id;
            },
            'expire' => function(){

                Carbon::setLocale('ru');
                $td = Carbon::now()->diff(\Carbon\Carbon::parse($this->expire));

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
            },
            'expire_date' => function(){
                return $this->expire;
            },
            'active' => function(){
                $td = (Carbon::parse($this->expire)->getTimestamp() - Carbon::now()->getTimestamp()) > 0;
                return $td;
            },
            'payment_id' => function(){
                return $this->id;
            },
            'title' => function(){
                return $this->tariffValue->title;
            }
        ];
    }


    public function rules()
    {
        return [
            [['id', 'user_id', 'tariff_id'], 'integer']
        ];
    }

    /**
     * Устанавливаем пользователю тариф по умолчанию, при регистрации.
     *
     * @param $user
     */
    static function initDefaultTariff($user)
    {
        $tariff = StaticConfig::defaulTariff();

        $elem = new Payment();

        $elem->user_id = $user;
        $elem->tariff_id = $tariff['id'];

        $elem->begin = Carbon::now()->format('Y-m-d H:i');
        $elem->expire = Carbon::now()->addDay($tariff['period'])->format('Y-m-d H:i');
        $elem->active = 1;

        $elem->save();
    }

    /**
     * Создание заказа
     *
     * @param $user
     * @param $tariff
     * @param $count
     * @return bool|Payment
     */
    static function newOrder($user, $tariff, $count)
    {
        $discount = 0;

        if($count >= 6 && $count < 12){
            $discount = 10;
        }elseif($count >= 12){
            $discount = 25;
        }


        $elem = new Payment();
        $tariffInfo = Tariffs::getTariffByID($tariff);

        $elem->user_id = $user;
        $elem->tariff_id = $tariffInfo->id;

        $elem->begin = Carbon::now()->format('Y-m-d H:i');
        $elem->expire = Carbon::now()->addDay($count * 30)->format('Y-m-d H:i');
        $elem->active = 0;

        $elem->totalPrice = (float)number_format((($tariffInfo->cost * $count * (1 - $discount / 100)) - User::getTariffBalance()), 2, '.', ''); //$tariffInfo->cost * $count;

        //return $elem->totalPrice;

        if($elem->save()){
            return $elem;
        }else{
            return false;
        }
    }

    /**
     * Активация заказа после оплаты
     */
    public function setActivePayment()
    {
        $this->active = 1;
        $this->save();
    }

    public static function getOrderByID($id){
        return self::find()
            ->andWhere(['id'=>$id])
            ->one();
    }

    public function getTotalPrice(){

        return number_format($this->totalPrice, 2, '.', '');
    }

    public function getId(){
        return $this->id;
    }
}