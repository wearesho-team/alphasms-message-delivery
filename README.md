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

## Cli Usage
You can use simple CLI tool to send messages and check balance:
```php
# Check balance
./alphasms balance
# Send messages interactively
./alphasms send
```

## Usage
### Configuration
- [ConfigInterface](./src/ConfigInterface.php) have to be used to configure requests.
Available implementations:
- [Config](./src/Config.php) - simple implementation using class properties
- [EnvironmentConfig](./src/EnvironmentConfig.php) - loads configuration values from environment using 
[getenv](http://php.net/manual/ru/function.getenv.php)

| Variable             | Required | Description                                                      |
|----------------------|----------|------------------------------------------------------------------|
| ALPHASMS_SENDER_NAME | yes      | Sender Name for SMS (alpha-name)                                 |
| ALPHASMS_API_KEY     | yes      | Can be received on [AlphaSMS Panel](https://alphasms.ua/panel/)  |
| ALPHASMS_WEBHOOK_URL | no       | URL for Webhooks with SMS statuses                               |

## VoiceOTP Service

This section describes how to use the Voice OTP functionality of the AlphaSMS service.

### Usage

The AlphaSMS service provides a method to send one-time passwords (OTP) via voice calls. This is useful for two-factor authentication or verification processes.

```php
<?php

/** @var \Wearesho\Delivery\AlphaSms\Service $service */

// Create a Voice OTP request
$request = new \Wearesho\Delivery\AlphaSms\VoiceOtp\Request(
    id: 12345,                  // Unique identifier for the request
    phoneNumber: '380991234567' // Phone number to receive the voice call
);

try {
    // Send the Voice OTP request
    $response = $service->voiceOtp($request);

    // Get the OTP code (4 digits)
    $code = $response->code();

    // Get the price of the operation
    $price = $response->price();

    echo "Voice OTP sent successfully. Code: {$code}, Price: {$price}";
} catch (\Wearesho\Delivery\Exception $e) {
    // Handle delivery exceptions
    echo "Failed to send Voice OTP: " . $e->getMessage();
}

```

#### Request Parameters

- `id` (int): A unique identifier for the request. This can be used for tracking or reference purposes.
- `phoneNumber` (string): The recipient's phone number in international format (e.g., 380991234567).

#### Response Properties

- `code()` (string): Returns the 4-digit OTP code that was sent to the recipient.
- `price()` (float): Returns the cost of the voice call operation.

#### Error Handling

The voiceOtp() method may throw a \Wearesho\Delivery\Exception in the following cases:

- Invalid request parameters
- Network connectivity issues
- Service provider errors
- Invalid response format

Always wrap the service call in a try-catch block to handle potential exceptions.

#### Command Line Interface

For command-line usage, you can use the provided 
[Symfony Console command](./cli/VoiceOtpCommand.php):

```bash
# Basic usage
$ ./alphasms voice-otp 380991234567

# With custom request ID
$ ./alphasms voice-otp 380991234567 --id=12345
```

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
