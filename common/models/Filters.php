<?php

namespace common\models;

use Yii;

/**
 * Модель для работы с клиентскими фильтрами по поиску потенциальных клиентов
 *
 * @property integer $id
 * @property integer $user_id - привязка к пользователю
 * @property string $name - пользовательское название фильтра
 * @property string $filter - содержимое фильтра в JSON
 */

class Filters extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'filters';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['user_id', 'location', 'theme'], 'integer'],
            [['name', 'search', 'email'], 'string']

        ];
    }

    /**
     * Возвращаем только имя, содержимое фильтра и почту для уведомлений
     * @return array
     */
    public function fields()
    {
        return [
            'name',
            'search',
            'location' => function(){
                return (string)$this->location;
            },
            'theme' => function(){
                return (string)$this->theme;
            },
            'email'
        ];
    }

    /**
     * Получить все фильтры пользователя
     *
     * @return object - сохраненные фильтры |
     *         bool - в случае отсутсвия сохраненных фильтров
     */

    public static function getFilters()
    {
        $model = static::find()->where(['user_id' => Yii::$app->user->id])->asArray()->all();

        if($model){
            return $model;
        }else{
            return false;
        }
    }

    /**
     * Получить все фильтры у которых включенны уведомления
     *
     * @return object - сохраненные фильтры |
     *         bool - в случае отсутсвия сохраненных фильтров
     */

    public static function checkFilters($item)
    {
        $model = static::find()
            ->where(
                ['AND',
                    ['OR',
                        ['location' => $item->location],
                        ['location' => null]
                    ],
                    ['OR',
                        ['theme' => $item->theme],
                        ['theme' => null]
                    ],
                ]
            )
            ->andWhere(['not', ['email' => null]])
            ->asArray()
            ->all();
        if($model){
            return $model;
        }else{
            return false;
        }
    }

    public static function checkSearch($elem, $filters)
    {
        if($filters){
            foreach($filters as $filter){
                if(strlen($filter['search'])>2){
                    if(strripos($elem['post_content'], $filter['search'])){
                        $arFilter[] = $filter;
                    }
                }else{
                    $arFilter[] = $filter;
                }
            }
            return $arFilter;
        }else{
            return false;
        }
    }


    public static function sendNotification($elem){
        $filters = Filters::checkSearch($elem, Filters::checkFilters($elem));
        $html = '
        <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#e4e4e4">
<tbody><tr>
	<td bgcolor="#e4e4e4" width="100%">

	<table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="table">
	<tbody><tr>
		<td width="600" class="cell">

	   	<table width="600" cellpadding="0" cellspacing="0" border="0" class="table">
		<tbody><tr>
			<td width="250" bgcolor="#e4e4e4" class="logocell"><img border="0" src="images/spacer.gif" width="1" height="20" class="hide"><br class="hide"><img src="http://salesbot.medialogic.ddemo.ru/cube/img/logo.png" width="54" height="54" alt="Campaign Monitor" style="-ms-interpolation-mode:bicubic;"><br><img border="0" src="images/spacer.gif" width="1" height="10" class="hide"><br class="hide"></td>
			<td align="right" width="350" class="hide" style="color:#a6a6a6;font-size:12px;font-family:\'Helvetica Neue\',Helvetica,Arial,sans-serif;text-shadow: 0 1px 0 #ffffff;" valign="top" bgcolor="#e4e4e4"><img border="0" src="images/spacer.gif" width="1" height="63"><br><span>WIDGET&nbsp;</span><strong><span style="text-transform:uppercase;"> <currentmonthname> <currentyear></currentyear></currentmonthname></span></strong> <span>NEWSLETTER&nbsp;</span></td>
		</tr>
		</tbody></table>

		<img border="0" src="images/spacer.gif" width="1" height="15" class="divider"><br>

		<repeater>
			<layout label="New feature">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tbody><tr>
				<td bgcolor="#85bdad" nowrap=""><img border="0" src="images/spacer.gif" width="5" height="1"></td>
				<td width="100%" bgcolor="#ffffff">

					<table width="100%" cellpadding="20" cellspacing="0" border="0">
					<tbody><tr>

						<td bgcolor="#ffffff" class="contentblock">
<img border="0" src="'.$elem->author_image_url.'" label="Hero image" editable="true" id="screenshot">
							<h4 class="secondary"><strong><singleline label="Title">'.$elem->author_name.'</singleline></strong></h4>
							<multiline label="Description">'.$elem->post_content.'</multiline>
                            <a href="#" style="    background-color: #8bc34a;
    border-color: #689f38;
    border: none;
    padding: 6px 12px;
    border-bottom: 2px solid;
    border-radius: 3px;
    background-clip: padding-box;
    margin-top: 13px;
    display: block;
    color: #fff;
    width: 130px;
    text-decoration: none;">Получить контакт</a>
						</td>
					</tr>
					</tbody></table>

				</td>
			</tr>
			</tbody></table>

		</repeater>

		</td>
	</tr>
	</tbody></table>

	<img border="0" src="images/spacer.gif" width="1" height="25" class="divider"><br>

	</td>
</tr>
</tbody></table>
        ';
        if($filters){
            foreach($filters as $filter){
                Yii::$app->mailer
                    ->compose('layouts/html', ['content' => $html])
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                    ->setTo($filter['email'])
                    ->setSubject('Новый контакт по фильтру: ' . $filter['name'])
                    ->send();
            }
        }

    }

    /**
     * Получить конкретный фильтр по его id
     *
     * @return object - найденный фильтр |
     *         bool - в случае отсутсвия фильтра
     */

    public static function getFilter($id)
    {
        $model = static::findOne(['id' => $id]);

        if($model){
            return $model;
        }else{
            return false;
        }
    }

    /**
     * Сохраняем новый фильтр
     */

    public function saveFilter($item)
    {
        $model = new Filters();

        $model->user_id = Yii::$app->user->id;
        $model->name = $item['name'];
        $model->search = $item['search'];
        $model->location = $item['location'];
        $model->theme = $item['theme'];

        $model->save();

    }

    /**
     * Обновляем существующий фильтр
     */

    public function updateFilter($item)
    {
        $model = Filters::findOne(['id' => $item['id']]);

        $model->name = $item['name'];
        $model->search = $item['search'];
        $model->location = $item['city'];
        $model->theme = $item['theme'];
        $model->email = $item['email'];

        $model->save();

    }
}
