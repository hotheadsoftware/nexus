<?php

namespace App\Services;

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
class Domains
{
    public function addToNameserver()
    {
        // TODO - route53, cloudflare, etc. code here
    }

    public function removeFromNameserver($domain)
    {
        // TODO - route53 code here
    }

    public function existsInNameserver($domain)
    {
        // TODO - route53 code here
    }
}
