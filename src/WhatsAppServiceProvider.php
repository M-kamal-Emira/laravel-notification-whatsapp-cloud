<?php

namespace NotificationChannels\WhatsApp;

use Illuminate\Support\ServiceProvider;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $config = [
            'from_phone_number_id' => $this->app->make('config')->get('services.whatsapp-cloud-api.from-phone-number-id'),
            'access_token' => $this->app->make('config')->get('services.whatsapp-cloud-api.token'),
            'graph_version' => $this->app->make('config')->get('services.whatsapp-cloud-api.graph_version', WhatsAppCloudApi::DEFAULT_GRAPH_VERSION),
            'timeout' => $this->app->make('config')->get('services.whatsapp-cloud-api.timeout'),
        ];

        $this->app->bind(WhatsAppCloudApi::class, static fn () => new WhatsAppCloudApi($config));
    }
}
