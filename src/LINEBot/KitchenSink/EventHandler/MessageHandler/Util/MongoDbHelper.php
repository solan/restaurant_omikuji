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

namespace LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util;

class MongoDbHelper {

    public static function updateGenre($uri, $uid, $genreID, $genreName) {
        $client = new \MongoDB\Client($uri);
        $genres = $client->heroku_qdx8sxd8->genres;
        $genres->updateOne(
                array('uid' =>$uid), array('$set' => array("uid" => $uid, "genreName" => $genreName, "genreID" => $genreID)), array("upsert" => true)
        );
    }

    public static function getGenre($uri, $uid) {
        $client = new \MongoDB\Client($uri);
        $query = array('uid' => $uid);
        $genres = $client->heroku_qdx8sxd8->genres;
        $result = $genres->findOne($query);
        return $result;
    }
    public static function deleteGenre($uri, $uid) {
        $client = new \MongoDB\Client($uri);
        $query = array('uid' => $uid);
        $genres = $client->heroku_qdx8sxd8->genres;
        $result = $genres->deleteOne($query);
        return $result;
    }

}
