<?php

namespace App\Controller\AuthController;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
        $parameters = json_decode($request->getContent(), true);

        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail($parameters['email']);
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

        if (!$user || !$user->getIsActive() || !$passwordHasher->isPasswordValid($user, $userPassword)) {
            return new Response(
                'Invalid credentials' ,
                500
            );
        }
        else {
            $accessToken = null;
            if ($user->getToken() !== null) {
                $accessToken = $this->jwts->checkTokenValidityOrUpdate($user);
            }
            if ($accessToken === null) {
                $accessToken = $this->jwts->generateToken($user);
            }

            return new JsonResponse(['token' => $accessToken->getValue()], 200);
        }
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        $accessToken = $this->getEntityManager()->getRepository(AccessToken::class)->findOneByValue($token);
        $user = $this->getEntityManager()->getRepository(User::class)->findOneByToken($accessToken);
        $user->setToken(null);
        $this->getEntityManager()->remove($accessToken);
        $this->getEntityManager()->flush();
        //TODO: redirect to login
        return new Response(
            'logout',
            200            
        );
    }
}
