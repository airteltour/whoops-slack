SERVER : `<?php echo gethostname(); ?>`

```
- File : <?php echo $exception->getFile() ?>(<?php echo $exception->getLine() ?>)
- Message : <?php echo $exception->getMessage() ?>

- $_SERVER :
<?php
foreach ($_SERVER as $key => $value) {
    $this->printArguments($value, 1, $key);
}
?>

- $_POST :
<?php
foreach ($_POST as $key => $value) {
    $this->printArguments($value, 1, $key);
}
?>

- $_GET :
<?php
foreach ($_GET as $key => $value) {
    $this->printArguments($value, 1, $key);
}
?>

- Trace :
<?php
foreach ($exception->getTrace() as $i => $trace) {
    echo "    [{$i}] =>\n";
    echo "        - File : ";
    if (isset($trace['file'])) echo "{$trace['file']}";
    if (isset($trace['line'])) echo "({$trace['line']})";
    echo "\n";
    echo "        - Func : ";
    if (isset($trace['class'])) echo "{$trace['class']}::";
    if (isset($trace['function'])) echo "{$trace['function']}";
    echo "\n";
    echo "        - Args :\n";
    foreach ($trace['args'] as $value) {
        $this->printArguments($value, 3);
    }
    echo "\n";
}
?>
```
