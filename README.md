Whoops Slack
===

Whoops라이브러리에서 Slack을 사용할 수 있도록 지원합니다.

## 사용법

```php
$client = new Client('https://hooks.slack.com/services/T00000000/B00000000/xxxxxxxxxxxxxxxxxxxxxxxx', [
    'username' => 'Oponiti',
    'channel' => '#your-channel'
]);

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->pushHandler(new SlackHandler($client));

$whoops->register();
```
