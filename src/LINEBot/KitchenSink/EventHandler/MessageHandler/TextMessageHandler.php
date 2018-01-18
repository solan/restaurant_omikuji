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
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\KitchenSink\EventHandler;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\HotPepperQuery;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\MongoDbHelper;

class TextMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;
    /** @var \Monolog\Logger $logger */
    private $logger;
    /** @var \Slim\Http\Request $logger */
    private $req;
    /** @var TextMessage $textMessage */
    private $textMessage;

    private $hotpepper;
    
    private $mongoDBURI;

    /**
     * TextMessageHandler constructor.
     * @param $bot
     * @param $logger
     * @param \Slim\Http\Request $req
     * @param TextMessage $textMessage
     */
    public function __construct($bot, $logger, \Slim\Http\Request $req, TextMessage $textMessage,$hotpepper,$mongoDBURI)
    {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->req = $req;
        $this->textMessage = $textMessage;
        $this->hotpepper=$hotpepper;
        $this->mongoDBURI=$mongoDBURI;
    }

    public function handle()
    {
        $text = $this->textMessage->getText();
        $text = strtolower(trim($text));
        $replyToken = $this->textMessage->getReplyToken();
        $this->logger->info("Got text message from $replyToken: $text");

        switch ($text) {
                case 'ジャンルの設定':
                $imageUrl = 'https://image.ibb.co/jYtObm/images.jpg';
                $carouselTemplateBuilder = new CarouselTemplateBuilder([
                    new CarouselColumnTemplateBuilder('  ', '  ', $imageUrl, [
                        new PostbackTemplateActionBuilder('居酒屋', 'action=set&genreID=G001&genreName=居酒屋'),
                        new PostbackTemplateActionBuilder('ダイニングバー', 'action=set&genreID=G002&genreName=ダイニングバー'),
                        new PostbackTemplateActionBuilder('創作料理', 'action=set&genreID=G003&genreName=創作料理'),
                    ]),
                    new CarouselColumnTemplateBuilder('  ', '  ', $imageUrl, [
                        new PostbackTemplateActionBuilder('和食', 'action=set&genreID=G004&genreName=和食'),
                        new PostbackTemplateActionBuilder('洋食', 'action=set&genreID=G005&genreName=洋食'),
                        new PostbackTemplateActionBuilder('イタリアン・フレンチ', 'action=set&genreID=G006&genreName=イタリアン・フレンチ'),

                    ]),
                    new CarouselColumnTemplateBuilder('  ', '  ', $imageUrl, [
                        new PostbackTemplateActionBuilder('中華', 'action=set&genreID=G007&genreName=中華'),
                        new PostbackTemplateActionBuilder('焼肉・韓国料理', 'action=set&genreID=G008&genreName=焼肉・韓国料理'),
                        new PostbackTemplateActionBuilder('アジアン', 'action=set&genreID=G009&genreName=アジアン'),
                    ]),
                    new CarouselColumnTemplateBuilder('  ', '  ', $imageUrl, [
                       new PostbackTemplateActionBuilder('各国料理', 'action=set&genreID=G010&genreName=各国料理'),
                       new PostbackTemplateActionBuilder('カラオケ・パーティ', 'action=set&genreID=G011&genreName=カラオケ・パーティ'),
                       new PostbackTemplateActionBuilder('バー・カクテル', 'action=set&genreID=G012&genreName=バー・カクテル'),
                    ]),
                    new CarouselColumnTemplateBuilder('  ', '  ', $imageUrl, [
                        new PostbackTemplateActionBuilder('ラーメン', 'action=set&genreID=G013&genreName=ラーメン'),
                        new PostbackTemplateActionBuilder('お好み焼き・もんじゃ・鉄板焼き', 'action=set&genreID=G016&genreName=お好み焼き・もんじゃ・鉄板焼き'),
                        new PostbackTemplateActionBuilder('カフェ・スイーツ', 'action=set&genreID=G014&genreName=カフェ・スイーツ'),
                    ])
                ]);
                $templateMessage = new TemplateMessageBuilder('ジャンルの設定', $carouselTemplateBuilder);
                $this->bot->replyMessage($replyToken, $templateMessage);
               
                break;
            case '現在設定のジャンル':
                $uid = $this->textMessage->getUserId();

                $result = MongoDbHelper::getGenre($this->mongoDBURI, $uid);
                if($result==null)
                {
                    $this->bot->replyText(
                            $replyToken, "ジャンルの設定はありません");
                }
                else
                {
                    $result=$result->getArrayCopy();
                    if (count($result) > 0) {
                    $genreName=$result['genreName'];
                    $this->bot->replyText(
                            $replyToken, $genreName . "を設定しております"
                    );
                    } 
                }
                break;
            case 'ジャンル設定の解消':
                $uid = $this->textMessage->getUserId();

                $result = MongoDbHelper::deleteGenre($this->mongoDBURI, $uid);
                $this->bot->replyText(
                            $replyToken, "ジャンル設定の解消しました");
                break;
            default:
                $restaurantList = HotPepperQuery::list_restaurant_by_name($this->hotpepper,$text);
                $option = array();
                foreach ($restaurantList['results']['shop'] as $restaurant) {
                $image_url=$restaurant["photo"]["pc"]["l"];
                $option[] = new CarouselColumnTemplateBuilder($restaurant["name"], $restaurant["address"], $image_url, [
                        new UriTemplateActionBuilder('詳細', $restaurant["urls"]["pc"]),
                        new UriTemplateActionBuilder('クーポン', $restaurant["coupon_urls"]["pc"]),
                    ]);
                }
            

         $carouselTemplateBuilder = new CarouselTemplateBuilder(
                  $option
                );
                $templateMessage = new TemplateMessageBuilder('この近くのレストラン', $carouselTemplateBuilder);
         $this->bot->replyMessage($replyToken, $templateMessage);
                break;
        }
    }
}
