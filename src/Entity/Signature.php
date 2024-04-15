<?php

namespace Retech\Celest\SignMe\Entity;

use Retech\Celest\SignMe\SignMeClient;

class Signature
{
    private string $signerHash;

    private ?string $signerName;

    private ?string $signerEmail;

    private ?string $ip;

    private ?\DateTimeImmutable $signedAt;

    private bool $hasSigned = false;

    public array $fields = [];
    public function __construct(array $signatureData, private SignMeClient $client, private string $documentSlug)
    {
        $this->signerHash = $signatureData['signerHash'] ?? '';
        $this->signerName = $signatureData['signerName'] ?? null;
        $this->signerEmail = $signatureData['signerEmail'] ?? null;
        $this->ip = $signatureData['ip'] ?? null;
        $this->signedAt = $signatureData['signedAt']['value'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s T', $signatureData['signedAt']['value'] . ' ' . $signatureData['signedAt']['dateTime']['timezone']) : null;
        $this->hasSigned = !!$this->signedAt;
    }

    public function getSignerHash(): string
    {
        return $this->signerHash;
    }

    public function getSignerName(): ?string
    {
        return $this->signerName;
    }

    public function getSignerEmail(): ?string
    {
        return $this->signerEmail;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getSignedAt(): ?\DateTimeImmutable
    {
        return $this->signedAt;
    }

    public function isSigned(): bool
    {
        return $this->hasSigned;
    }

    public function setSignerName(?string $signerName): void
    {
        $this->signerName = $signerName;
    }

    public function setSignerEmail(?string $signerEmail): void
    {
        $this->signerEmail = $signerEmail;
    }

    public function addField(string $label, int $page = 1, string $type = 'signature') : Field
    {
        $response = $this->client->post('documents/' . $this->documentSlug . '/fields', [
            'page' => $page,
            'signerHash' => $this->getSignerHash(),
            'type' => $type,
            'label' => $label,
            'x' => 2,
            'y' => 3,
            'width' => 16,
            'height' => 7
        ]);
        $newField = new Field($response, $this->client, $this->documentSlug);
        $this->fields[] = $newField;
        return $newField;
    }
    public function getSignatureLink(): string
    {
        return $this->client->getBaseUrl() . 'sign/' . $this->documentSlug . '/' . $this->getSignerHash();
    }
}