<?php

namespace App\Services;

use App\Data\CreatePersonalAccessTokenData;
use App\Enums\TokenAbility;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthenticationService
{
    public function __construct(
        private readonly WorkspaceService $workspaces,
        private readonly SecurityMonitor $security,
    ) {}

    /** @return array{user: User, workspace: Workspace} */
    public function registerMember(string $name, string $email, string $password): array
    {
        return DB::transaction(function () use ($name, $email, $password): array {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $user->assignRole(UserRole::Member->value);

            return ['user' => $user, 'workspace' => $this->workspaces->createFor($user, $user->name.' Workspace')];
        });
    }

    public function createPersonalAccessToken(CreatePersonalAccessTokenData $data): NewAccessToken
    {
        $user = User::query()->where('email', $data->email)->first();
        if ($user === null || ! Hash::check($data->password, $user->password)) {
            $this->security->authenticationFailure('api_token', $data->email, request());
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        return $this->issue($user, $data->deviceName, $data->abilities, now()->addDays($data->expiresInDays));
    }

    public function tokenAbilities(): array
    {
        return array_column(TokenAbility::cases(), 'value');
    }

    /** @param list<string> $abilities */
    public function createForAuthenticatedUser(User $user, string $password, string $deviceName, array $abilities, int $expiresInDays): NewAccessToken
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['The password is incorrect.']]);
        }

        return $this->issue($user, $deviceName, $abilities, now()->addDays($expiresInDays));
    }

    public function rotate(User $user, PersonalAccessToken $token): NewAccessToken
    {
        abort_unless((int) $token->tokenable_id === $user->id && $token->tokenable_type === $user->getMorphClass(), 404);
        $expiresAt = $token->expires_at && $token->expires_at->isFuture() ? $token->expires_at : now()->addDays(30);
        $replacement = $this->issue($user, $token->name, $token->abilities ?? [], $expiresAt);
        $token->delete();

        return $replacement;
    }

    public function revoke(User $user, PersonalAccessToken $token): void
    {
        abort_unless((int) $token->tokenable_id === $user->id && $token->tokenable_type === $user->getMorphClass(), 404);
        $token->delete();
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->delete();
    }

    public function revokeCurrentAccessToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /** @param list<string> $abilities */
    private function issue(User $user, string $deviceName, array $abilities, \DateTimeInterface $expiresAt): NewAccessToken
    {
        $allowed = $this->tokenAbilities();
        $abilities = array_values(array_unique(array_intersect($abilities, $allowed)));
        if ($abilities === []) {
            throw ValidationException::withMessages(['abilities' => ['Select at least one allowed ability.']]);
        }

        return $user->createToken(trim($deviceName), $abilities, $expiresAt);
    }
}
