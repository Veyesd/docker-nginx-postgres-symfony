<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    private $em;
    
    public function __construct(
        EntityManagerInterface $em,
        private AccessTokenRepository $repository,
        private UserRepository $userRepository,
        private TokenStorageInterface $tokenStorage,
        private JwtService $jwts
    ) {
        $this->jwts = $jwts;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $accessToken = $this->repository->findOneByValue($accessToken);
        if (null === $accessToken) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        $user = $this->em->getRepository(User::class)->findOneByToken($accessToken->getId());
        $accessToken = $this->jwts->checkTokenValidityOrUpdate($user);

        if ($user === null  || $accessToken === null) {
            $this->em->remove($accessToken);
            $this->em->flush();
            throw new CustomUserMessageAuthenticationException('User must to reconnect');
        }

        return new UserBadge($user->getEmail());
    }
}