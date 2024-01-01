<?php

namespace App\Helpers;

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
    public function __construct(protected \App\Models\Domain $domain)
    {
        //
    }

    public function addDomain()
    {
        // TODO
    }

    public function removeDomain($domain)
    {
        // TODO
    }

    public function domainExists($domain)
    {
        // TODO
    }
}
