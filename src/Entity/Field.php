<?php

namespace Retech\Celest\SignMe\Entity;

use Retech\Celest\SignMe\Exceptions\ConnectionException;
use Retech\Celest\SignMe\SignMeClient;

class Field
{
    private string $type;

    private string $signerHash;

    private int $page;

    private float $x;

    private float $y;

    private float $width;

    private float $height;

    private ?string $label;

    private SignMeClient $client;


    private int $id;

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setSignerHash(string $signerHash): void
    {
        $this->signerHash = $signerHash;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function setX(float $x): void
    {
        $this->x = $x;
    }

    public function setY(float $y): void
    {
        $this->y = $y;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getY(): float
    {
        return $this->y;
    }

    public function getX(): float
    {
        return $this->x;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSignerHash(): string
    {
        return $this->signerHash;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function __construct($fieldArray, SignMeClient $client, private readonly string $documentSlug)
    {
        $this->id = $fieldArray['id'];
        $this->type = $fieldArray['type'];
        $this->signerHash = $fieldArray['signerHash'];
        $this->page = $fieldArray['page'];
        $this->x = $fieldArray['x'];
        $this->y = $fieldArray['y'];
        $this->width = $fieldArray['width'];
        $this->height = $fieldArray['height'];
        $this->label = $fieldArray['label'];
        $this->client = $client;
    }

    /**
     * @throws ConnectionException
     */
    public function update(): static
    {
        $data = [];
        foreach (['type', 'signerHash', 'page', 'x', 'y', 'width', 'height', 'label'] as $key) {
            $data[$key] = $this->$key;
        }
        $this->client->put('documents/' . $this->documentSlug . '/fields/' . $this->id, $data);
        return $this;
    }

    /**
     * @throws ConnectionException
     */
    public function delete(): void
    {
        $this->client->delete('documents/' . $this->documentSlug . '/fields/' . $this->id);
    }
}