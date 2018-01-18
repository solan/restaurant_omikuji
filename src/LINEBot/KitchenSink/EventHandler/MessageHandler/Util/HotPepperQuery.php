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

class HotPepperQuery
{
    public static function list_restaurant($key,$longitude,$latitude,$genreID)
    {

    
    $curl = curl_init();
    if($genreID=="")
    {
        $url="https://webservice.recruit.co.jp/hotpepper/gourmet/v1/?key=$key&format=json&lng=$longitude&lat=$latitude&order=4";
    }
 else {
      $url="https://webservice.recruit.co.jp/hotpepper/gourmet/v1/?key=$key&format=json&lng=$longitude&lat=$latitude&order=4&genre=$genreID";  
 }
    

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);


    curl_close($curl);

    return json_decode($result, true) ;
    }
    public static function list_restaurant_by_name($key,$name)
    {

    
    $curl = curl_init();

    $url="https://webservice.recruit.co.jp/hotpepper/gourmet/v1/?key=$key&format=json&keyword=$name&order=4";

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);


    curl_close($curl);

    return json_decode($result, true) ;
    }
    public static function list_genre($key)
    {

    
    $curl = curl_init();

    $url="https://webservice.recruit.co.jp/hotpepper/genre/v1/?key=$key&format=json";

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);


    curl_close($curl);

    return json_decode($result, true) ;
    }


}

