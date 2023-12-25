# Cloud.Inc Nexus

## Description

This is a Laravel 10+ project that incorporates numerous components with the aim
of providing a scaffold for micro-Saas applications.

## Feature List

- [x] Domain-Based Multi-Database Multi-Tenancy (Every Tenant on its own Domain & Database)
- [x] Filament v3 Control Plane (Central Context)
- [x] Filament v3 Application Plane (Tenant Context)
- [x] Debugging: Laravel Telescope
- [x] Queue Management: Laravel Horizon
- [x] App Monitoring: Laravel Pulse
- [ ] Model Tagging (spatie/laravel-tags)
- [ ] User Roles & Permissions (spatie/laravel-permission)
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

1. Clone the repository
2. Composer Install

   a. `composer install` if you have PHP & Composer installed locally, OR

   b. `docker run -v $(pwd):/app composer install` if you are using Docker
   
       You might run into errors around missing certain php extensions, but can override those with flags provided by
       the error output.
3. Copy .env.example to .env
4. ./vendor/bin/sail up -d
5. ./vendor/bin/sail artisan migrate:fresh --seed
6. ./vendor/bin/sail npm install
7. ./vendor/bin/sail npm run dev

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

