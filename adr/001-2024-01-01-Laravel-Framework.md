# ADR 100: Full-Stack Framework

## Status

Accepted - 2024-01-01

## Context

We need a back-end and front-end technology stack to deliver on the core promises of Nexus.
Our goal is to enable extremely rapid development of new Saas services "from soup to nuts". 
This requires frontend and backend services that are tightly integrated and work well together.

## Decision

Nexus will be based on Laravel 10+ and Filament v3 for the backend and frontend respectively.

## Consequences

Making use of Laravel & Filament together will cause the application to be more tightly coupled
than if we were to use a headless CMS and static front-end. This will also lock us into using 
PHP as the primary language for the application. We will depend on these packages being maintained 
and updated by their respective maintainers.

## Alternatives Considered

Modular: a modern Laravel-based backend using inertia & VueJS for front-end connectivity. This
project provides a very nicely styled dashboard and set of components, but would require a lot
of work to get to the same level of functionality as Filament.

For specific projects with a limited scope, this may be a better choice. Nexus, being a general
purpose application scaffold, needs to provide a more complete solution and Filament gets us
there without a lot of additional work.

Any number of backend API frameworks could be used (ie, Python/FastAPI, Node/Express, etc) but
these would require a lot of additional work to get to the same level of functionality as Laravel.

## Related Decisions

None

## References

[Laravel](https://laravel.com/)

[Filament](https://filamentphp.com/)

