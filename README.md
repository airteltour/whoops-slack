Whoops Slack
===

[![Latest Stable Version](https://poser.pugx.org/oponiti/whoops-slack/v/stable.svg)](https://packagist.org/packages/oponiti/whoops-slack)
[![Latest Unstable Version](https://poser.pugx.org/oponiti/whoops-slack/v/unstable.svg)](https://packagist.org/packages/oponiti/whoops-slack)
[![Total Downloads](https://poser.pugx.org/oponiti/whoops-slack/downloads.svg)](https://packagist.org/packages/oponiti/whoops-slack)
[![License](https://poser.pugx.org/oponiti/whoops-slack/license.svg)](https://packagist.org/packages/oponiti/whoops-slack)
[![Build Status](https://img.shields.io/travis/oponiti/whoops-slack/master.svg)](https://travis-ci.org/oponiti/whoops-slack)

'Whoops Slack' is a handler for [Whoops](https://github.com/filp/whoops). It sends message to [Slack](http://slack.com) when error is occured.

## Installing
Use [Composer](http://getcomposer.org) to install Whoops into your project:

```
composer require oponiti/whoops-slack
```

## Usage

```php
$client = new Maknz\Slack\Client('https://hooks.slack.com/services/T00000000/B00000000/xxxxxxxxxxxxxxxxxxxxxxxx', [
    'username' => 'your-user-name',
    'channel' => '#your-channel'
]);

$whoops = new Whoops\Run;
$whoops->pushHandler(new Oponiti\Whoops\SlackHandler($client));
$whoops->register();
```

## Config

```php
new Oponiti\Whoops\SlackHandler($client, [
    'template' => __DIR__ . '/other/yours.template.php',
    'max_array_depth' => 3,
    'max_array_count' => 5,
]);
```

**max_array_depth**

Array value's maximum depth. You will show the message like below.

`max_array_depth = 2`

```
Array[
    [depth1] => Array[
        [depth2] => Array[
            ...many depth...
        ],
    ],
],
```

**max_array_count**

Array value's maximum count. You will show the message like below.

`max_array_count = 3`

```
Array[
    [0] => Array[],
    [1] => Array[],
    [2] => Array[],
    ...many count...
],
```

## Filter

```php
$handler = new Oponiti\Whoops\SlackHandler($client);
$handler->filter(function (\Exception $exception, \Whoops\Exception\Inspector $inspector) {
    if ($exception instanceof \Some\Notice\Exception) {
        return false;
    }
    return true;
});
```

## Offering Infomation 

- File name and line
- Error message
- $_SERVER
- $_POST
- $_GET
- Backtrace