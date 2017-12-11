<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use BW\Vkontakte;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class TestCommand extends UserCommand
{
    protected $name = 'test';                      // Your command's name
    protected $description = 'A command for test'; // Your command description
    protected $usage = '/test';                    // Usage of your command
    protected $version = '1.0.0';


    public function execute()
    {


        $request_params = array(
            'access_token'=>'8fcb85b9309ab335a9a4a2b142aeba64dbb29504be688050e09d893d38f10c95e2c123810c44bf2cb14f3',
            'owner_id' => 452356544,
            'message'=>'Test message from telegram',


        );
        $get_params = http_build_query($request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/wall.post?'. $get_params));


        file_put_contents(__DIR__ . '/../logs/vk.log',json_encode( $result));
        die;
        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text' => $result, // Set message to send
        ];

        return Request::sendMessage($data);        // Send message!

    }
}