<?php

namespace Retech\Celest\SignMe\Entity;

use Retech\Celest\SignMe\Exceptions\ConnectionException;
use Retech\Celest\SignMe\SignMeClient;

class Company
{
    private int $id;

    private string $name;

    private int $credits;

    private string $webhookSecret;

    private SignMeClient $client;

    public function __construct(array $company, SignMeClient $client)
    {
        $this->client = $client;
        $this->id = $company['id'];
        $this->name = $company['name'];
        $this->webhookSecret = $company['webhookSecret'];
        $this->credits = $company['credits'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }


    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    public function setWebhookSecret(string $webhookSecret): void
    {
        $this->webhookSecret = $webhookSecret;
    }

    /**
     * @throws ConnectionException
     */
    public function update(): void
    {
        $this->client->put('company', [
            'name' => $this->name,
            'webhookSecret' => $this->webhookSecret,
        ]);
    }

}