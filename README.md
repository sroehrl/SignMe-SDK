# SignMe (sign.celest.services) PHP-SDK

Handle your SignMe-requests in a human-digestible way. The goal is to make it feel like working with local models.

[SignMe](https://sign.celest.services) is a signature service targeted at integrators, rather than a pure UI-solution like most competitors.

***Status: Private BETA. Please do not use in production yet***

## Example

```php

use Retech\Celest\SignMe\SignMeClient;

$sdk = (new SignMeClient())->authenticate();

// create a new document to sign
$newDocument = $sdk->uploadDocument('SDK upload','path/contracts/max-hunter-lease-agreement.pdf');

// add Signatory
$signatoryMax = $newDocument->addSignatory('my-reference-c9d9b8b8', 'Max Hunter', 'm.hunter@example.com');

// create field on page 4
$signatureField = $signatoryMax->addField('Please sign here', 4);

// move field in position using percentages
$signatureField->setX(53.3);
$signatureField->setY(40);

// you can set width/height as well
$signatureField->setHeight(7);

// send changes to API
$signatureField->update();

// and then some pseudo-code to get the picture
$email = new MyEmailClientWrapper();
$email->to('Max Hunter', 'm.hunter@example.com');
$email->body('Hi Max. Please sign this: ' . $signatoryMax->getSignatureLink());
$email->send();
```


## Installation
`composer require retech/sign-me`

## Usage

This package is a one-stop solution for all your remote signMe needs. 
It's functionality can be divided into three categories:

- [api](#pure-api)
- [easy objects](#easy-objects-oo)
- [webhook reader](#webhook-reader)

### pure API
In order to use the public REST-API, method-wrappers simplify your calls.
All of these methods return the result as assoc-array on success and throw the Exception
_Retech\Celest\SignMe\Exceptions\ConnectionException_ when things go wrong.


***authenticate***

| argument | type     | required |
|--- |----| --- |
| $clientId | string   | no (reads from env) |
| $apiKey | string | no (reads from env) |

The method establishes authentication using the environment variables 
_SIGN_ME_CLIENT_ID_ and _SIGN_ME_API_KEY_. Alternatively, you can pass in your 
clientId & ApiKey directly.

```php 
$client = new SignMeClient();
$client->authenticate();
```

***get***

| argument | type     | required |
|----------|----|----------|
| $url     | string   | yes      |

Execute GET-calls against the base-uri `https://sign.celest.services/api/`

```php
$myDocument = $client->get('documents/123-123-123-1231-123123123');
```

***post***

| argument | type     | required |
|----------|----|----------|
| $url     | string   | yes      |
| $payload | array | no        |

Execute POST-calls against the base-uri `https://sign.celest.services/api/`

```php
$myReference = 'user-123-345-523-93645'
$newSignatory = $client->post('signature/123-123-123-1231-123123123', [
    'signerHash' => $myReference,
    'signerEmail' => 'john.doe@email.com'
]);
```

***put***

| argument | type     | required |
|----------|----|----------|
| $url     | string   | yes      |
| $payload | array | yes      |

Execute PUT-calls against the base-uri `https://sign.celest.services/api/`

```php
$webHookUrl = 'https://webhook.your-domain.com?mydocId=1023'
$updatedDoc = $client->put('documents/123-123-123-1231-123123123', [
    'webhookUrl' => $webHookUrl
]);
```

***delete***

| argument | type     | required |
|----------|----|----------|
| $url     | string   | yes      |

Execute DELETE-calls against the base-uri `https://sign.celest.services/api/`

```php
$success = $client->delete('documents/123-123-123-1231-123123123');
```

### Easy Objects (OO)

Other than managing your transactions and company-settings, the use-case of the signMe API is predictable enough
to make your day-to-day life easier with our object oriented interaction possibilities.

***getDocuments***

| argument | type | required           |
|----------|------|--------------------|
| $page    | int  | no (defaults to 1) |

```php
$documents = $client->getDocuments();
$totalPages = $documents['pages']; // e.g. 3
$allDocumentCount = $documents['total'] // e.g. 127
$newestDocument = $documents[documents][0] // Document (see Entities)
```

***getDocument***

| argument      | type   | required |
|---------------|--------|----------|
| $documentSlug | string | yes      |

```php
$myDocument = $client->getDocument('d3373e45-f7bd-43dc-b48e-db5bca1a5493'); // Document (see Entities)
```

***uploadDocument***

| argument  | type   | required |
|-----------|--------|----------|
| $name     | string | yes      |
| $filePath | string | yes      |

Currently accepts PDF & DOCX only

```php
$myDocument = $client->uploadDocument('Mike\'s car sale', __DIR__ . '/file.php'); // Document (see Entities)
```

### Entities

#### Document

- getDownloadUrl(): null|string 
- getDownloadUrl(): null|string
- getFinalInstructions(): null|string
- setFinalInstructions(finalInstructions: null|string): void
- getWebhookUrl(): null|string
- setWebhookUrl(webhookUrl: null|string): void
- getSlug(): string
- getName(): string
- getRequesterHash(): string
- getFields(): array
- getSignatures(): array
- getSignature(signerHash: string): null|Signature
- addSignatory(signerHash: string, [signerName: null|string = null], [signerEmail: null|string = null]): Signature
- update(): Document
#### Signature

- fields: array = [...]
- documentSlug: string
- getSignerHash(): string
- getSignerName(): null|string
- getSignerEmail(): null|string
- getIp(): null|string
- getSignedAt(): DateTimeImmutable|null
- isSigned(): bool
- setSignerName(signerName: null|string): void
- setSignerEmail(signerEmail: null|string): void
- addField(label: string, [page: int = 1], [type: string = 'signature']): Field
- getSignatureLink(): string

#### Field

- setType(type: string): void
- setSignerHash(signerHash: string): void
- setPage(page: int): void
- setX(x: float): void
- setY(y: float): void
- setWidth(width: float): void
- setHeight(height: float): void
- setLabel(label: null|string): void
- getLabel(): null|string
- getHeight(): float
- getWidth(): float
- getY(): float
- getX(): float
- getPage(): int
- getSignerHash(): string
- getType(): string
- update(): Field
- delete(): void

### Webhook Reader

Lastly, let's have a quick look at the webhook validation tool included in this package

```php
$webHook = new Retech\Celest\SignMe\WebHookValidator($mySignatureSecret);
try{
    $webHook->validate();
} catch (WebhookValidationException $e){
    // malicious!! 
}

// readonly properties (available after validate() or parse())

$event = $webHook->event; // Retech\Celest\SignMe\Enums\Event e.g Event::UPDATE
$entity = $webHook->entity; // Retech\Celest\SignMe\Enums\Entity e.g. Entity::DOCUMENT
$slug = $webHook->slug;

// payload
$payload = $webHook->parse();

if($webHook->entity === Entity::DOCUMENT && $payload->isCompleted()){
    // all signatures are valid
} elseif ($webHook->entity === Entity::DOCUMENT) {
    // apparently some signatures are outstanding
    $signatures = $payload->getSignatures(); // e.g. [['ip' => null, 'time' => null, 'signerHash' => '123-123-123-k']]
    ...
    // or let's get the doc
    $sdk = (new SignMeClient())->authenticate();
    $document = $sdk->getDocument($webHook->slug);
    ...
    
}
```


## Support

Before the official release, no public support is provided.


## License
MIT
