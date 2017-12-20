<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Carbon\Carbon;
use common\models\User;
use frontend\controllers\bot\libs\TelegramWrap;
use frontend\controllers\bot\libs\Utils;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;

class PostCommand extends UserCommand
{
    protected $name = 'post';                      // Your command's name
    protected $description = 'Post to the soccial network'; // Your command description
    protected $usage = '/post';                    // Usage of your command
    protected $version = '1.0.0';
    protected $need_mysql = true;
    protected $conversation;

    public function execute()
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

            $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

            file_put_contents(\Yii::getAlias('@frontend') . '/runtime/logs/cbb.log',
                json_encode($this->conversation) . "\n", FILE_APPEND);

            $notes = &$this->conversation->notes;
            !is_array($notes) && $notes = [];
            //cache data from the tracking session if any
            $state = 0;
            if (isset($notes['state'])) {
                $state = $notes['state'];
            }


            switch ($state) {
                case 0:
                    $notes['state'] = 1;
                    if (!$message->getPhoto() && !$message->getVideo() && !$message->getAudio()) {
                        $notes['state'] = 0;
                        $notes['MsgId'] = $message->getMessageId();
                        $this->conversation->update();
                        $data['text'] = 'Приложите желаемое медиа:';
                        $inline_keyboard = new InlineKeyboard([
                            //['text' => 'Опубликовать', 'callback_data' => 'publicpost'],
                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                        ]);
                        $data['reply_markup'] = $inline_keyboard;

                        $result = Request::sendMessage($data);
                        break;
                    }

                    if ($message->getPhoto()) {
                        $photos = $message->getPhoto();

                        $photo = end($photos);

                        $response2 = Request::getFile(['file_id' => $photo->getFileId()]);
                        if ($response2->isOk()) {
                            /** @var File $photo_file */
                            $photo_file = $response2->getResult();
                            Request::downloadFile($photo_file);
                            $notes['Photo'] = json_encode([$photo_file]);
                        }


                        $text = '';
                    }

                    if ($message->getVideo()) {
                        $video = $message->getVideo();


                        $response2 = Request::getFile(['file_id' => $video->getFileId()]);
                        if ($response2->isOk()) {
                            /** @var File $photo_file */
                            $video_file = $response2->getResult();
                            Request::downloadFile($video_file);
                            $notes['Video'] = json_encode([$video_file]);
                        }


                        $text = '';
                    }
                    if ($message->getAudio()) {
                        $audio = $message->getAudio();


                        $response2 = Request::getFile(['file_id' => $audio->getFileId()]);
                        if ($response2->isOk()) {
                            /** @var File $photo_file */
                            $audio_file = $response2->getResult();
                            Request::downloadFile($audio_file);
                            $notes['Audio'] = json_encode([$audio_file]);
                        }


                        $text = '';
                    }

                case 1:

                    if ($text === '' || $text === $telConfig->config['buttons']['post']['label']) {
                        $notes['state'] = 1;
                        $data['text'] = "Введите текст сообщения:";
                        $inline_keyboard = new InlineKeyboard([
                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                        ]);
                        $data['reply_markup'] = $inline_keyboard;
                        $notes['fm'] = Request::sendMessage($data);
                        $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id);

                        $this->conversation->update();
                        break;
                    }

                    if (!isset($notes['Text']) || $notes['Text'] == "") {
                        $notes['state'] = 2;

                        $notes['Text'] = $text;

                        $inline_keyboard = new InlineKeyboard(
                            [
                                ['text' => 'Опубликовать', 'callback_data' => 'publicpost']
                            ],
                            [
                                ['text' => 'Отменить', 'callback_data' => 'cancelpost']
                            ]

                        );

                        $data = [
                            'chat_id' => $chat_id,
                            'user_id' => $user_id,
                            'reply_markup' => $inline_keyboard->setSelective(true),
                            'text' => "Дальнейшее действие:",

                        ];

                        Request::deleteMessage([
                            'chat_id' => $chat_id,
                            'message_id' => $notes['fm']['result']['message_id'],
                        ]);

                        $notes['fm'] = Request::sendMessage($data);
                        $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id);

                        $notes['MsgId'] = $message->getMessageId();


                        $this->conversation->update();
                        /**************************************/
                        break;
                    }

                case 2:
                    Request::deleteMessage([
                        'chat_id' => $chat_id,
                        'message_id' => $notes['fm']['result']['message_id'],
                    ]);

                    $notes['MsgId'] = $message->getMessageId();

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => 'Да', 'callback_data' => 'sendpost'],
                        ['text' => 'Отложить', 'callback_data' => 'planpost'],
                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                    ]);

                    $data['text'] = "Опубликовать?";
                    $data['reply_markup'] = $inline_keyboard;

                    $notes['state'] = 3;


                    $notes['fm'] = Request::sendMessage($data);

                    $this->conversation->update();
                    break;

                case 3:
                    Request::deleteMessage([
                        'chat_id' => $chat_id,
                        'message_id' => $notes['fm']['result']['message_id'],
                    ]);

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => 'Опубликовать сейчас', 'callback_data' => 'sendpost'],
                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                    ]);

                    $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id, true);
                    $notes['MsgId'] = $message->getMessageId();

                    //часовой пояс пользователя

                    /** @var User $user */
                    $user = User::findOne([
                        'telegram_id' => $user_id,
                    ]);

//                   //по умолчанию ставим
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

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => 'Опубликовать сейчас', 'callback_data' => 'sendpost'],
                        ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                    ]);

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
                        $notes['schedule_dt'] = Carbon::parse($text)->timezone($timeZone)->timezone('Europe/London')->toDateTimeString();

                        $this->conversation->update();
                        $inline_keyboard = new InlineKeyboard([
                            ['text' => 'Опубликовать', 'callback_data' => 'sendpost'],
                            ['text' => 'Отменить', 'callback_data' => 'cancelpost'],
                        ]);

                        Carbon::setLocale('ru');
                        $td = Carbon::now()->timezone($timeZone)->diff(\Carbon\Carbon::parse($text)->timezone($timeZone));

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

                    }
                    $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id, true);

                    $data['reply_markup'] = $inline_keyboard;

                    $notes['fm'] = Request::sendMessage($data);

                    $this->conversation->update();
                    break;
            }


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