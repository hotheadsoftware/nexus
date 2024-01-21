<?php

use App\Services\Domains;

beforeEach(function () {
    $this->domains = new Domains();
    $this->domain = 'example.com';
});

it('can add a domain to the dns provider', function () {
    $this->domains->addToNameserver($this->domain);
})->expectNotToPerformAssertions();

it('can remove a domain from the dns provider', function () {
    $this->domains->removeFromNameserver($this->domain);
})->expectNotToPerformAssertions();

it('can check to see if a domain exists in the dns provider', function () {
    $this->domains->existsInNameserver($this->domain);
})->expectNotToPerformAssertions();
