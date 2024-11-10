<?php

namespace Samjuk\Varnish\Requests;

use Aero\Common\Requests\AeroRequest;

class UpdateVarnishRequest extends AeroRequest
{

    public function rules() : array
    {
        return [
            'enabled' => 'max:255',
            'debug_mode' => 'max:255',
            'app_host' => 'max:255',
            'varnish_host' => 'required|max:255',
            'varnish_port' => 'required|max:6'
        ];
    }

}