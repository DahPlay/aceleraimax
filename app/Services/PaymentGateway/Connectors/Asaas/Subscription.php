<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway\Connectors\Asaas;

use App\Services\PaymentGateway\Connectors\Asaas\Concerns\HasFilter;
use App\Services\PaymentGateway\Contracts\AdapterInterface;
use App\Services\PaymentGateway\Contracts\SubscriptionInterface;

class Subscription implements SubscriptionInterface
{
    use HasFilter;

    public function __construct(
        public AdapterInterface $http,
    ) {}

    public function list(array $filters = []): array
    {
        return $this->http->get('/subscriptions' . $this->filter($filters));
    }

    public function create(array $data): array
    {
        return $this->http->post('/subscriptions', $data);
    }

    public function update(int|string $id, array $data): array
    {
        return $this->http->put('/subscriptions/' . $id, $data);
    }

    public function delete(int|string $id): array
    {
        return $this->http->delete('/subscriptions/' . $id);
    }
}
