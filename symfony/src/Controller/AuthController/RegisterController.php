<?php

namespace App\Controller\AuthController;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'create_user', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $parameters = json_decode($request->getContent(), true);

        $userEmail = $parameters['email'];
        $userPassword = $parameters['password'];
        $admin = $parameters['admin'] ?? false;

        $newUser = new User();
        $newUser->setEmail($userEmail);
        $newUser->setPassword($userPassword);
        $hashedPassword = $passwordHasher->hashPassword(
            $newUser,
            $userPassword
        );
        $newUser->setPassword($hashedPassword);
        $userFirstname = $parameters['firstname'];
        $userLastname = $parameters['lastname'];
        $newUser->setLastname($userLastname);
        $newUser->setFirstname($userFirstname);
        
        $roles = [];
        if ($admin) {
            $roles = $newUser->getRoles();
            $roles[] = "ROLE_ADMIN";
        }
        else {
            $roles = $newUser->getRoles();
        }
        $newUser->setRoles($roles);

        $now = new DateTimeImmutable('now');
        $newUser->setCreationDate($now);

        $newUser->setIsActive(false);
        // TODO validator par email pour aciver le client

        $this->getEntityManager()->persist($newUser);
        $this->getEntityManager()->flush();

        return new Response(
            'New user register!',
            200            
        );
    }
}
