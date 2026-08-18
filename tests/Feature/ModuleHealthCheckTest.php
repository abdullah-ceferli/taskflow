<?php

use App\Support\ModuleHealthCheck;

test('required modules and public contracts are healthy', function (): void {
    $result = app(ModuleHealthCheck::class)->inspect();

    expect($result['healthy'])->toBeTrue()
        ->and($result['modules'])->not->toContain(false)
        ->and($result['contracts'])->not->toContain(false);
});

test('module health command reports success', function (): void {
    $this->artisan('taskflow:modules:check')->assertSuccessful();
});
