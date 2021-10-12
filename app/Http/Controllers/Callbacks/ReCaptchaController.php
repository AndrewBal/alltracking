<?php

namespace App\Http\Controllers\Callbacks;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Models\Seo\UrlAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class ReCaptchaController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function load(Request $request)
    {
        $_secret_key = config('services.google.reCaptcha_secret');
        $_ratio = config('services.google.reCaptcha_ratio');
        $_response = NULL;
        if (is_null($_secret_key)) {
            return [
                'error'   => 0,
                'success' => 1,
                'action'  => NULL,
                'score'   => NULL
            ];
        }
        if ($request->get('token') && $request->get('action')) {
            $_url = 'https://www.google.com/recaptcha/api/siteverify';
            $_response = [
                'error'   => 0,
                'success' => 0,
                'action'  => NULL,
                'score'   => NULL
            ];
            $_params_request = [
                'secret'   => $_secret_key,
                'response' => $request->get('token'),
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];
            $_ch = curl_init($_url);
            curl_setopt($_ch, CURLOPT_POST, 1);
            curl_setopt($_ch, CURLOPT_POSTFIELDS, $_params_request);
            curl_setopt($_ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($_ch, CURLOPT_HEADER, 0);
            curl_setopt($_ch, CURLOPT_RETURNTRANSFER, 1);
            $_response_curl = curl_exec($_ch);
            if (!empty($_response_curl)) $_decoded_response_curl = json_decode($_response_curl);
            if ($_decoded_response_curl && $_decoded_response_curl->success && $_decoded_response_curl->action == $request->get('action') && $_decoded_response_curl->score > $_ratio) {
                $_response['success'] = 1;
                $_response['action'] = $_decoded_response_curl->action;
                $_response['score'] = $_decoded_response_curl->score;
            } else {
                $_response['action'] = $_decoded_response_curl->action;
                $_response['score'] = $_decoded_response_curl->score;
                $_response['error'] = 1;
            }
            $_response = ['token' => data_encrypt((object)$_response)];
        }

        return response($_response, 200);
    }
}
