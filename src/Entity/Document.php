<?php

namespace Retech\Celest\SignMe\Entity;

use Retech\Celest\SignMe\SignMeClient;

class Document
{
    private string $slug;

    private string $name;

    private string $requesterHash;

    private array $fields;

    private array $signatures = [];

    private ?string $finalInstructions;

    private ?string $webhookUrl;

    private ?string $downloadUrl;

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }
    public function getFinalInstructions(): ?string
    {
        return $this->finalInstructions;
    }

    public function setFinalInstructions(?string $finalInstructions): void
    {
        $this->finalInstructions = $finalInstructions;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    private SignMeClient $client;

    public function __construct(array $document, SignMeClient $client)
    {
        $this->client = $client;
        $this->slug = $document['slug'] ?? '';
        $this->name = $document['name'] ?? '';
        $this->requesterHash = $document['requesterHash'] ?? '';
        $this->fields = $document['fields'] ?? [];
        if(isset($document['signatures'])) {
            foreach ($document['signatures'] as $signature) {
                $this->signatures[] = new Signature($signature, $client, $this->getSlug());
            }
        }
        if(isset($document['fields'])) {
            foreach ($document['fields'] as $field) {
                $newField = new Field($field, $client, $this->getSlug());
                $this->fields[] = $newField;
                // bind
                foreach ($this->signatures as $i => $signature) {
                    if ($signature->getSignerHash() === $field['signerHash']) {
                        $signature->fields[] = $newField;
                    }
                }
            }
        }


        $this->finalInstructions = $document['finalInstructions'] ?? null;
        $this->webhookUrl = $document['webhookUrl'] ?? null;
        $this->downloadUrl = $document['downloadUrl'] ?? null;

    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequesterHash(): string
    {
        return $this->requesterHash;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getSignatures(): array
    {
        return $this->signatures;
    }
    public function getSignature(string $signerHash): ?Signature
    {
        foreach ($this->signatures as $signature) {
            if ($signature->getSignerHash() === $signerHash) {
                return $signature;
            }
        }
        return null;
    }
    public function addSignatory(string $signerHash, string $signerName = null, string $signerEmail = null): Signature
    {
        $data = ['signerHash' => $signerHash];
        foreach (['signerName', 'signerEmail'] as $key) {
            if (isset($$key)) {
                $data[$key] = $$key;
            }
        }
        $signature = $this->client->post('signature/' . $this->getSlug(), $data);
        $signatory = new Signature($signature, $this->client, $this->getSlug());
        $this->signatures[] = $signatory;
        return $signatory;
    }

}