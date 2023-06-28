<?php

namespace App\Manager;

use App\Entity\Client;
use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function createUser(User $user): void
    {
        $password = $user->getPassword();
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "$password"));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

}
