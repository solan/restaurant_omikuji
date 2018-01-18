<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace LINE\LINEBot\KitchenSink\EventHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\KitchenSink\EventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\MongoDbHelper;

class PostbackEventHandler implements EventHandler {

    /** @var LINEBot $bot */
    private $bot;

    /** @var \Monolog\Logger $logger */
    private $logger;

    /** @var PostbackEvent $postbackEvent */
    private $postbackEvent;
    
    
    private $uri;

    /**
     * PostbackEventHandler constructor.
     * @param LINEBot $bot
     * @param \Monolog\Logger $logger
     * @param PostbackEvent $postbackEvent
     */
    public function __construct($bot, $logger, PostbackEvent $postbackEvent, $mongoDBURI) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->postbackEvent = $postbackEvent;
        $this->uri = $mongoDBURI;
    }

    public function handle() {
        $uid = $this->postbackEvent->getUserId();
        $replyToken = $this->postbackEvent->getReplyToken();
        $paramaters = $this->postbackEvent->getPostbackData();
        
       
        parse_str($paramaters, $output);
        $action = $output['action'];

        switch ($action) {
            case 'set':
                $genreID = $output['genreID'];
                $genreName = $output['genreName'];
                MongoDbHelper::updateGenre($this->uri, $uid, $genreID,$genreName);

                $this->bot->replyText(
                        $replyToken, $genreName . "を絞りました"
                );
                break;
            case 'get':
                $result = MongoDbHelper::getGenre($this->uri, $uid);
                if (count($result) > 0) {
                    $genreName=$result[0]['genreName'];
                    $this->bot->replyText(
                            $replyToken, $genreName . "を設定しております"
                    );
                } else {
                    $this->bot->replyText(
                            $replyToken, "設定しておりません");
                }
                break;
            default:
                $this->bot->replyText($replyToken, "失敗しました。");
                break;
        }
    }

}
