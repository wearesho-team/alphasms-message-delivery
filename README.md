# AlphaSMS Integration
[![Test & Lint](https://github.com/wearesho-team/alphasms-message-delivery/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/wearesho-team/alphasms-message-delivery/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/alphasms-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/alphasms-message-delivery)

[wearesho-team/message-delivery](https://github.com/wearesho-team/message-delivery) implementation of
[Delivery\ServiceInterface](https://github.com/wearesho-team/message-delivery/blob/1.3.4/src/ServiceInterface.php)

## Installation
```bash
composer require wearsho-team/alphasms-message-delivery:^3.0
```

## Usage
### Configuration
- [ConfigInterface](./src/ConfigInterface.php) have to be used to configure requests.
Available implementations:
- [Config](./src/Config.php) - simple implementation using class properties
- [EnvironmentConfig](./src/EnvironmentConfig.php) - loads configuration values from environment using 
[getenv](http://php.net/manual/ru/function.getenv.php)

| Variable             | Required | Description                                                     |
|----------------------|----------|-----------------------------------------------------------------|
| ALPHASMS_SENDER_NAME | yes      | Sender Name for SMS (alpha-name)                                |
| ALPHASMS_API_KEY     | yes      | Can be received on [AlphaSMS Panel](https://alphasms.ua/panel/) |

### Additional methods
Besides implementing Delivery\ServiceInterface [Service](./src/Service.php) provides
```php
<?php

use Wearesho\Delivery;

$config = new Delivery\AlphaSms\Config(
    apiKey: "bb56a4369eb19***cfec6d1776bd25",
    senderName: "alphasms" 
);

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

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
