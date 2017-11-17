<?php
namespace Oponiti\Whoops;

use Maknz\Slack\Client;
use Whoops\Handler\Handler;

class SlackHandler extends Handler
{
    /** @var \Maknz\Slack\Client */
    protected $client;

    /** @var array */
    protected $config = [
        /*'template' => __DIR__ . '/template.php',*/
        'getMessageCallStack' => true,
        'getMessageDebug' => true
    ];

    /** @var callable */
    protected $filter;

    /**
     * @param \Maknz\Slack\Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param callable $filter
     */
    public function filter(callable $filter)
    {
        $this->filter = $filter;
    }

    public function handle()
    {
        $exception = $this->getException();
        $inspector = $this->getInspector();

        $messageCallStack = '';
        $messageDebug = '';

        if (
            !isset($this->filter) ||
            call_user_func($this->filter, $exception, $inspector) === true
        ) {

            $fields = [
                [
                    'title' => '서버 발생 시간',
                    'value' => Date('Y-m-d H:i:s'),
                    'short' => true,
                ],
                [
                    'title' => 'URL',
                    'value' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    'short' => true,
                ],
                [
                    'title' => 'Referrer',
                    'value' => $_SERVER['HTTP_REFERER'] ?? "",
                    'short' => true,
                ],
                [
                    'title' => 'User Agent',
                    'value' => $_SERVER['HTTP_USER_AGENT'] ?? "",
                    'short' => true,
                ]
            ];

            if($this->config['getMessageCallStack']){
                $messageCallStack = "\n\n\n*CallStack*";
                foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $key => $row) {
                    if ($key == 0)
                        continue;

                    $message = "";
                    if (isset($row['class']))
                        $message .= $row['class'] . "->" . $row['function'];
                    else
                        $message .= $row['function'];

                    $message .= "() called at [" . $row['file'] . ":" . $row['line'] . "]";

                    $messageCallStack .= "\n#" . $key . " " . $message;
                }

                array_push($fields, [
                    'title' => '실행위치',
                    'value' => $messageCallStack,
                    'short' => false,
                ]);
            }

            if($this->config['getMessageDebug']){
                $message = "";
                if (!empty($_POST)) {
                    $message .= "\n\n*\$_POST*";
                    $message .= "\n" . print_r($_POST, true);
                }
                if (!empty($_GET)) {
                    $message .= "\n\n*\$_GET*";
                    $message .= "\n" . print_r($_GET, true);
                }

                if (!empty($_SERVER)) {
                    $message .= "\n\n*\$_SERVER*";
                    foreach ($_SERVER as $key => $val) {
                        $message .= "\n*" . $key . "* : " . $val;
                    }
                }
                $messageDebug = $message . "\n";
            }

            $this->client->attach([
                'color' => 'danger',
                'fallback' => $exception->getMessage(),
                'text' => "*" . $exception->getMessage() . "*" . $messageCallStack . $messageDebug,
                'fields' => $fields
            ])->send('');

        }

        return Handler::DONE;
    }
}
