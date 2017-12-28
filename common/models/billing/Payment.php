<?php
namespace common\models\billing;

use yii\db\ActiveRecord;
use common\models\billing\Tariffs;
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

                return $dif;
            },
            'title' => function(){
                return $this->tariffValue->title;
            },
        ];
    }


    public function rules()
    {
        return [
            [['id', 'user_id', 'tariff_id'], 'integer']
        ];
    }

}