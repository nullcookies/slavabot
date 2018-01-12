<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 10:00
 */

namespace common\commands\command;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;


class SendEmailNotificationCommand extends BaseObject implements SelfHandlingCommand
{
    public $email;
    public $text;
    public $subject;


    public function handle($command)
    {
        $email = $command->email;
        $text = $command->text;
        $subject = $command->subject;

        try {
            $html = "
                <div>
                    <div>
                        <p>" . $text . "</p>
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
                ->setTo($email)
                ->setSubject('SlavaBot | '.$subject)
                ->setHtmlBody($html)
                ->send();
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}