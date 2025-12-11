<?php

namespace App\Services\AppIntegration;

use App\Models\Customer;
use App\Services\YouCast\Customer\CustomerAuthenticate;
use App\Services\YouCast\Customer\CustomerSearch;
use App\Services\YouCast\Customer\CustomerUpdate;

class CustomerService
{
    protected $customerSearch;
    protected $customerUpdate;
    protected $customerAuthenticate;

    public function __construct(
        CustomerSearch $customerSearch,
        CustomerUpdate $customerUpdate,
        CustomerAuthenticate $customerAuthenticate
    ) {
        $this->customerSearch = $customerSearch;
        $this->customerUpdate = $customerUpdate;
        $this->customerAuthenticate = $customerAuthenticate;
    }

    public function findExternalCustomerByLogin(string $login, string $password = null): ?array
    {
        $response = $this->customerSearch->handle($login);

        if (isset($response['error'])) {
            return null;
        }

        $customerData = $response['response'] ?? null;

        if (!$customerData) {
            return null;
        }

        $customerId = array_key_first($customerData);

        if (!$customerId || !isset($customerData[$customerId])) {
            return null;
        }

        $customerDetails = $customerData[$customerId];

        $viewers_id = $customerDetails['viewers_id'] ?? null;
        $name = $customerDetails['viewers_firstname'] ?? null;
        $email = $customerDetails['contacts'][0]['viewers_contact_content'] ?? null;
        $login = $customerDetails['devices'][0]['device_motv_login'] ?? null;

        return [
            'customer' => [
                'source' => '',
                'viewers_id' => $viewers_id,
                'name' => $name,
                'email' => $email,
                'login' => $login,
                'password' => $password,
            ],
        ];
    }

    public function getExternalDataByLogin(string $login): ?array
    {
        return $this->findExternalCustomerByLogin($login);
    }

    public function updateFromApp(Customer $customer, ?string $password = null): array
    {
        return $this->customerUpdate->handle($customer, $password);
    }

    public function authenticateExternalCustomer(string $login, string $password = null): array
    {
        $response = $this->customerAuthenticate->handle($login, $password);

        return $response;
    }
}
