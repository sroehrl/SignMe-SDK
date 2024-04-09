<?php

namespace Retech\Celest\SignMe\Entity;

/**
 * @method string|null getSlug()
 * @method string|null getName()
 * @method array|null getSignatures()
 * @method string|null getDownloadUrl()
 * @method string|null getCompleted()
 * @method bool|null isCompleted()
 */
class Payload
{
    private array $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function __call(string $name, array $arguments)
    {
        if(str_starts_with($name, 'get')) {
            $key = lcfirst(substr($name, 3));
            if(isset($this->data[$key])) {
                return $this->data[$key];
            }
        } else if(str_starts_with($name, 'is')) {
            $key = lcfirst(substr($name, 3));
            if(isset($this->data[$key])) {
                $this->data[$key] = !empty($this->data[$key]);
            }
        }
        return null;
    }
}