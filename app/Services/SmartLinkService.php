<?php

namespace App\Services;

use App\Models\User;
use App\Services\Alloyal\User\UserCreateSmartLink;
use Illuminate\Support\Facades\Log;

class SmartLinkService
{
    public function createForUser(int $userId): array
    {
        $user = User::with('customer')->findOrFail($userId);
        $customer = $user->customer;

        $cpf = $customer->document;
        $alloyalResponse = (new UserCreateSmartLink())->handle($cpf);

        if (isset($alloyalResponse['errors'])) {
            return ['errors' => $alloyalResponse['errors']];
        }

        $customer->update(['web_smart_link' => $alloyalResponse['web_smart_link']]);

        Log::channel('alloyal')->info("SmartLink adicionado ao customer com sucesso", [
            'user' => $user->name,
            'customer' => $customer->name,
            'web_smart_link' => $alloyalResponse['web_smart_link'],
        ]);

        return ['web_smart_link' => $alloyalResponse['web_smart_link']];
    }
}
