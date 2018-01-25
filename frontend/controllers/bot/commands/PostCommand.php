<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Carbon\Carbon;
use common\models\User;
use common\services\StaticConfig;
use frontend\controllers\bot\libs\TelegramWrap;
use frontend\controllers\bot\libs\Utils;
use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Longman\TelegramBot\Entities\Update;


class PostCommand extends UserCommand
{
    protected $name = 'post';                               // Your command's name
    protected $description = 'Post to the soccial network'; // Your command description
    protected $usage = '/post';                             // Usage of your command
    protected $version = '1.0.0';
    protected $need_mysql = true;
    public $conversation;
//    public $telegram;

    public function execute($new=false, $intro_text='')
    {

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
        $chat = $message->getChat();
        $user = $this->getMessage() ? $message->getFrom() : $this->getCallbackQuery()->getFrom();

        $text = trim($message->getText(true));

        $chat_id = $chat->getId();
        $user_id = $user->getId();


        $data = [
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ];



        try {

            /**
             * Инициируем новый/загружаем текущий разговор
             */

            $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

            file_put_contents(\Yii::getAlias('@frontend') . '/runtime/logs/cbb.log',
                json_encode($this->conversation) . "\n", FILE_APPEND);

            $notes = &$this->conversation->notes;
            !is_array($notes) && $notes = [];
            //cache data from the tracking session if any

            $state = 0;

            // Восстанавливаем этап разговора

            if (isset($notes['state'])) {
                $state = $notes['state'];
            }

            if($new){
                $data['text'] = $intro_text;
                Request::sendMessage($data);
            }

            switch ($state) {
                case 0:
                    /**
                     * Первый этап ввода сообщения.
                     * Проверяем на входящие данные, если они отсутсвуют, то ожидаем их.
                     * Если данные пришли, то сохраняем.
                     */

                    if ($new || $notes['stage'] != 'added' &&  ($text === '' || $notes['Text'] === '')  && !$message->getPhoto() && !$message->getVideo() && !$message->getAudio()) {

                        $notes['state'] = 0;
                        $notes['MsgId'] = $message->getMessageId();


                        $this->conversation->update();

//                        $data['text'] = 'Введите данные: ';
//
//
//                        Request::sendMessage($data);

                        break;
                    }else{

                        $notes['state'] = 1;
                        $notes['stage'] = 'post';

                        if($text != '' && ($notes['Text']==='' || !isset($notes['Text']))){
                            $notes['Text'] = $text;
                        }

                        if ($message->getPhoto() && ($notes['Photo']==='' || !isset($notes['Photo']))) {
                            $photos = $message->getPhoto();

                            $photo = end($photos);

                            $response2 = Request::getFile(['file_id' => $photo->getFileId()]);
                            if ($response2->isOk()) {
                                /** @var File $photo_file */
                                $photo_file = $response2->getResult();
                                Request::downloadFile($photo_file);
                                $notes['Photo'] = json_encode([$photo_file]);
                            }


                            //$text = '';
                        }
                        if ($message->getVideo() && ($notes['Video']==='' || !isset($notes['Video']))) {
                            $video = $message->getVideo();


                            $response2 = Request::getFile(['file_id' => $video->getFileId()]);
                            if ($response2->isOk()) {
                                /** @var File $photo_file */
                                $video_file = $response2->getResult();
                                Request::downloadFile($video_file);
                                $notes['Video'] = json_encode([$video_file]);
                            }


                            //$text = '';
                        }
                        if ($message->getAudio() && ($notes['Audio']==='' || !isset($notes['Audio']))) {
                            $audio = $message->getAudio();


                            $response2 = Request::getFile(['file_id' => $audio->getFileId()]);
                            if ($response2->isOk()) {
                                /** @var File $photo_file */
                                $audio_file = $response2->getResult();
                                Request::downloadFile($audio_file);
                                $notes['Audio'] = json_encode([$audio_file]);
                            }


                            //$text = '';
                        }


                        $this->conversation->update();

                    }


                case 1:
                    /**
                     * Второй этап: в случае поступления данных выводим доступные действия.
                     * Проверяем наличие текста/медиа и позволяем добавить недостающий контент
                     */

                    try{
                        Request::deleteMessage([
                            'chat_id' => $chat_id,
                            'message_id' => $notes['fm']['result']['message_id'],
                        ]);

                    } catch (TelegramException $e) {

                    }
                    $data['text'] = 'Выберите действие:';

                    // Проверяем на возможность добавления в пост текста/медиа

                    if(!isset($notes['Text']) || !isset($notes['Photo'])){
                        $buttonsArray = [
                            ['text' => 'Опубликовать', 'callback_data' => 'sendpost'],
                            ['text' => 'Запланировать', 'callback_data' => 'planpost'],
                            ['text' => 'Добавить', 'callback_data' => 'addpost'],
                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                        ];
                    }else{
                        $buttonsArray = [
                            ['text' => 'Опубликовать', 'callback_data' => 'sendpost'],
                            ['text' => 'Запланировать', 'callback_data' => 'planpost'],
                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                        ];
                    }


                    $inline_keyboard = new InlineKeyboard($buttonsArray);

                    $data['reply_markup'] = $inline_keyboard;

                    $notes['fm'] = Request::sendMessage($data);

                    $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id);

                    $this->conversation->update();
                    break;
                case 3:

                    /**
                     * Отложенная публикация. Запрашиваем у пользователя желаемое время публикации.
                     */

                    Request::deleteMessage([
                        'chat_id' => $chat_id,
                        'message_id' => $notes['fm']['result']['message_id'],
                    ]);

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                    ]);

