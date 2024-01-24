<?php

namespace App\Pulse;

use App\Models\Administrator;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Laravel\Pulse\Contracts\ResolvesUsers;

class UserResolver implements ResolvesUsers
{
    protected array $resolvedUsers;

    public function load(Collection $keys): self
    {
        // index 0 is the class, index 1 is the id
        $keys = $keys->map(fn ($key) => explode(':', $key));

        $this->resolvedUsers = [
            Administrator::class => Administrator::findMany($keys
                ->filter(fn ($key) => $key[0] === Administrator::class)->map(fn ($key) => $key[1])->values()),

            // do-not-remove-nexus-user-resolver-load-goes-here

            User::class => User::findMany($keys
                ->filter(fn ($key) => $key[0] === User::class)->map(fn ($key) => $key[1])->values()),
        ];

        return $this;
    }

    /**
     * We can use a match here by class type to return a slightly transformed object
     * which tells us a little more about the user type.
     */
    public function find(int|string|null $key): object
    {
        [$class, $id] = explode(':', $key);

        $user = $this->resolvedUsers[$class]->first(fn ($user) => $user->id == $id);

        return $this->getProfile($user);
    }

    protected function getProfile(\Illuminate\Foundation\Auth\User $user): object
    {
        $authenticatableType = match (get_class($user)) {
            Administrator::class => 'Administrator',
            User::class          => 'User',
            default              => 'Unknown',
        };

        return (object) [
            'name'   => $user->name." ($authenticatableType)",
            'extra'  => $user->email,
            'avatar' => 'https://gravatar.com/avatar?d=mp',
        ];
    }

    public function key(Authenticatable $user): int|string|null
    {
        return get_class($user).':'.$user->getAuthIdentifier();
    }
}
