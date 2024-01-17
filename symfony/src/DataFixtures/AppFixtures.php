<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    private $ph;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->ph = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $group = $this->getGroups()[0];
        $email = $this->getGroups()[0].'@gmail.com';
        $userLastname = $this->getGroups()[0];
        $userFirstname = $this->getGroups()[0];

        $userPassword = 'begin123';

        $newUser = new User();
        $newUser->setEmail($email);
        $newUser->setPassword($userPassword);
        $hashedPassword = $this->ph->hashPassword(
            $newUser,
            $userPassword
        );
        $newUser->setPassword($hashedPassword);
        $newUser->setLastname($userLastname);
        $newUser->setFirstname($userFirstname);
        
        $roles = $newUser->getRoles();
        $roles[] = "ROLE_ADMIN";
        $newUser->setRoles($roles);

        $now = new DateTimeImmutable('now');
        $newUser->setCreationDate($now);

        $newUser->setIsActive(true);
        
        $manager->persist($newUser);
        $manager->flush();

    }
    
    public static function getGroups(): array
    {
        return ['default'];
    }
}
