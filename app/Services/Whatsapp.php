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

        if ($template_params[0]=='file'){
            $params_file = array(
                $template_params[1],
                $template_params[2],
            );
        }
        $data = [
            "apiKey" => $api_key,
            "campaignName" => $campaign_name,
            "destination" => $number,
            "userName" => "Agrolink Manufacturing Private Limited",
            "templateParams" => $params_file ?? $template_params,
            "source" => "new-landing-page form",
            "media" => (object) [
                "url" => $template_params[3],
                "filename" => $template_params[4]
            ],
            "buttons" => [],
            "carouselCards" => [],
            "location" => new stdClass(),
            "attributes" => new stdClass(),
            "paramsFallbackValue" => [],
        ];

        return Http::post($api_url, $data);
    }
}
