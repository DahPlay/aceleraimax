<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\YouCast\Customer\CustomerSearch;
use App\Services\YouCast\Customer\CustomerUpdate;
use Illuminate\Support\Facades\Log;

class UserObserver
{

    public function updated(User $user): void
    {
        if (!$user->customer) {
            Log::warning('UserObserver - usuário sem customer relacionado', ['user_id' => $user->id]);
            return;
        }

        $customerSearch = (new CustomerSearch)->handle($user->customer->login);

        if ($customerSearch["status"] === 1) {
            (new CustomerUpdate)->handle($user->customer);
        }

        Log::error('UserObserver - line 24:', $customerSearch);
    }
}
