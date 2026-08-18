<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use Throwable;

interface ExceptionTrackerInterface
{
    public function capture(Throwable $exception, ?Request $request = null): void;
}
