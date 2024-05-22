<?php

namespace App\Controller\AuthController;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Service\JwtService;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * Durée de validité des tokens lorsqu'ils sont controllés dans ce controlleur
     * @var int $tokenDurability
     */
    private $tokenDurability = 15;

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(UserPasswordHasherInterface $passwordHasher, Request $request, JwtService $jwts): Response
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
                $accessToken = $this->refreshToken($this->getEntityManager(), $user);
            }
            if ($accessToken === null) {
                $accessToken = new AccessToken();
                $accessToken->setValue($jwts->generateToken($user));
                $accessToken->setLastUpdate(new DateTime('now'));
                $this->getEntityManager()->persist($accessToken);
            }
            $roles = $user->getRoles();
            $roles[] = 'ROLE_CONNECTED';
            $user->setRoles($roles);
            $user->setToken($accessToken);
            $this->getEntityManager()->flush();

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
        $roles = $user->getRoles();
        unset($roles[array_search("ROLE_CONNECTED", $user->getRoles())]);
        $user->setRoles($roles);

        $this->getEntityManager()->remove($accessToken);
        $this->getEntityManager()->flush();
        //TODO: redirect to login
        return new Response(
            'logout',
            200            
        );
    }

    public function refreshToken(User $user)
    {
        $accessToken = $this->clearTokenIfOutdated($this->getEntityManager(), $user);
        if($accessToken !== null){
            $accessToken->setLastUpdate(new DateTime('now'));
            $this->getEntityManager()->flush();
        }

        return $accessToken;
    }

    public function clearTokenIfOutdated(User $user)
    {
        $accessToken = $this->getEntityManager()->getRepository(AccessToken::class)->findOneByValue($user->getToken()->getValue());
        if (intval($accessToken->getLastUpdate()->sub(new DateInterval('PT60M'))->diff(new DateTime('now', new DateTimeZone('Europe/Paris')))->format('%i')) >= $this->tokenDurability)
        {
            $user->setToken(null);
            $this->getEntityManager()->remove($accessToken);
            $this->getEntityManager()->flush();
            return null;
        } else {
            return $accessToken;
        }
    }
}
