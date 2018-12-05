# AlphaSMS Integration
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![Build Status](https://travis-ci.org/wearesho-team/alphasms-message-delivery.svg?branch=master)](https://travis-ci.org/wearesho-team/alphasms-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/alphasms-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/alphasms-message-delivery)

[wearesho-team/message-delivery](https://github.com/wearesho-team/message-delivery) implementation of
[Delivery\ServiceInterface](https://github.com/wearesho-team/message-delivery/blob/1.3.4/src/ServiceInterface.php)

## Installation
```bash
composer require wearsho-team/alphasms-message-delivery:^2.2.0
```

## Usage
### Configuration
[ConfigInterface](./src/ConfigInterface.php) have to be used to configure requests.
Available implementations:
- [Config](./src/Config.php) - simple implementation using class properties
- [EnvironmentConfig](./src/EnvironmentConfig.php) - loads configuration values from environment using 
[getenv](http://php.net/manual/ru/function.getenv.php)

| Variable          | Required                  | Description                                                                             |
|-------------------|---------------------------|-----------------------------------------------------------------------------------------|
| ALPHASMS_LOGIN    | if empty ALPHASMS_API_KEY | should be used if API key cannot be generated                                           |
| ALPHASMS_PASSWORD | if empty ALPHASMS_API_KEY | should be used if API key cannot be generated                                           |
| ALPHASMS_API_KEY  | no                        | API key should be used. Can be received on [AlphaSMS Panel](https://alphasms.ua/panel/) |

### Additional methods
Besides implementing Delivery\ServiceInterface [Service](./src/Service.php) provides
```php
<?php

use Wearesho\Delivery;

$config = new Delivery\AlphaSms\Config;
$config->login = '380000000000';
$config->password = 'qwerty123';

$service = new Delivery\AlphaSms\Service($config, new GuzzleHttp\Client);
```

- Check balance on current account
```php
<?php

use Wearesho\Delivery;

/** @var Delivery\AlphaSms\Service $service */

$balance = $service->balance();
$balance->getAmount();
$balance->getCurrency();

$message = (string)$balance; // will output "{amount} {currency}"
```

- Get cost of sending messages on concrete phone numbers
```php
<?php

use Wearesho\Delivery;

/** @var Delivery\AlphaSms\Service $service */
/** @var Delivery\AlphaSms\Response\CostCollection $costs */

$costs = $service->cost([
    '380000000001',
    '380000000002'
]); // fetch costs of sending message on concrete phones

$sum = $costs->sum(); // total cost of sending

/** @var Delivery\AlphaSms\Response\Cost $singleCost */
foreach ($costs as $singleCost) {
    $singleCost->getRecipient();
    $singleCost->getAmount();
    $singleCost->getCurrency();
    
    $singleCost->jsonSerialize(); // cast to json
    (string)$singleCost; // cast to string
}

(string)$costs; // cast collection to string using Cost::__toString() format
$costs->jsonSerialize(); // cast collection to array
```

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
