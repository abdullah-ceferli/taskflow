<?php

use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$sourcePath = $root.'/Modules/Tasks/app/Services/TaskStatusService.php';
$source = file_get_contents($sourcePath);
$directory = $root.'/storage/framework/testing/mutation-pilot';

if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
    fwrite(STDERR, "Could not create the mutation pilot directory.\n");
    exit(1);
}

$mutants = [
    'remove todo cancellation edge' => [
        '[TaskStatus::InProgress, TaskStatus::Cancelled]',
        '[TaskStatus::InProgress]',
    ],
    'remove review completion edge' => [
        '[TaskStatus::InProgress, TaskStatus::Done]',
        '[TaskStatus::InProgress]',
    ],
    'reopen done into todo' => [
        'TaskStatus::Done ? TaskStatus::InProgress : TaskStatus::Todo',
        'TaskStatus::Done ? TaskStatus::Todo : TaskStatus::Todo',
    ],
    'deny every manager reopen' => [
        '&& $this->projects->forActor($task->project_id, $actor)->manager',
        '&& false && $this->projects->forActor($task->project_id, $actor)->manager',
    ],
];

$baseCommand = [PHP_BINARY, '-d', 'auto_prepend_file='.$root.'/scripts/mutation-prepend.php', $root.'/vendor/bin/pest', '--configuration='.$root.'/phpunit.xml', '--filter=mutation', '--colors=never'];
$original = new Process($baseCommand, $root);
$original->setTimeout(60);
$original->run();
if (! $original->isSuccessful()) {
    fwrite(STDERR, "The mutation guard tests do not pass before mutation.\n".$original->getErrorOutput().$original->getOutput());
    exit(1);
}

$killed = 0;
foreach ($mutants as $name => [$search, $replacement]) {
    if (! str_contains($source, $search)) {
        fwrite(STDERR, "Mutation target not found: {$name}\n");
        exit(1);
    }

    $mutantPath = $directory.'/TaskStatusService-'.hash('sha256', $name).'.php';
    file_put_contents($mutantPath, str_replace($search, $replacement, $source));
    $process = new Process($baseCommand, $root, ['TASKFLOW_MUTANT_FILE' => $mutantPath]);
    $process->setTimeout(60);
    $process->run();
    @unlink($mutantPath);

    if ($process->isSuccessful()) {
        fwrite(STDERR, "Escaped mutant: {$name}\n");

        continue;
    }

    $killed++;
    fwrite(STDOUT, "Killed mutant: {$name}\n");
}

$score = (int) round(($killed / count($mutants)) * 100);
fwrite(STDOUT, "Mutation pilot score: {$score}% ({$killed}/".count($mutants).")\n");

exit($score >= 80 ? 0 : 1);
