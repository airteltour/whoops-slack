<?php
namespace Oponiti\Whoops;

use Maknz\Slack\Client;
use Whoops\Handler\Handler;

class SlackHandler extends Handler
{
    /** @var \Maknz\Slack\Client */
    protected $client;

    /** @var array */
    protected $config;

    /** @var callable */
    protected $filter;
    
    /** @var array */
    protected $arguments = [];

    /**
     * @param \Maknz\Slack\Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;
        $this->config = $config + [
                'template' => __DIR__ . '/template.php',
                'max_array_depth' => 3,
                'max_array_count' => 5,
            ];
    }

    /**
     * @param callable $filter
     */
    public function filter(callable $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;
    }

    public function handle()
    {
        $exception = $this->getException();
        $inspector = $this->getInspector();

        if (
            !isset($this->filter) ||
            call_user_func($this->filter, $exception, $inspector) === true
        ) {
            $contents = $this->getTemplateContents([
                'exception' => $exception,
                'inspector' => $inspector,
            ] + $this->arguments);
            $this->sendToSlack($contents);
        }

        return Handler::DONE;
    }

    protected function getTemplateContents(array $values = [])
    {
        extract($values);
        ob_start();
        require $this->config['template'];
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    protected function sendToSlack($message)
    {
        $this->client->send($message);
    }

    protected function printArguments($param, $depth = 0, $key = null)
    {
        for ($i = 0; $i < $depth; $i++) {
            echo "    ";
        }
        if ($this->config['max_array_depth'] < $depth) {
            echo "...many depth...\n";
            return;
        }
        if (isset($key)) {
            echo "[{$key}] => ";
        }
        if (is_array($param)) {
            echo "Array[\n";
            $count = 0;
            foreach ($param as $k => $v) {
                if ($count >= $this->config['max_array_count']) {
                    echo "    ...many count...\n";
                    break;
                }
                $this->printArguments($v, $depth + 1, $k);
                $count++;
            }
            for ($i = 0; $i < $depth; $i++) {
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
