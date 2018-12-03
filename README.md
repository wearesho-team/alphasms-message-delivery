# AlphaSMS Integration
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![Build Status](https://travis-ci.org/wearesho-team/alphasms-message-delivery.svg?branch=master)](https://travis-ci.org/wearesho-team/alphasms-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/alphasms-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/alphasms-message-delivery)

[wearesho-team/message-delivery](https://github.com/wearesho-team/message-delivery) implementation of
[Delivery\ServiceInterface](https://github.com/wearesho-team/message-delivery/blob/1.3.4/src/ServiceInterface.php)

## Installation
```bash
composer require wearsho-team/alphasms-message-delivery:^1.0
```

## Usage
### Configuration
[ConfigInterface](./src/ConfigInterface.php) have to be used to configure requests.
Available implementations:
- [Config](./src/Config.php) - simple implementation using class properties
- [EnvironmentConfig](./src/EnvironmentConfig.php) - loads configuration values from environment using 
[horat1us/environment-config](https://packagist.org/packages/horat1us/environment-config) based on 
[getenv](http://php.net/manual/ru/function.getenv.php)

| Variable          | Required                  | Description                                                                             |
|-------------------|---------------------------|-----------------------------------------------------------------------------------------|
| ALPHASMS_LOGIN    | if empty ALPHASMS_API_KEY | should be used if API key cannot be generated                                           |
| ALPHASMS_PASSWORD | if empty ALPHASMS_API_KEY | should be used if API key cannot be generated                                           |
| ALPHASMS_KEY      | no                        | API key should be used. Can be received on [AlphaSMS Panel](https://alphasms.ua/panel/) |
| ALPHASMS_SENDER_NAME | no                     | Sender name that used for recipient message |

`.env` example:
```dotenv
ALPHASMS_LOGIN=
ALPHASMS_PASSWORD=
ALPHASMS_KEY=
ALPHASMS_SENDER_NAME=
```

### Additional methods
Besides implementing Delivery\ServiceInterface [Service](./src/Service.php) provides
```php
<?php

use Wearesho\Delivery;

$service = new Delivery\AlphaSms\Service(
    $config = new Delivery\AlphaSms\EnvironmentConfig(),
    $client = new GuzzleHttp\Client()
);

$service->send(new Delivery\Message('content', 'recipient')); // send message

$balance = $service->balance(); // fetch balance on current account
$balance->getAmount();
$balance->getCurrency();
```

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
