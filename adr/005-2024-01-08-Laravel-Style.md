# ADR 005: Laravel Style

## Status
Accepted (Modified) - 2024-01-08

## Context

All software projects should establish and adhere to a style guide. This
style guide should be enforced by a linter, and the linter should be run
as part of the CI/CD pipeline.

To minimize cognitive load, it is helpful for all code in one language to
follow the same style guide. The Laravel Framework adheres to a style which
is used throughout the framework and is recommended for use in applications
built on the framework (psr-2).

## Decision

Nexus will use the Laravel style guide for all PHP code, with the following
exception: 

- Consecutive array assignments will be vertically aligned. For example:

```php
// Default Laravel Style
$foo = [
    'thing' => 'baz',
    'something' => 'quux',
];

// Nexus Style
$foo = [
    'thing' => 'baz',
    'something' => 'quux',
];
```

This project will include recommended content for a pre-commit hook which 
will automatically fix style issues. This will be included in the project
repository and will be **required** for acceptance of any merge request.

## Consequences

No significant consequences of this decision are expected. PRs will be
easier to review and merge with fewer unnecessary changes to parse. 

## Alternatives Considered

PSR-12 was considered, but does not include array alignment. Laravel
Style (unmodified) was also considered but rejected for the same
reason. 

## Related Decisions

None.

## References

https://laravel.com/docs/10.x/contributions#coding-style

https://www.php-fig.org/psr/psr-2/

https://www.php-fig.org/psr/psr-12/
