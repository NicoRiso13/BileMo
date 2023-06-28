<?php

namespace App\Manager;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function createClient(Client $client)
    {
        $password = $client->getPassword();
        $client->setRoles(["ROLE_USER"]);
        $client->setPassword($this->userPasswordHasher->hashPassword($client, "$password"));
        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    public function updateClient(Client $client, Request $request, SerializerInterface $serializer)
    {
        $updateUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        $password = $client->getPassword();
        $updateUser->setName($client->getName());
        $updateUser->setEmail($client->getEmail());
        $updateUser->setPassword($this->userPasswordHasher->hashPassword($client, "$password"));

    }

    public function deleteClient(Client $client, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($client);
        $entityManager->flush();

    }

}
