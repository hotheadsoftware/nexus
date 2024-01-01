# Cloud.Inc Nexus

## Description

This is a Laravel 10+ project that incorporates numerous components with the aim
of providing a scaffold for micro-Saas applications.

## TOP OPEN ISSUES

- [ ] If a user is on a tenant domain, the /admin dashboard should result in a 404.
  - This is not critical but it's annoying me.  
  - I've tried to resolve this one a few different ways:
    - Add a middleware to the admin panel. This works, but causes a "route login not defined" error intermittently.
    - Add a check to the register() method of the admin panel.
      - If I call abort(404), I get a cache not found error instead.
      - If I die() I get a blank page (acceptable, but also not great UX. I would prefer a nicely formatted 404).
      - If I use header('Location: /manage'); exit; I get a redirect loop.

## Architecture

### Phase One: MVP

This app is a mono-repo, monolithic application. It contains two base panels: Admin (central)
and Manage (tenant) panel (naming subject to revision). The central panel is used for initial
sign-up, subscription management, and creation of tenant & domain environments. 

The Manage panel is where users will spend most of their time, using the functionality provided
by the application. They can allow user registration here (public users), or they can create
users in the Admin panel and assign them to the tenant.

The app consists of individual docker containers: App, Database, Cache, Utility. We use Laravel
Sail for local development. 

### Phase Two: Growing Up

Split Admin and Manage panels into separate applications, behind an ingress controller in k8s.
Handle domains dynamically, sending central traffic to Admin and all other traffic to Manage.
Develop locally using Kind or Minikube.

## Feature List

- [x] [Laravel Spark](https://spark.laravel.com) Billing & Subscription Management
- [x] [Domain-Based Multi-Database Multi-Tenancy](https://tenancyforlaravel.com/docs/v3/)
- [x] [Filament v3](https://filamentphp.com/docs) Control Plane (Central Context)
- [x] [Filament v3](https://filamentphp.com/docs) Application Plane (Tenant Context)
- [x] [Laravel Telescope](https://laravel.com/docs/10.x/telescope)
- [x] [Laravel Horizon](https://laravel.com/docs/10.x/horizon)
- [x] [Laravel Pulse](https://pulse.laravel.com/)
- [x] [Authentication Logs & Notifications](https://rappasoft.com/docs/laravel-authentication-log/v1/introduction)
- [x] [Model Change Audits](https://laravel-auditing.com)
- [x] [Model Tagging](https://spatie.be/docs/laravel-tags/v4/introduction)
- [x] [User Roles & Permissions](https://spatie.be/docs/laravel-permission/v6/introduction)

- [ ] Custom Artisan Helper Commands
- [ ] Stretch Goal -- IAC (Infrastructure as Code) with Terraform
    - [ ] S3 Buckets
    - [ ] CloudFront Distributions
    - [ ] Route53 DNS Records
    - [ ] ACM Certificates
    - [ ] ECS Cluster, Task Definitions, Services
    - [ ] RDS Database
    - [ ] Redis Cache
    - [ ] SNS Topics
    - [ ] SQS Queues
- [ ] Stretch Goal -- CI/CD with GitHub Actions
    - [ ] Deploy Terraform Infrastructure
    - [ ] Build & Deploy Application

## Installation

### Requirements

Docker Desktop (MacOS, Windows, Linux) or Docker Engine (Linux)

### Steps

#### Prerequisites

**Pre-Requisites**: You must have a Laravel Spark account & license. If you're part of the
Cloud.Inc organization, ask for a copy of <u>auth.json</u>. If you're not, you'll need to purchase
a license from [Laravel Spark](https://spark.laravel.com) and provide creds during Composer
install operations.

#### Method 1: setup.sh

Instead of having to run all of the commands below, you can clone the repo, then
run `./setup.sh` from the root directory. This will run everything after the clone
operation, and install a pre-commit hook for Pint styling. You'll also get the
option to install some bash aliases to make working with Sail easier.

#### Method 2: Manual

    git clone (your ssh key):/clouddotinc/nexus
    cd nexus
    docker run -v $(pwd):/app composer:latest install --ignore-platform-reqs
    cp .env.example .env

    (edit .env - see Paddle Setup)

    ./vendor/bin/sail up -d
    ./vendor/bin/sail composer install
    ./vendor/bin/sail artisan migrate:fresh --seed
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run dev

#### Paddle Setup

**Pre-requisite**: <u>A domain name that can reach your local machine</u>. You can use something like
[DuckDNS](https://duckdns.org) + port forwarding (on your router), or you can use a tunneling service 
like [NGrok](https://ngrok.io). Regardless of your choice, Paddle will need a path to reach your 
machine with webhook notifications.

1. [Create a Paddle Sandbox account](https://sandbox-vendors.paddle.com)
2. Create a Product (Catalog > Products)
3. Add Prices (Unlimited Monthly, Unlimited Annual)
   a. Note the Price IDs
   b. Add to .env:
      1. PLAN_UNLIMITED_MONTHLY_ID=
      2. PLAN_UNLIMITED_ANNUAL_ID=
4. Create a Notification Webhook (Developer Tools > Notifications)
   1. Add your domain name to the URL (http://YOUR_DOMAIN_HERE/paddle/webhook)
   2. https is optional but then you need SSL termination somewhere.
   3. Add to .env: PADDLE_WEBHOOK_SECRET=(your secret key from webhook)
5. Select Events:
   - transaction.completed
   - transaction.updated
   - subscription.activated
   - subscription.canceled
   - subscription.created
   - subscription.paused
   - subscription.updated
   - customer.updated
6. Create & Record an Authentication Code (Developer Tools > Authentication))
   1. Add to .env: PADDLE_AUTH_CODE=(your auth code)
   2. (Same Page) Add to .env: PADDLE_SELLER_ID=(your seller ID) 



## Usage

This is a standard Laravel 10 + Filament application with a minimal set of
customizations. I make use of `stancl/tenancy` for multi-tenancy. This package
bootstraps most connections/resources so that they are tenant-aware. This
means that database connections, queues, jobs, events, etc, are all scoped
to the client.

### Tenancy & Storage

Because of the way that `stancl/tenancy` works, we need to make sure that
when storing & retrieving tenant assets, we include `$tenant->id` in the
path. (The tenancy package can do this automatically, but it introduces
some undesirable behavior with Filament assets, so I've opted to do it
manually.)

- [ ] TODO: figure out how to make this work with the tenancy package; there may be a way to block certain paths from
  being rewritten.

