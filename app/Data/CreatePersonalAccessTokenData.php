<?php

namespace App\Data;

use App\Enums\TokenAbility;

final readonly class CreatePersonalAccessTokenData
{
    /** @param list<string> $abilities */
    public function __construct(public string $email, public string $password, public string $deviceName, public array $abilities, public int $expiresInDays) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            $data['email'],
            $data['password'],
            $data['device_name'],
            $data['abilities'] ?? [TokenAbility::ProjectsRead->value, TokenAbility::TasksRead->value],
            (int) ($data['expires_in_days'] ?? 30),
        );
    }
}
