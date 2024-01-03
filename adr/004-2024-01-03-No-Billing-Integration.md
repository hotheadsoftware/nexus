# ADR 001: No Billing

## Status

Accepted - 2024-01-03

## Context

Nexus is a Saas-application scaffold intended to make it easy to stand up a multi-database, multi-tenant application.
To maintain maximum flexibility, we want to avoid any assumptions about the billing model that will be used by the
application. This means that we will not include any billing functionality in the core application.

## Decision

Nexus will not include Laravel Spark or any other billing functionality in the core application. We may, at our
own discretion, elect to provide documentation which aids a user in configuring certain billing packages.

## Consequences

Users will need to make a determination as to how they'd like to incorporate billing into their applications.

## Alternatives Considered

An initial implementation included Laravel Spark, but it was removed to avoid a dependency on a paid package.
This would have forced users to pay Laravel, Inc. for a license to use Nexus. This is not desirable.

## Related Decisions

None

## References

[Laravel Spark](https://spark.laravel.com/)


