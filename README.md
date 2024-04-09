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

## Documentation

## Support

## Contributing

## License
MIT
