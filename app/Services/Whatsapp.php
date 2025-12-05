<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use stdClass;

class Whatsapp
{
    protected $api_key;

    public function __construct()
    {
        $this->api_key = config('services.aisensy.api_key');
    }

    public function send($number, $campaign_name, $template_params)
    {
        $api_url = 'https://backend.aisensy.com/campaign/t1/api/v2';
        $api_key = $this->api_key;

        $data = [
            "apiKey" => $api_key,
            "campaignName" => $campaign_name,
            "destination" => $number,
            "userName" => "Agrolink Manufacturing Private Limited",
            "templateParams" => $template_params,
            "source" => "new-landing-page form",
            "media" => new stdClass(),
            "buttons" => [],
            "carouselCards" => [],
            "location" => new stdClass(),
            "attributes" => new stdClass(),
            "paramsFallbackValue" => [],
        ];

        return Http::post($api_url, $data);
    }
}
