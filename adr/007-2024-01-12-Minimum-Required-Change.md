# ADR 007: Minimum Required Change

## Status

Accepted - 2024-01-12

## Context

To maintain maximum compatibility with the currently selected set of plugins
as well as potential future plugins, the minimum required change to the
existing codebase should be made.

## Decision

Our changes from "standard" or "default" processing are to be extremely limited
and extensively documented when justified. Changes to default templates should
be extracted to Services or Overrides as much as possible, to avoid any need
for a developer to manage the changes manually.

## Consequences

Failure to adhere to extracted logic and minimum change requirements would result
in a non-viable upgrade path for any developer seeking to use our project. We want
to ensure that as we get close a v1.0 release, our changes are: 

* Justified: changes should be necessary to achieve our goals.

* Isolated: strongly prefer implementation in Nexus Classes to avoid changes to
  core/filament code.

* Precisely targeted: changes should be as small as possible and achieve specific aims.

* Tested: all new and existing code should be exhaustively tested.

* Documented: Any change to application behavior should be documented in the
  commit message and in the documentation repo if it affects the user experience
  or the developer experience.

## Alternatives Considered

There are no alternatives to producing minimum viable change scoped outside
core logic. To pursue another path is madness. 

## Related Decisions

N/A

## References

N/A
