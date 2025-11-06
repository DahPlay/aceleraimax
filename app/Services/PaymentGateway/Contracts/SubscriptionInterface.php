<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway\Contracts;

interface SubscriptionInterface
{

    public function list(array $filters = []): array;

    public function create(array $data): array;

    public function update(int|string $id, array $data): array;

    public function delete(int|string $id): array;

    public function updateCreditCard(int|string $subscriptionId, string $creditCardToken, string $customerIp): array;
}
