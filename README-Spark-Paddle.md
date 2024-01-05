# CDI Nexus + Laravel Spark

## Description

I've made the decision to exclude Laravel Spark from the core Nexus application. 
This was done to avoid a dependency on a paid package. This would have forced users
to pay Laravel, Inc. for a license to use Nexus. This is not desirable.

That said, I do really like how Spark saves time in generating a billing portal and
managing subscriptions. For some spin-off projects, I may choose to include Spark. 
The process of incorporating Spark into a Nexus application is documented here.

## Time Requirement

An experienced developer should be able to complete the initial setup in about 30 
minutes. Integrating subscription checks and other functionality will vary based 
on the complexity of the application.

## Steps

### Prerequisites: Paddle

#### Local Internet-Reachable Environment

Your local environment needs to be reachable from the internet. This can be done
with a combination of DNS + Port Forwarding, or by using a service like Ngrok.
If using Port Forwarding, forward ports 80 and/or 443 to your local machine.

Paddle does not require SSL/TLS for the sandbox webhook. If you want to set up
SSL/TLS, you can use a service like [Let's Encrypt](https://letsencrypt.org/).
For a non-prod environment, this would be great practice but isn't required.

#### Paddle Sandbox Account

[Create a Paddle Sandbox account here](https://sandbox-vendors.paddle.com). 

#### Paddle Sandbox Product

In Catalog > Products, create a product. This will be the name of the application
you're selling. For example, "Nexus".

Add prices for the product. You'll need to add a monthly and annual price. Note
the Price IDs for each. Add them to your .env file: 

    PLAN_UNLIMITED_MONTHLY_ID=
    PLAN_UNLIMITED_ANNUAL_ID=

#### Paddle Sandbox Webhook

In Developer Tools > Notifications, create a webhook. This will be used to receive
notifications from Paddle. Add your domain name to the URL:

    http://YOUR_DOMAIN_HERE/paddle/webhook

Add the secret key to your .env file:

    PADDLE_WEBHOOK_SECRET=(your secret key from webhook)

As noted above, you can use http (unsecured) for your local environment. Using SSL/TLS
is optional and will require additional setup outside the scope of this document.

The webhook should send the following events: 
  - transaction.completed
  - transaction.updated
  - subscription.activated
  - subscription.canceled
  - subscription.created
  - subscription.paused
  - subscription.updated
  - customer.updated

#### Paddle Authentication Code

In Developer Tools > Authentication, create an authentication code. This will be used
to help secure your sandbox and validate your requests. Add the code to your .env file:

    PADDLE_AUTH_CODE=(your auth code)

From the same page, you can see your Seller ID. Add it to your .env file:

    PADDLE_SELLER_ID=(your seller ID)

## Install Spark + Paddle

### Composer Require

[See Laravel Spark Installation Docs](https://spark.laravel.com/docs/10.0/installation)
for the latest procedure. This will require some manual editing of composer.json as well
as an interactive login during the first run of composer. 

After having done so successfully, you'll be prompted to generate an auth.json file. If
you choose to do so, add it to your .gitignore so you're not committing your credentials.
This will allow your local system to automatically authenticate with Spark.

In your CI/CD pipeline, you'll dynamically generate an auth.json file (probably from
a vault like Secrets Manager or Github Secrets) before calling composer install. This 
will allow your CI/CD pipeline to authenticate with Spark.

## Configure Spark

### App Configuration

In config/spark.php, create a reference to your product. Ensure that the plan IDs
line up with the Price IDs you captured from the Spark sandbox.

Add some features to the features array. These will be displayed when users are
viewing the product & pricing options. 

## Billable Users

We're going to use the default Laravel authentication guard (web), expecting a User
model (users table). We'll use the default Laravel User model.

Add the Spark/Billable trait to the User model (don't replace your other traits):

    use Laravel\Spark\Billable;

    class User extends Authenticatable
    {
        use Billable;
    }

## Non-Billable Users

Alternative user types should NOT extend the user model but should use the Authenticatable 
trait. These alternative user tables and types should be created in the Tenant context 
(not central). 

Alternative user types should use a table which is derived from the schema of the
users table, but which is named to match the model as certain functionality is
based on conversion of the model name (specifically, relationship stuff).

The convention is that the various items should be named as such:

Model name maps to panel name.
Table name maps to guard name. Exception: 'web' is the default for users.

**Note:** if you plan to build an API for your application, you'll need to
add the appropriate guards to config/sanctum.php in order to include
them in the auth chain. 

| Model Name | Table Name | Panel Name | Guard Name | Provider Name |
|------------|------------|------------|------------|---------------|
| User       | users      | user       | web        | users         |
| Admin      | admins     | admin      | admins     | admins        |
| Manager    | managers   | manager    | managers   | managers      |
| Buyer      | buyers     | buyer      | buyers     | buyers        |

For example, if we have a user type called Manager, we'll create a managers table
with the same initial schema as the users table. We'll create a Manager model which
uses the Authenticatable trait. We'll create a managers guard which uses the
Manager model.

## Test The System

Navigate to http://localhost/admin and sign up for an account. After signing up,
you should land on the admin panel.

Open a new tab and navigate to http://localhost/billing. You should see a message
that indicates you're on a free trial, with an expiration date. You'll have the 
option to choose a plan and submit payment information. 

Do so, and you should be redirected to the admin panel. You should see a message
that indicates you're on a paid plan, with a "next invoice on" date. 

If you go back to /billing, you should see your plan and have options to change
payment details, select a different plan, or cancel. 

Check your Paddle dashboard. Go to notifications and look at the webhook log to
ensure that everything is flowing through correctly. 

If all of this happened without error, you're ready to start building your
Saas app! If you had to take any other steps, please submit a PR to help
correct this documentation. 
