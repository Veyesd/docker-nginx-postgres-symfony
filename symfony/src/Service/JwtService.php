<?php

namespace App\Service;

use App\Entity\AccessToken;
use App\Entity\User;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Namshi\JOSE\SimpleJWS;

class JwtService
{
    /**
     * Durée de validité des tokens lorsqu'ils sont controllés dans ce controlleur
     * @var int $tokenDurability
     */
    private $tokenDurability = 15;

    private $em;
    private $jws;
    private string $secretKey;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->secretKey = "youpi";
        $this->jws = new SimpleJWS([
            'alg' => 'HS256'
        ]);
    }

    public function generateToken(User $user): AccessToken
    {
        $data[] = [
            "email" => $user->getEmail(),
        ];
        $this->jws->setPayload($data);
        $this->jws->sign($this->secretKey);

        $accessToken = new AccessToken();
        $accessToken->setValue($this->jws->getTokenString());
        $accessToken->setLastUpdate(new DateTime('now'));
        $this->em->persist($accessToken);
        $user->setToken($accessToken);
        $this->em->flush();

        return  $accessToken;
    }

    public function validateToken(string $token): bool
    {
        $this->jws->load($token);
        return $this->jws->isValid($this->secretKey);
    }

    public function checkTokenValidityOrUpdate($user): AccessToken | null
    {
        $accessToken = $this->em->getRepository(AccessToken::class)->findOneByValue($user->getToken()->getValue());
        if (intval($accessToken->getLastUpdate()->sub(new DateInterval('PT60M'))->diff(new DateTime('now', new DateTimeZone('Europe/Paris')))->format('%i')) >= $this->tokenDurability)
        {
            $this->clearToken($accessToken);
            $accessToken = null;
        } else {
            $accessToken = $this->refreshToken($accessToken);
        }

        return $accessToken;
    }

    public function refreshToken(AccessToken $accessToken): AccessToken
    {
        if($accessToken !== null){
            $accessToken->setLastUpdate(new DateTime('now'));
            $this->em->flush();
        }

        return $accessToken;
    }

    public function clearToken(AccessToken $accessToken): null
    {
        if ($accessToken !== null)
        {
            $user = $this->em->getRepository(User::class)->findOneByToken($accessToken->getId());
            if ($user !== null) {
                $user->setToken(null);
                $this->em->remove($accessToken);
                $this->em->flush();
                return null;
            }     
        }
    }
}