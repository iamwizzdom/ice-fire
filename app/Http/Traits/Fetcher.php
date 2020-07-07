<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 7/7/2020
 * Time: 9:56 AM
 */

namespace App\Http\Traits;


trait Fetcher
{
    protected function fetchBooks(string $url, string $method = "GET"): array {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, ['Content-Type' => 'application/json']);

        curl_setopt($ch,CURLOPT_TIMEOUT, 60);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, $method);

        $content = curl_exec($ch);

        if ($content === false) {
            $response = [
                'status' => false,
                'response' => curl_error($ch)
            ];
            curl_close($ch);
            return $response;
        }

        // Initiate Retry
        $retry = 0;

        // Try again if it fails/times out
        while(curl_errno($ch) == CURLE_OPERATION_TIMEDOUT && $retry < 5) {
            $content = curl_exec($ch);
            $retry++;
            sleep(2);
        }

        if (curl_errno($ch)) {
            $response = [
                'status' => false,
                'response' => curl_error($ch)
            ];
            curl_close($ch);
            return $response;
        }

        curl_close($ch);

        return [
            'status' => true,
            'response' => json_decode($content, true) ?: $content
        ];
    }
}
