<?php

namespace App\Services\Alloyal\Concerns;

Trait AlloyalClient
{
    public function __construct(
        protected ?string $base_url = null,
        protected ?string $business_id = null,
        protected ?string $token = null,
        protected ?string $email = null,
        protected ?string $password = null,
        protected array $data = [],
    ) {
        $this->environment = app()->isLocal() ? 'sandbox' : 'production';
        $this->base_url = config("alloyal.{$this->environment}.base_url");
        $this->business_id = config("alloyal.{$this->environment}.business_id");
        $this->token = config("alloyal.{$this->environment}.token");
        $this->email = config("alloyal.{$this->environment}.email");
        $this->password = config("alloyal.{$this->environment}.password");
    }
}
