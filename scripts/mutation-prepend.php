<?php

$root = dirname(__DIR__);
require_once $root.'/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    if ($class !== 'Modules\\Tasks\\Services\\TaskStatusService') {
        return;
    }

    $mutant = getenv('TASKFLOW_MUTANT_FILE');
    if (is_string($mutant) && $mutant !== '' && is_file($mutant)) {
        require $mutant;
    }
}, true, true);
