<?php

namespace App\Controller\ApiController;

use App\Entity\User;
use App\Service\ApiFormaterResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/all', name: 'get_all_user', methods: ['GET'])]
    public function getAllUsers(EntityManagerInterface $entityManager, ApiFormaterResponseService $afrs): Response
    {
        return $afrs->response(
            'users',
            $entityManager->getRepository(User::class)->findAll()
        );
    }

    #[Route('/user/{id}', name: 'get_user_by_id', methods: ['GET'], condition: "params['id']")]
    public function getUserById(EntityManagerInterface $entityManager, ApiFormaterResponseService $afrs, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneById($id);

        if(!$user){
            return new Response('user not found');
        }
        else {
            return $afrs->response(
                'user',
                $user
            );
        }
    }

    #[Route('/user/{id}/roles', name: 'get_user_roles', methods: ['GET'], condition: "params['id']")]
    public function getRolesFromUdId(EntityManagerInterface $entityManager, ApiFormaterResponseService $afrs, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneById($id);

        if(!$user){
            return new Response('user not found');
        }
        else {
            return $afrs->response(
                'roles',
                $user->getRoles()
            );
        }
    }

    public function toogleUser(User $user)
    {
        $user->setIsActive(!$user->getIsActive());
    }
}
