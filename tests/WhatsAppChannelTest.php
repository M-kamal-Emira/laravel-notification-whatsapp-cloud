<?php

namespace NotificationChannels\WhatsApp\Test;

use Illuminate\Notifications\Notifiable;
use Netflie\WhatsAppCloudApi\Http\ClientHandler;
use Netflie\WhatsAppCloudApi\Http\RawResponse;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use NotificationChannels\WhatsApp\Exceptions\CouldNotSendNotification;
use NotificationChannels\WhatsApp\Test\Support\DummyNotifiable;
use NotificationChannels\WhatsApp\Test\Support\DummyNotification;
use NotificationChannels\WhatsApp\Test\Support\DummyNotificationWithoutRecipient;
use NotificationChannels\WhatsApp\WhatsAppChannel;
use PHPUnit\Framework\TestCase;

final class WhatsAppChannelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $notifiable = $this->getMockForTrait(Notifiable::class);
        $notification = new DummyNotification();

        $httpClient = \Mockery::mock(ClientHandler::class);
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $notification->toWhatsApp($notifiable)->recipient(),
            'type' => 'template',
            'template' => [
                'name' => $notification->toWhatsApp($notifiable)->configuredName(),
                'language' => ['code' => $notification->toWhatsApp($notifiable)->configuredLanguage()],
                'components' => [],
            ],
        ];
        $headers = [
            'Authorization' => 'Bearer super-secret',
            'Content-Type' => 'application/json',
        ];
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new RawResponse($headers, json_encode($body), 200));

        $whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => '34676202545',
            'access_token' => 'super-secret',
            'client_handler' => $httpClient,
        ]);
        $channel = new WhatsAppChannel($whatsapp);

        $response = $channel->send($notifiable, $notification);

        $this->assertEquals(json_encode($body), $response->body());
        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertNotEmpty($notification->toWhatsApp($notifiable)->recipient());
        $this->assertNotEmpty($notification->toWhatsApp($notifiable)->configuredName());
        $this->assertNotEmpty($notification->toWhatsApp($notifiable)->configuredLanguage());
    }

    /** @test */
    public function it_can_send_a_notification_if_notifiable_provide_a_recipient_from_route()
    {
        $notifiable = new DummyNotifiable();
        $notification = new DummyNotificationWithoutRecipient();

        $httpClient = \Mockery::mock(ClientHandler::class);
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $notifiable->routeNotificationForWhatsApp(),
            'type' => 'template',
            'template' => [
                'name' => $notification->toWhatsApp($notifiable)->configuredName(),
                'language' => ['code' => $notification->toWhatsApp($notifiable)->configuredLanguage()],
                'components' => [],
            ],
        ];
        $headers = [
            'Authorization' => 'Bearer super-secret',
            'Content-Type' => 'application/json',
        ];
        $httpClient->shouldReceive('send')
            ->once()
            ->andReturn(new RawResponse($headers, json_encode($body), 200));

        $whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => '34676202545',
            'access_token' => 'super-secret',
            'client_handler' => $httpClient,
        ]);
        $channel = new WhatsAppChannel($whatsapp);

        $response = $channel->send($notifiable, $notification);

        $this->assertEquals(json_encode($body), $response->body());
        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertNotEmpty($body['to']);
        $this->assertNotEmpty($notification->toWhatsApp($notifiable)->configuredName());
        $this->assertNotEmpty($notification->toWhatsApp($notifiable)->configuredLanguage());
    }

    /** @test */
    public function it_does_not_send_a_notification_if_the_notifiable_does_not_provide_a_recipient()
    {
        $notifiable = $this->getMockForTrait(Notifiable::class);
        $notification = new DummyNotificationWithoutRecipient();

        $httpClient = \Mockery::mock(ClientHandler::class);
        $httpClient->shouldNotHaveReceived('send');

        $whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => '34676202545',
            'access_token' => 'super-secret',
            'client_handler' => $httpClient,
        ]);
        $channel = new WhatsAppChannel($whatsapp);

        $response = $channel->send($notifiable, $notification);

        $this->assertNull($response);
    }

    /** @test */
    public function send_notification_failed()
    {
        $notifiable = $this->getMockForTrait(Notifiable::class);
        $notification = new DummyNotification();

        $httpClient = \Mockery::mock(ClientHandler::class);
        $httpClient
            ->shouldReceive('send')
            ->once()
            ->andReturn(new RawResponse([], json_encode(['error' => true]), 500));

        $whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => '34676202545',
            'access_token' => 'super-secret',
            'client_handler' => $httpClient,
        ]);
        $channel = new WhatsAppChannel($whatsapp);

        $this->expectException(CouldNotSendNotification::class);
        $response = $channel->send($notifiable, $notification);

        $this->assertNull($response);
    }
}
