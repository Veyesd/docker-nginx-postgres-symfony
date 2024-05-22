<?php

namespace App\Controller\ApiController;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\ApiFormaterResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/all', name: 'get_all_user', methods: ['GET'])]
    public function getAllUsers(): Response
    {
        // pour simplifier les choses, nous avons un apiFormaterService
        return $this->getResponseFormater()->response(
            'users',
            // et on dérive l'abstractcontroller pour éviter les injections
            $this->getEntityManager()->getRepository(User::class)->findAll()
        );
    }

    #[Route('/user/{id}', name: 'get_user_by_id', methods: ['GET'], condition: "params['id']")]
    public function getUserById(int $id): Response
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneById($id);

        if(!$user){
            return new Response('user not found');
        }
        else {
            return $this->getResponseFormater()->response(
                'user',
                $user
            );
        }
    }

    #[Route('/user/{id}/roles', name: 'get_user_roles', methods: ['GET'], condition: "params['id']")]
    public function getRolesFromUdId(int $id): Response
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneById($id);

        if(!$user){
            return new Response('user not found');
        }
        else {
            return $this->getResponseFormater()->response(
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
