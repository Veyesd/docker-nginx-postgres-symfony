<?php

namespace App\Controller\AuthController;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Service\JwtService;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Request $request, JwtService $jwts): Response
    {
        $parameters = json_decode($request->getContent(), true);

        $user = $entityManager->getRepository(User::class)->findOneByEmail($parameters['email']);
        if ($user === null) {
            return new Response(
                'User not found' ,
                404
            );
        } elseif ($user->getIsActive() === false) {
            return new Response(
                'Email must be validated' ,
                401
            );
        }

        $userPassword = $parameters['password'];
        $newUser = new User();
        $newUser->setEmail($parameters['email']);
        $newUser->setPassword($userPassword);

        if(!$user || !$user->getIsActive() || !$passwordHasher->isPasswordValid($user, $userPassword)){
            return new Response(
                'Invalid credentials' ,
                500
            );
        }
        else {
            if ($user->getToken() !== null) {
                $accessToken = $this->refreshToken($entityManager, $user);
            } else {
                $accessToken = new AccessToken();
                $accessToken->setValue($jwts->generateToken($user));
                $accessToken->setLastUpdate(new DateTime('now'));
                $entityManager->persist($accessToken);
            }
            $roles = $user->getRoles();
            $roles[] = 'ROLE_CONNECTED';
            $user->setRoles($roles);
            $user->setToken($accessToken);
            // $this->createToken(new Passport(new UserCustomBadge($userIdentifier), $credentials), 'api');
            $entityManager->flush();

            return new JsonResponse(['token' => $accessToken->getValue()], 200);
        }
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        $accessToken = $entityManager->getRepository(AccessToken::class)->findOneByValue($token);
        $user = $entityManager->getRepository(User::class)->findOneByToken($accessToken);
        $user->setToken(null);
        $roles = $user->getRoles();
        unset($roles[array_search("ROLE_CONNECTED", $user->getRoles())]);
        $user->setRoles($roles);

        $entityManager->remove($accessToken);
        $entityManager->flush();
        //TODO: redirect to login
        return new Response(
            'logout',
            200            
        );
    }

    public function refreshToken(EntityManagerInterface $entityManager, User $user)
    {
        $accessToken = $this->clearTokenIfOutdated($entityManager, $user);
        $accessToken = $entityManager->getRepository(AccessToken::class)->findOneByValue($accessToken->getValue());
        $accessToken->setLastUpdate(new DateTime('now'));
        $entityManager->flush();

        return $accessToken;
    }

    public function clearTokenIfOutdated(EntityManagerInterface $entityManager, User $user)
    {
        $accessToken = $entityManager->getRepository(AccessToken::class)->findOneByValue($user->getToken()->getValue());
        if (intval($accessToken->getLastUpdate()->sub(new DateInterval('PT60M'))->diff(new DateTime('now', new DateTimeZone('Europe/Paris')))->format('%i')) >= 15)
        {
            $user->setToken(null);
            $entityManager->remove($accessToken);
            $entityManager->flush();
            return null;
        } else {
            return $accessToken;
        }
    }
}
