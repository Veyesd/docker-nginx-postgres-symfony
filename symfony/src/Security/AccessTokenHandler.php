<?php
// src/Security/AccessTokenHandler.php
namespace App\Security;

use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
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
        private TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $accessToken = $this->repository->findOneByValue($accessToken);
        if (null === $accessToken || !$accessToken->isValid()) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        $user = $this->em->getRepository(User::class)->findOneByToken($accessToken->getId());
        if (null === $user) {
            $this->em->remove($accessToken);
            $this->em->flush();
            throw new CustomUserMessageAuthenticationException('User must to reconnect');
        }
        $userIdentifier = $user->getEmail();

        return new UserBadge($userIdentifier);
    }
}