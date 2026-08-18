<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

final class MigrationCompatibilityInspector
{
    /** @return list<array{file: string, rule: string}> */
    public function inspect(string $path): array
    {
        $issues = [];
        foreach (File::glob(rtrim($path, '/\\').'/*.php') as $file) {
            $source = File::get($file);
            $up = $this->upMethod($source);
            foreach ($this->unsafeRules() as $rule => $pattern) {
                if (preg_match($pattern, $up) === 1) {
                    $issues[] = ['file' => basename($file), 'rule' => $rule];
                }
            }
        }

        return $issues;
    }

    private function upMethod(string $source): string
    {
        $start = strpos($source, 'function up');
        $end = strpos($source, 'function down');
        if ($start === false) {
            return '';
        }

        return substr($source, $start, $end === false ? null : $end - $start);
    }

    /** @return array<string, string> */
    private function unsafeRules(): array
    {
        return [
            'table-drop' => '/Schema::drop(?:IfExists)?\s*\(/i',
            'column-drop' => '/->dropColumn\s*\(/i',
            'column-rename' => '/->renameColumn\s*\(/i',
            'in-place-column-change' => '/->change\s*\(/i',
            'destructive-sql' => '/\b(?:DROP|TRUNCATE|DELETE\s+FROM)\b/i',
        ];
    }
}
