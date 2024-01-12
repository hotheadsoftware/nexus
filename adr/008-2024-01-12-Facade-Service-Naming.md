# ADR 007: Minimum Required Change

## Status

Accepted - 2024-01-12

## Context

We want to name things in a way that's consistent and easy to interpret by
anyone without a lot of cognitive overhead or investigation. 

## Decision

Services and Facades will use the following naming convention: 

If the service is bound as a singleton, it will be named using the singular noun. 

If the service is bound as a transient, it will be named using the plural noun.

Example: 

`Environment` is singular because it's a singleton. When we use the Facade we 
will always get the same instance, regardless of process. 

`Colors` is plural because each time we call the Facade, we'll get a new instance
of the class. 

## Consequences

This will ensure that you can quickly and reliably determine whether a Nexus Facade
returns a singleton or a transient.

Without this pattern, you could encounter unstable behavior across Facades. 


## Alternatives Considered

None

## Related Decisions

None

## References

None
