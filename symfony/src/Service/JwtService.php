<?php

namespace App\Service;

use App\Entity\User;
use Namshi\JOSE\SimpleJWS;

class JwtService
{
    private $jws;
    private string $secretKey;

    public function __construct(string $secretKey = "youpi")
    {
        $this->secretKey = $secretKey;
        $this->jws = new SimpleJWS([
            'alg' => 'HS256'
        ]);
    }

    public function generateToken(User $user): string
    {
        $data[] = [
            "email" => $user->getEmail(),
        ];
        $this->jws->setPayload($data);
        $this->jws->sign($this->secretKey);

        return  $this->jws->getTokenString();

    }

    public function validateToken(string $token): bool
    {
        $this->jws->load($token);
        return $this->jws->isValid($this->secretKey);
    }
}