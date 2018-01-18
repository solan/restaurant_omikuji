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

namespace LINE\LINEBot\KitchenSink\EventHandler\MessageHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\KitchenSink\EventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\HotPepperQuery;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\MongoDbHelper;

class LocationMessageHandler implements EventHandler {

    /** @var LINEBot $bot */
    private $bot;

    /** @var \Monolog\Logger $logger */
    private $logger;

    /** @var LocationMessage $event */
    private $locationMessage;
    private $hotpepper;
    private $mongoDBURI;

    /**
     * LocationMessageHandler constructor.
     * @param LINEBot $bot
     * @param \Monolog\Logger $logger
     * @param LocationMessage $locationMessage
     */
    public function __construct($bot, $logger, LocationMessage $locationMessage, $hotpepper, $mongoDBURI) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->locationMessage = $locationMessage;
        $this->hotpepper = $hotpepper;
        $this->mongoDBURI = $mongoDBURI;
    }

    public function handle() {
        $replyToken = $this->locationMessage->getReplyToken();
        $title = $this->locationMessage->getTitle();
        $address = $this->locationMessage->getAddress();
        $latitude = $this->locationMessage->getLatitude();
        $longitude = $this->locationMessage->getLongitude();

        $this->logger->info("Got text message from $replyToken: $this->hotpepper");

        //$this->bot->replyText($replyToken, '調べる中0x10007A');

        $uid = $this->locationMessage->getUserId();

        $result = MongoDbHelper::getGenre($this->mongoDBURI, $uid);
        if($result==null)
        {
            $genreID = "";
        }
        else
        {
            $result = $result->getArrayCopy();
            if (count($result) > 0) {
                $genreID = $result['genreID'];
            } 
        }
       

        $restaurantList = HotPepperQuery::list_restaurant($this->hotpepper, $longitude, $latitude,$genreID);


        $option = array();

        shuffle($restaurantList['results']['shop']);

        $count = 1;

        foreach ($restaurantList['results']['shop'] as $restaurant) {

            $image_url = $restaurant["photo"]["pc"]["l"];

            $option[] = new CarouselColumnTemplateBuilder($restaurant["name"], $restaurant["address"], $image_url, [
                new UriTemplateActionBuilder('詳細', $restaurant["urls"]["pc"]),
                new UriTemplateActionBuilder('クーポン', $restaurant["coupon_urls"]["pc"]),
            ]);
            if ($count > 5) {
                break;
            }
            $count++;
        }

        if (count($option) == 0) {
            $this->bot->replyText($replyToken, 'ご希望のお店が見つかりませんでした。条件を変更して検索しますか？');
        } else {
            $carouselTemplateBuilder = new CarouselTemplateBuilder($option);
            $templateMessage = new TemplateMessageBuilder('この近くのレストラン', $carouselTemplateBuilder);
            $this->bot->replyMessage($replyToken, $templateMessage);
        }
    }

}
