<?php

namespace App\Security;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Security\Credentials\UserCredentials;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private $em;

    public function __construct(
        EntityManagerInterface $em,
    ){
        $this->em = $em;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $apiTokenValue = $request->headers->get('X-AUTH-TOKEN');
        if (null === $apiTokenValue) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $apiToken = $this->em->getRepository(AccessToken::class)->findOneByValue($apiTokenValue);
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('Token not found');
        }

        $user = $this->em->getRepository(User::class)->findOneByToken($apiToken->getId());
        if (null === $user) {
            $this->em->remove($apiToken);
            $this->em->flush();
            throw new CustomUserMessageAuthenticationException('User must to reconnect');
        }
        $userIdentifier = $user->getEmail();

        $credentials = new UserCredentials($user->getEmail(), $user->getPassword());
        // dd( new Passport(new UserCustomBadge($userIdentifier), $credentials));
        return new Passport(new UserCustomBadge($userIdentifier), $credentials);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}