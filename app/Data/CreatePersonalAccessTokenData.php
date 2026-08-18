<?php

namespace App\Data;

final readonly class CreatePersonalAccessTokenData
{
    public function __construct(public string $email, public string $password, public string $deviceName) {}

    public static function fromValidated(array $data): self
    {
        return new self($data['email'], $data['password'], $data['device_name']);
    }
}
