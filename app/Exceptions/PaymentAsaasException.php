<?php

namespace App\Exceptions;

use Exception;

class PaymentAsaasException extends Exception
{
    protected string $userMessage;

    public function __construct(string $message, string $userMessage = null)
    {
        parent::__construct($message);
        $this->userMessage = $userMessage ?? 'Erro ao processar o pagamento.';
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
