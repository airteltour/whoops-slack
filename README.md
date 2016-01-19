Whoops Slack
===

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