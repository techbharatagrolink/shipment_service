<?php

namespace App\Services;

use http\Env\Response;
use Illuminate\Support\Facades\Http;

class DelhiveryService
{

    protected $token;

    public function __construct(){
        $this->token = config('services.delhivery.api_key');
    }

    public function serviceability($data){
        $token = $this->token;
        return Http::withToken($token)->get('https://track.delhivery.com/api/dc/expected_tat',$data);
    }

}
