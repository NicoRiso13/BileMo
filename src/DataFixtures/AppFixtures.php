<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->userPasswordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $mobile = new Product();
            $mobile->setBrand('Marque' . $i);
            $mobile->setName('Téléphone' . $i);
            $mobile->setPicture('Picture' . $i . '.jpg');
            $mobile->setCreatedAt(new \DateTimeImmutable('now'));
            $mobile->setDescription('descritpion mobile' . $i);
            $manager->persist($mobile);
        }
        $manager->flush();


        //Création de deux client "ADMIN"
        $client = new Client();
        $client->setName('Touygues telecom');
        $client->setEmail("Touygues Telecom@gmail.com");
        $client->setRoles(["ROLE_ADMIN"]);
        $client->setPassword($this->userPasswordHasher->hashPassword($client, "password"));
        $manager->persist($client);
        $listClient[] = $client;


        $client = new Client();
        $client->setName('Orage');
        $client->setEmail("Orage@gmail.com");
        $client->setRoles(["ROLE_ADMIN"]);
        $client->setPassword($this->userPasswordHasher->hashPassword($client, "password"));
        $manager->persist($client);
        $listClient[] = $client;

        $client = new Client();
        $client->setName('Admin');
        $client->setEmail("adminBilemo@admin.fr");
        $client->setRoles(["ROLE_ADMIN"]);
        $client->setPassword($this->userPasswordHasher->hashPassword($client, "password"));
        $manager->persist($client);
        $listClient[] = $client;

        $manager->flush();


        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setName('user' . $i);
            $user->setEmail('email' . $i . '@gmail.com');
            $user->setRoles(["ROLE_USER"]);
            $user->setClient($listClient[array_rand($listClient)]);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
            $manager->persist($user);
        }
        $manager->flush();
    }
}

