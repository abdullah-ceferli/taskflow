<?php

namespace App\Services;

final class FeatureFlagService
{
    public function enabled(string $feature, bool $default = false): bool
    {
        return (bool) config("taskflow.features.{$feature}", $default);
    }
}
