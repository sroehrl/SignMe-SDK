<?php

namespace Retech\Celest\SignMe;

use Retech\Celest\SignMe\Entity\Payload;
use Retech\Celest\SignMe\Enums\Entity;
use Retech\Celest\SignMe\Enums\Event;
use Retech\Celest\SignMe\Exceptions\WebhookValidationException;

class WebHookValidator
{
    private string $secret;

    private bool $throw = true;

    public readonly Event $event;

    public readonly Entity $entity;

    public readonly string $slug;

    public function __construct(string $secret = null)
    {
        $this->secret = $secret ?? getenv('SIGN_ME_CLIENT_ID');
    }

    public function throwOnInvalid(bool $throw = true): static
    {
        $this->throw = $throw;
        return $this;
    }

    public function parse(): Payload
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if(!isset($this->event)){
            $this->event = Event::tryFrom($data['event']);
            $this->entity = Entity::tryFrom($data['entity']);
            $this->slug = $data['content']['slug'];
        }

        return new Payload($data['content']);

    }

    /**
     * @throws WebhookValidationException
     */
    public function validate(): static|bool
    {
        $headers = getallheaders();
        if(!isset($headers['X-SignMe-HMAC-SHA256'])){
            return $this->throw ? throw new WebhookValidationException('Missing signature') : false;
        }
        $hmac = $headers['X-SignMe-HMAC-SHA256'];
        $payload = stripslashes(file_get_contents('php://input'));
        $valid =  hash_equals(hash_hmac('sha256', $payload, $this->secret), $hmac);
        if(!$valid){
            return $this->throw ? throw new WebhookValidationException('Invalid signature') : false;
        }
        if(!isset($this->event)){
            $data = json_decode($payload, true);
            $this->event = Event::tryFrom($data['event']);
            $this->entity = Entity::tryFrom($data['entity']);
            $this->slug = $data['content']['slug'];
        }

        return $this;
    }

}