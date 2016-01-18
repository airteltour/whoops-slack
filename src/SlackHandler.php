<?php
namespace Oponiti\Whoops;

use Maknz\Slack\Client;
use Whoops\Handler\Handler;

class SlackHandler extends Handler
{
    /** @var \Maknz\Slack\Client */
    protected $client;

    /** @var string */
    protected $template;

    /**
     * @param \Maknz\Slack\Client $client
     * @param string $template
     */
    public function __construct(Client $client, $template = null)
    {
        $this->client = $client;
        $this->template = isset($template) ? $template : __DIR__ . '/template.php';
    }

    public function handle()
    {
        $exception = $this->getException();
        $inspector = $this->getInspector();

        ob_start();
        require $this->template;

        $contents = ob_get_contents();
        ob_end_clean();
        $this->sendToSlack($contents);

        return Handler::DONE;
    }

    protected function sendToSlack($message)
    {
        $this->client->send($message);
    }

    protected function printArguments($param, $indent = 0, $key = null)
    {
        for ($i = 0; $i < $indent; $i++) {
            echo "    ";
        }
        if (isset($key)) {
            echo "[{$key}] => ";
        }
        if (is_array($param)) {
            echo "Array[\n";
            foreach ($param as $k => $v) {
                $this->printArguments($v, $indent + 1, $k);
            }
            for ($i = 0; $i < $indent; $i++) {
                echo "    ";
            }
            echo "],\n";
        } elseif (is_string($param)) {
            echo "String(" . $param . "),\n";
        } elseif (is_bool($param)) {
            echo "Boolean(" . ($param ? "true" : "false") . "),\n";
        } elseif (is_numeric($param)) {
            echo "Number(" . $param . "),\n";
        } elseif (is_null($param)) {
            echo "NULL,\n";
        } elseif ($param instanceof \Closure) {
            echo "Closure,\n";
        } elseif (is_object($param)) {
            echo "Object(" . get_class($param) . "),\n";
        } else {
            echo "Unknown,\n";
        }
    }
}
