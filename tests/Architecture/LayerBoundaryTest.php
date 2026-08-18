<?php

function architecturePhpFiles(string $relativePath): array
{
    $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$relativePath;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

    return array_values(array_map(
        static fn (SplFileInfo $file): string => $file->getPathname(),
        array_filter(iterator_to_array($iterator), static fn (SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php'),
    ));
}

test('controllers do not execute eloquent queries or use the database facade', function (): void {
    $violations = [];
    foreach ([...architecturePhpFiles('app/Http/Controllers'), ...architecturePhpFiles('Modules')] as $file) {
        if (str_contains($file, DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR) && ! str_contains($file, DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR)) {
            continue;
        }

        $source = file_get_contents($file);
        if (str_contains($source, 'Illuminate\\Support\\Facades\\DB') || preg_match('/::(?:query|where|create|updateOrCreate)\s*\(/', $source)) {
            $violations[] = str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $file);
        }
    }

    expect($violations)->toBe([]);
});

test('livewire components do not inject repositories directly', function (): void {
    $violations = [];
    foreach (architecturePhpFiles('Modules') as $file) {
        if (! str_contains($file, DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Livewire'.DIRECTORY_SEPARATOR)) {
            continue;
        }
        if (str_contains((string) file_get_contents($file), '\\Repositories\\')) {
            $violations[] = str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR, '', $file);
        }
    }

    expect($violations)->toBe([]);
});

test('cross module repository implementations stay private to their module', function (): void {
    $violations = [];
    foreach (architecturePhpFiles('Modules') as $file) {
        $sourceModule = explode(DIRECTORY_SEPARATOR, str_replace(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR, '', $file))[0];
        preg_match_all('/^use Modules\\\\([^\\\\]+)\\\\Repositories\\\\/m', (string) file_get_contents($file), $matches);
        foreach ($matches[1] as $targetModule) {
            if ($targetModule !== $sourceModule) {
                $violations[] = $sourceModule.' -> '.$targetModule.' repository';
            }
        }
    }

    expect(array_values(array_unique($violations)))->toBe([]);
});
