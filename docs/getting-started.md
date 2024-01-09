# Getting Started

For detailed installation steps, please refer to the readme. 

This guide is intended to talk about the architecture and usage of the project. 

## Glossary

| Term       | Definition                                                                                                                                                                       |
|------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Owner      | The organization, person, or entity which hosts, maintains, operates, offers, and/or engineers the application.                                                                  |
| Subscriber | The person (usually a customer or subscriber) who maintains an account in the application.                                                                                       |
| Admin      | A person who is responsible for operations & support of Users, resolution of bugs, etc.                                                                                          |
| 3rd Party  | A person or organization who logs into the application to use the application, but who is not part of the User's business (customers, buyers, sellers, the general public, etc). |
| User Type  | A class of application user granted access to a specific panel using a combination of Model, table, auth guard.                                                                  |
| Panel      | A distinct area of the application, with its own URL path, user type, and set of features.                                                                                       |


### Panels & Users

Each Panel has a distinct User Type, named for the panel itself. For example, the `Admin` panel
has an `Admin` user type. The only exception to this rule is the `Account` panel, which has
a `User` user type (this is the default user type necessary for billing integrations). 

Each Panel has a URL path which is prefixed by the panel name. For example, the `Admin` panel
has a URL path of `/admin`. The account panel has a URL path of `/account`.


### Multiple-User-Tier Architecture

Note: For purposes of counting the number of user tiers, we exclude Admin & Account. This is
because those are administrative/operational panels, and not user-facing.

When we talk about user tiers, we're talking about people who log in to the application and
perform some action within the app, based on the perceived personal or business value of 
doing so. Think end users, buyers, sellers, etc.

Example Single-User-Tier Architecture:

| Tier | Panel   | User Type | Ownership | Description                                      | Path       |
|------|---------|-----------|-----------|--------------------------------------------------|------------|
| 0    | Admin   | Admin     | Owner     | Owner, Operating & Supporting the App/Platform   | `/admin`   |
| 0    | Account | User      | User      | User, managing subscriptions and global settings | `/account` |
| 1    | Operate | Operator  | User      | End Users, using the application as offered      | `/app`     |

Example Multi-User-Tier Architecture:

| Tier | Panel   | User Type | Ownership | Description                                                         | Path       |
|------|---------|-----------|-----------|---------------------------------------------------------------------|------------|
| 0    | Admin   | Admin     | Owner     | Owner, Operating & Supporting the App/Platform                      | `/admin`   |
| 0    | Account | User      | User      | User, managing subscriptions and global settings                    | `/account` |
| 1    | Operate | Operator  | User      | User's employees, configuring & reporting on the platform.          | `/operate` |
| 2    | Buyer   | Buyer     | User      | Third-party Business Partners, Logging in, generating reports, etc. | `/buyer`   |
| 3    | Seller  | Seller    | User      | Third-party Business Partners, Logging in, generating reports, etc. | `/seller`  |

