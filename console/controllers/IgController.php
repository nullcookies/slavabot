<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 02.02.18
 * Time: 12:52
 */

namespace console\controllers;


use common\models\Accounts;
use common\models\SocialDialoguesInstagram;
use common\models\SocialDialoguesPeerInstagram;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\FrontendNotificationCommand;
use frontend\controllers\rest\send\V1Controller;
use yii\console\Controller;

class IgController extends Controller
{
    private $commentPks = [];

    public function actionComments()
    {
        //while (true) {



            $accounts = Accounts::getIg();

            /**
             * @var $account Accounts
             */
            foreach ($accounts as $account) {
                $fields = $account->fields();
                $data = $fields['data']();

                try {
                    $ig = new \InstagramAPI\Instagram(false, false);
                    //$ig->setProxy("http://51.15.205.156:3128");
                    $ig->login($data->login, $data->password);

                    echo "ACC: " . $ig->account_id . PHP_EOL;
                } catch (\Exception $e) {
                    echo "Не удалось подключить user ID: " . $account->user_id . PHP_EOL;
                    echo $e->getMessage() . PHP_EOL;
                    continue;
                }

                try {
                    echo "User ID: " . $account->user_id . PHP_EOL;

                    $this->commentPks = SocialDialoguesInstagram::getCommentIdsByUserId($account->user_id);

                    $this->getComments($ig, $account->user_id, $account->userValue->telegram_id);
                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }

                sleep(5);
            }

            sleep(5);
        //}
    }

    protected function getComments(\InstagramAPI\Instagram $ig, $userId, $telegramId)
    {
        //проверяется на глубину одной недели
        $minTimestamp = time() - 60 * 60 * 24 * 7;
        $maxId = null;
        $mediaIds = [];

        $feeds = $ig->timeline->getSelfUserFeed($maxId, $minTimestamp);
        while ($feeds->items) {
            foreach ($feeds->items as $key => $item) {
                //var_dump($item);
                echo date('d.m.Y', $item->taken_at) . ': ' . $item->id . PHP_EOL;
                $mediaIds[] = $item->id;
                $maxId = $item->id;
                $instagramOwnerId = $item->user->pk;
            }

            $feeds = $ig->timeline->getSelfUserFeed($maxId, $minTimestamp);
        }


        foreach ($mediaIds as $id) {
            $maxId = null;
            $comments = $ig->media->getComments($id, $maxId);
            //var_dump($comments);
            $break = false;
            while ($comments->comments) {
                $maxId = $comments->comments[0]->pk;
                foreach ($comments->comments as $key => $item) {
                    //если такой комментарий уже есть, то переходим к следующему посту
                    if (in_array($item->pk, $this->commentPks)) {
                        $break = true;
                        continue;
                    } elseif($instagramOwnerId != $item->user_id) {
                        $this->commentPks[] = $item->pk;
                        echo $item->text . PHP_EOL;

                        $user = $item->user;

                        SocialDialoguesInstagram::newIgComment(
                            $id,
                            $userId,
                            $ig->account_id,
                            $id,
                            $item->pk,
                            $item->text,
                            $user->pk
                        );

                        SocialDialoguesPeerInstagram::saveIgPeer(
                            $user->pk,
                            $user->username,
                            $user->profile_pic_url
                        );

                        $bot = new Bot();
                        $telegram = $bot->GetTelegram();

                        $command = new FrontendNotificationCommand($telegram);
                        $command->prepareParams([
                            'tid' => $telegramId,
                            'message' => $user->username.":\n".$item->text,
                        ]);
                        $command->execute($user->pk, $id);

                        echo 'sended' . PHP_EOL;
                    }
                }
                if(!$break) {
                    $comments = $ig->media->getComments($id, $maxId);
                } else {
                    break;
                }

            }
        }
    }

}