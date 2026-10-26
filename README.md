# AlphaSMS Integration
[![Test & Lint](https://github.com/wearesho-team/alphasms-message-delivery/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/wearesho-team/alphasms-message-delivery/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/v/stable.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![Total Downloads](https://poser.pugx.org/wearesho-team/alphasms-message-delivery/downloads.png)](https://packagist.org/packages/wearesho-team/alphasms-message-delivery)
[![codecov](https://codecov.io/gh/wearesho-team/alphasms-message-delivery/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/alphasms-message-delivery)

[wearesho-team/message-delivery](https://github.com/wearesho-team/message-delivery) implementation of
[Delivery\ServiceInterface](https://github.com/wearesho-team/message-delivery/blob/master/src/ServiceInterface.php)

Compatibility: tested on PHP 8.4, PHP 8.5

## Installation
```bash
composer require wearesho-team/alphasms-message-delivery:^3.3
```

## Cli Usage
You can use simple CLI tool to send messages and check balance:
```php
# Check balance
./alphasms balance
# Send messages interactively
./alphasms send
# Send OTP via voice call
./alphasms voice-otp 380991234567
# Send OTP via Telegram
./alphasms telegram-otp 380991234567
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

## TelegramOTP Service

This section describes how to send one-time passwords (OTP) via Telegram
using [AlphaSMS Telegram OTP](https://docs.alphasms.net/api/json/telegram/otp/) API.

Unlike Voice OTP, the code is generated on your side and delivered to the recipient as a Telegram message.

### Usage

```php
<?php

/** @var \Wearesho\Delivery\AlphaSms\Service $service */

// Create a Telegram OTP request
$request = new \Wearesho\Delivery\AlphaSms\TelegramOtp\Request(
    id: 12345,                   // Unique identifier for the request
    phoneNumber: '380991234567', // Phone number to receive the Telegram message
    code: '123456',              // OTP code to send (4-8 digits)
    lifetime: 300,               // Optional: Telegram message lifetime in seconds (min 60)
    webhookUrl: 'https://example.com/alphasms-webhook' // Optional: URL for delivery status webhooks
);

try {
    // Send the Telegram OTP request
    $response = $service->telegramOtp($request);

    // Get AlphaSMS message ID (can be used to match delivery status webhooks)
    $messageId = $response->messageId();

    echo "Telegram OTP sent successfully. Message ID: {$messageId}";
} catch (\Wearesho\Delivery\Exception $e) {
    // Handle delivery exceptions
    echo "Failed to send Telegram OTP: " . $e->getMessage();
}

```

#### Request Parameters

- `id` (int): A unique identifier for the request in your system.
- `phoneNumber` (string): The recipient's phone number in international format (e.g., 380991234567).
- `code` (string): The one-time password, from 4 to 8 digits.
- `lifetime` (int|null): Telegram message lifetime in seconds, minimum 60. AlphaSMS default is 2 days.
- `webhookUrl` (string|null): URL for delivery status webhooks.
If not set, `ConfigInterface::getWebhookUrl()` (`ALPHASMS_WEBHOOK_URL`) is used.

The request constructor throws `\InvalidArgumentException` if `code` or `lifetime` are invalid.

#### Response Properties

- `messageId()` (string): Returns the AlphaSMS message identifier.

#### Error Handling

The telegramOtp() method may throw a \Wearesho\Delivery\Exception in the following cases:

- Network connectivity issues
- Service provider errors (e.g., `Access denied` with code 2001, message-level errors with code 2002)
- Invalid response format

#### Command Line Interface

For command-line usage, you can use the provided
[Symfony Console command](./cli/TelegramOtpCommand.php):

```bash
# Basic usage (random 6-digit code will be generated)
$ ./alphasms telegram-otp 380991234567

# With custom code, request ID and message lifetime
$ ./alphasms telegram-otp 380991234567 --code=4321 --id=12345 --lifetime=300
```

## Authors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
