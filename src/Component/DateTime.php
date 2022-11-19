<?php

namespace NotificationChannels\WhatsApp\Component;

class DateTime extends Component
{
    private \DateTimeImmutable $dateTime;

    private string $format;

    public function __construct(\DateTimeImmutable $dateTime, string $format = 'Y-m-d H:i:s')
    {
        $this->dateTime = $dateTime;
        $this->format = $format;
    }

    public function toArray(): array
    {
        return [
            'type' => 'date_time',
            'date_time' => [
                'fallback_value' => $this->dateTime->format($this->format),
            ],
        ];
    }
}
