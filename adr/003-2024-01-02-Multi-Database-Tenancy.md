# ADR 001: Multi-Database Tenancy

## Status

Accepted - 2024-01-02

## Context

Nexus' core purpose is to provide a multi-tenant Saas application scaffold. This means that we need to provide
a mechanism for creating new tenants, and an isolation mechanism for those tenants. Our options are:

1. Single Database, Shared Schema
2. Single Database, Separate Schema
3. Multiple Databases

## Decision

Nexus will be a multi-database solution to offer maximum data isolation to its clients.

- Each tenant will have its own database.
- Data isolation is at maximum here. 
- Migrations are simple: just move the database to another server. 

## Consequences

- Nexus will require a database connection for each tenant.
- Nexus will require a database connection for the central context.
- Nexus will not be able to perform cross-database queries.
- Any roll-up or summary reporting will be a long-running process as it may require many connections.

## Alternatives Considered

- Single Database, Shared Schema: The simplest solution offers minimal data isolation and a much more complex
  migration story. Developers need to be mindful of query scoping, ensuring that they don't accidentally leak data.
  In this approach, all data is stored in the same tables, but with a `tenant_id` column to scope the data.
 
- Single Database, Separate Schema: Each tenant has its own schema, which improves data isolation. Queries can
  join against multiple schema, so global reporting is somewhat streamlined. However, the migration story is still
  complex as both the database (objects) and schema need to migrated to another server if we're moving a tenant. 

## Related Decisions

None

## References

[TenancyForLaravel](https://tenancyforlaravel.com/)
