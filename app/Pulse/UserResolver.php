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

    public function find(int|string|null $key): object
    {

        [$class, $id] = explode(':', $key);

        $user = $this->resolvedUsers[$class]->first(
            fn ($user) => $user->id == $id
        );

        return $this->getProfile($user);
    }

    /**
     * All Nexus user types extend this User model. We use this to get the necessary
     * panel detail.
     */
    protected function getProfile(\Illuminate\Foundation\Auth\User $user): object
    {
        return (object) [
            'name'  => $user->name,
            'extra' => $user->email,
        ];

    }

    public function key(Authenticatable $user): int|string|null
    {

        return get_class($user).':'.$user->getAuthIdentifier();

    }
}
