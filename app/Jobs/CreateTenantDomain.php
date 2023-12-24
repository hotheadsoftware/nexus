<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Events\DomainCreated;


class CreateTenantDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TenantWithDatabase $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        if(!$this->tenant->name) {
            throw new Exception('Tenant name is required');
        }

        $sub_domain = str_replace('-', '', Str::slug($this->tenant->name));
        $top_domain = parse_url(config('app.url'), PHP_URL_HOST);

        $domain = $this->tenant->domains()->create([
            'domain' => $sub_domain . '.' . $top_domain,
        ]);

        event(new DomainCreated($domain));
    }
}
