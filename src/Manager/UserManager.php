<?php

namespace App\Manager;

use App\Entity\Client;
use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;
    private TokenStorageInterface $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->tokenStorage = $tokenStorage;
    }

    public function createUser(User $user): void
    {
        $token = $this->tokenStorage->getToken();
        if($token !== null) {
            $client = $token->getUser();
            if($client instanceof Client) {
                $user->setClient($client);
            }
        }
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