                    $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id, true);
                    $notes['MsgId'] = $message->getMessageId();

                    //часовой пояс пользователя

                    /** @var User $user */
                    $user = User::findOne([
                        'telegram_id' => $user_id,
                    ]);

                    //по умолчанию ставим
                    $timeZone = 'Europe/Moscow';
                    if ($user) {
                        $timeZone = $user->timezone;
                    }

                    $data['text'] = "Введите время публикации.\nНапример: " . Carbon::now()->timezone($timeZone)->addHour()->format('d.m.Y H:i');

                    $data['reply_markup'] = $inline_keyboard;
                    $notes['state'] = 4;
                    $notes['fm'] = Request::sendMessage($data);
                    $this->conversation->update();

                    break;
                case 4:
                    /**
                     * Обработка введенного пользователем времени публикации.
                     * Если данные корректны, отправляем пост в очередь на публикацию.
                     */

                    $notes['MsgId'] = $message->getMessageId();


                    if (!self::IsDate($text)) {
                        $data['text'] = "Не верный формат: " . $text;
                    } else {

                        //часовой пояс пользователя

                        /** @var User $user */
                        $user = User::findOne([
                            'telegram_id' => $user_id,
                        ]);

                        //по умолчанию ставим
                        $timeZone = 'Europe/Moscow';
                        if ($user) {
                            $timeZone = $user->timezone;
                        }

                        $notes['state'] = 5;
                        $notes['schedule_dt'] = Carbon::parse($text,
                            $timeZone)->setTimezone('Europe/London')->toDateTimeString();
                        //Carbon::parse($text)->timezone($timeZone)->timezone('Europe/London')->toDateTimeString();

                        $this->conversation->update();
                        $inline_keyboard = new InlineKeyboard([
                            ['text' => 'Опубликовать', 'callback_data' => 'sendpost'],
                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                        ]);

                        Carbon::setLocale('ru');
                        $td = Carbon::now()->timezone($timeZone)->diff(\Carbon\Carbon::parse($text, $timeZone));

                        $dif = "";

                        if ($td->y > 0) {
                            $dif .= Utils::human_plural_form($td->y, ["год", "года", "лет"]) . " ";
                        }
                        if ($td->m > 0) {
                            $dif .= Utils::human_plural_form($td->m, ["месяц", "месяц", "месяцев"]) . " ";
                        }
                        if ($td->d > 0) {
                            $dif .= Utils::human_plural_form($td->d, ["день", "дня", "дней"]) . " ";
                        }
                        if ($td->h > 0) {
                            $dif .= Utils::human_plural_form($td->h, ["час", "часа", "часов"]) . " ";
                        }
                        if ($td->i > 0) {
                            $dif .= Utils::human_plural_form($td->i, ["минуту", "минуты", "минут"]) . " ";
                        }
                        if ($td->s > 0) {
                            $dif .= Utils::human_plural_form($td->s, ["секунду", "секунды", "секунд"]);
                        }

                        $data['text'] = "Ваш пост будет опубликован через " . $dif;

                        return (new SendpostCommand($this->telegram,
                            new Update(json_decode($this->update->toJson(), true))))->executeNow($data['text']);
                    }
            }
