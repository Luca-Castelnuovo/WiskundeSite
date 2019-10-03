<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CaptchaHelper {

    /**
     * Validates the captcha response
     *
     * @param $captcha_response
     *
     * @return bool
     *
     * @throws GuzzleException
     */
   public static function validate($captcha_response) {

      if (config('app.debug')) {
            return true;
      }

       $guzzle_client = new Client();

       $response = $guzzle_client->request('POST', config('captcha.endpoint'), [
           'form_params' => [
               'secret' => config('captcha.private_key'),
               'response' => $captcha_response
           ]
       ]);

       $response = json_decode($response->getBody());

       return $response->success;
   }
}