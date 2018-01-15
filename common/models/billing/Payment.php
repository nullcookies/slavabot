<?php
namespace common\models\billing;

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

}