//            switch ($state) {
//                case 0:
//                    $notes['state'] = 1;
//                    if (!$message->getPhoto() && !$message->getVideo() && !$message->getAudio()) {
//                        $notes['state'] = 0;
//                        $notes['MsgId'] = $message->getMessageId();
//                        $this->conversation->update();
//                        $data['text'] = 'Приложите желаемое медиа:';
//                        $inline_keyboard = new InlineKeyboard([
//                            //['text' => 'Опубликовать', 'callback_data' => 'publicpost'],
//                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
//                        ]);
//                        $data['reply_markup'] = $inline_keyboard;
//
//                        $result = Request::sendMessage($data);
//                        break;
//                    }
//
//                    if ($message->getPhoto()) {
//                        $photos = $message->getPhoto();
//
//                        $photo = end($photos);
//
//                        $response2 = Request::getFile(['file_id' => $photo->getFileId()]);
//                        if ($response2->isOk()) {
//                            /** @var File $photo_file */
//                            $photo_file = $response2->getResult();
//                            Request::downloadFile($photo_file);
//                            $notes['Photo'] = json_encode([$photo_file]);
//                        }
//
//
//                        $text = '';
//                    }
//
//                    if ($message->getVideo()) {
//                        $video = $message->getVideo();
//
//
//                        $response2 = Request::getFile(['file_id' => $video->getFileId()]);
//                        if ($response2->isOk()) {
//                            /** @var File $photo_file */
//                            $video_file = $response2->getResult();
//                            Request::downloadFile($video_file);
//                            $notes['Video'] = json_encode([$video_file]);
//                        }
//
//
//                        $text = '';
//                    }
//                    if ($message->getAudio()) {
//                        $audio = $message->getAudio();
//
//
//                        $response2 = Request::getFile(['file_id' => $audio->getFileId()]);
//                        if ($response2->isOk()) {
//                            /** @var File $photo_file */
//                            $audio_file = $response2->getResult();
//                            Request::downloadFile($audio_file);
//                            $notes['Audio'] = json_encode([$audio_file]);
//                        }
//
//
//                        $text = '';
//                    }
//
//                case 1:
//
//                    if ($text === '' || $text === $telConfig->config['buttons']['post']['label']) {
//                        $notes['state'] = 1;
//                        $data['text'] = "Введите текст сообщения:";
//                        $inline_keyboard = new InlineKeyboard([
//                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
//                        ]);
//                        $data['reply_markup'] = $inline_keyboard;
//                        $notes['fm'] = Request::sendMessage($data);
//                        $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id);
//
//                        $this->conversation->update();
//                        break;
//                    }
//
//                    if (!isset($notes['Text']) || $notes['Text'] == "") {
//                        $notes['state'] = 2;
//
//                        $notes['Text'] = $text;
//
//                        $inline_keyboard = new InlineKeyboard(
//                            [
//                                ['text' => 'Опубликовать', 'callback_data' => 'publicpost']
//                            ],
//                            [
//                                ['text' => 'Отменить', 'callback_data' => 'cancelpost']
//                            ]
//
//                        );
//
//                        $data = [
//                            'chat_id' => $chat_id,
//                            'user_id' => $user_id,
//                            'reply_markup' => $inline_keyboard->setSelective(true),
//                            'text' => "Дальнейшее действие:",
//
//                        ];
//
//                        Request::deleteMessage([
//                            'chat_id' => $chat_id,
//                            'message_id' => $notes['fm']['result']['message_id'],
//                        ]);
//
//                        $notes['fm'] = Request::sendMessage($data);
//                        $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id);
//
//                        $notes['MsgId'] = $message->getMessageId();
//
//
//                        $this->conversation->update();
//                        /**************************************/
//                        break;
//                    }
//
//                case 2:
//                    Request::deleteMessage([
//                        'chat_id' => $chat_id,
//                        'message_id' => $notes['fm']['result']['message_id'],
//                    ]);
//
//                    $notes['MsgId'] = $message->getMessageId();
//
//                    $inline_keyboard = new InlineKeyboard([
//                        ['text' => 'Да', 'callback_data' => 'sendpost'],
//                        ['text' => 'Отложить', 'callback_data' => 'planpost'],
//                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
//                    ]);
//
//                    $data['text'] = "Опубликовать?";
//                    $data['reply_markup'] = $inline_keyboard;
//
//                    $notes['state'] = 3;
//
//
//                    $notes['fm'] = Request::sendMessage($data);
//
//                    $this->conversation->update();
//                    break;
//
//                case 3:
//                    Request::deleteMessage([
//                        'chat_id' => $chat_id,
//                        'message_id' => $notes['fm']['result']['message_id'],
//                    ]);
//
//                    $inline_keyboard = new InlineKeyboard([
//                        ['text' => 'Опубликовать сейчас', 'callback_data' => 'sendpost'],
//                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
//                    ]);
//
//                    $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id, true);
//                    $notes['MsgId'] = $message->getMessageId();
//
//                    //часовой пояс пользователя
//
//                    /** @var User $user */
//                    $user = User::findOne([
//                        'telegram_id' => $user_id,
//                    ]);
//
////                   //по умолчанию ставим
//                    $timeZone = 'Europe/Moscow';
//                    if ($user) {
//                        $timeZone = $user->timezone;
//                    }
//
//                    $data['text'] = "Введите время публикации.\nНапример: " . Carbon::now()->timezone($timeZone)->addHour()->format('d.m.Y H:i');
//
//                    $data['reply_markup'] = $inline_keyboard;
//                    $notes['state'] = 4;
//                    $notes['fm'] = Request::sendMessage($data);
//                    $this->conversation->update();
//
//                    break;
//
//                case 4:
//
//                    $inline_keyboard = new InlineKeyboard([
//                        ['text' => 'Опубликовать сейчас', 'callback_data' => 'sendpost'],
//                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
//                    ]);
//
//                    $notes['MsgId'] = $message->getMessageId();
//
//
//                    if (!self::IsDate($text)) {
//                        $data['text'] = "Не верный формат: " . $text;
//                    } else {
//
//                        //часовой пояс пользователя
//
//                        /** @var User $user */
//                        $user = User::findOne([
//                            'telegram_id' => $user_id,
//                        ]);
//
//                        //по умолчанию ставим
//                        $timeZone = 'Europe/Moscow';
//                        if ($user) {
//                            $timeZone = $user->timezone;
//                        }
//
//                        $notes['state'] = 5;
//                        $notes['schedule_dt'] = Carbon::parse($text, $timeZone)->setTimezone('Europe/London')->toDateTimeString();
//                        //Carbon::parse($text)->timezone($timeZone)->timezone('Europe/London')->toDateTimeString();
//
//                        $this->conversation->update();
//                        $inline_keyboard = new InlineKeyboard([
//                            ['text' => 'Опубликовать', 'callback_data' => 'sendpost'],
//                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
//                        ]);
//
//                        Carbon::setLocale('ru');
//                        $td = Carbon::now()->timezone($timeZone)->diff(\Carbon\Carbon::parse($text, $timeZone));
//
//                        $dif = "";
//
//                        if ($td->y > 0) {
//                            $dif .= Utils::human_plural_form($td->y, ["год", "года", "лет"]) . " ";
//                        }
//                        if ($td->m > 0) {
//                            $dif .= Utils::human_plural_form($td->m, ["месяц", "месяц", "месяцев"]) . " ";
//                        }
//                        if ($td->d > 0) {
//                            $dif .= Utils::human_plural_form($td->d, ["день", "дня", "дней"]) . " ";
//                        }
//                        if ($td->h > 0) {
//                            $dif .= Utils::human_plural_form($td->h, ["час", "часа", "часов"]) . " ";
//                        }
//                        if ($td->i > 0) {
//                            $dif .= Utils::human_plural_form($td->i, ["минуту", "минуты", "минут"]) . " ";
//                        }
//                        if ($td->s > 0) {
//                            $dif .= Utils::human_plural_form($td->s, ["секунду", "секунды", "секунд"]);
//                        }
//
//                        $data['text'] = "Ваш пост будет опубликован через " . $dif;
//
//                    }
//                    $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id, true);
//
//                    $data['reply_markup'] = $inline_keyboard;
//
//                    $notes['fm'] = Request::sendMessage($data);
//
//                    $this->conversation->update();
//                    break;
//            }
        } catch (TelegramException $e) {
            TelegramLog::error($e->getMessage());
        }

    }

    private function changeFM($notes, $inline_keyboard, $user_id, $chat_id, $remove_kb = false)
    {

        $notes=json_decode(json_encode($notes),true);

        $mid = $notes['fm']['result']['message_id'];
        $mtext = $notes['fm']['result']['text'];

        if (!$remove_kb) {
            $data_edit = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'message_id' => $mid,
                'text' => $mtext,
                'reply_markup' => $inline_keyboard
            ];
        } else {
            $data_edit = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'message_id' => $mid,
                'text' => $mtext,

            ];
        }

        // Try to edit selected message.
        $result = Request::editMessageText($data_edit);
        return $result->getResult();
    }


    private static function IsDate($_value, $_format = 'd.m.Y H:i')
    {

        $d = \DateTime::createFromFormat($_format, $_value);

        return $d/* && $d->format($_format) == $_value*/
            ;
    }
}