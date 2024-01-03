<?php

namespace App\Helpers;

use App\Models\Domain;

/**
 * Class Domain (Helper)
 *
 * This class exists to provide some utility for working with Domains.
 * My first intent is to create AWS SDK methods for adding and removing
 * domains from Route53 & CloudFront if env !== local.
 *
 * We'll extend from that use-case as we identify needs which don't cleanly
 * fit into a domain model or MVC pattern.
 */
class DomainHelper
{
    public static function inTenantContext(): bool
    {
        if (in_array(request()->getHost(), config('tenancy.central_domains'))) {
            return false;
        }

        return true;
    }

    public static function addDomain()
    {
        // TODO
    }

    public static function removeDomain($domain)
    {
        // TODO
    }

    public static function domainExists($domain)
    {
        // TODO
    }
}
