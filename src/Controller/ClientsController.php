<?php

namespace App\Controller;

use App\Entity\Client;
use App\Manager\ClientManager;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;

class ClientsController extends AbstractController
{


    /**
     * Cette méthode permet de récupérer l'ensemble des clients.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des clients",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"getBooks"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Clients")
     *
     * @Route("/api/clients", name="app_clients", methods={"GET"})
     */
    public function getClientsList(ClientRepository $clientRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $clientCache = "getClientsList-" . $page . "-" . $limit;

        $jsonClientsList = $cache->get($clientCache, function (ItemInterface $item) use ($clientRepository, $page, $limit, $serializer) {
            echo("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            $item->tag("clientsCache");
            $clientsList = $clientRepository->findAllClientsWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            return $serializer->serialize($clientsList, 'json', $context);
        });
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer les détails d'un client.
     * @OA\Tag(name="Clients")
     *
     * @Route("/api/clients/{id}", name="app_details_client", methods={"GET"})
     */
    public function getDetailsClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonClient = $serializer->serialize($client, 'json', $context);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Cette méthode permet de supprimer un client.
     * @OA\Tag(name="Clients")
     *
     * @Route("/api/clients/{id}", name="app_delete_client", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour supprimer un client")
     */
    public function deleteClient(Client $client, ClientManager $clientManager): JsonResponse
    {
        $connectedUser = $this->getUser();
        $admin = $connectedUser->getUserIdentifier();
        if($admin !== "adminBilemo@admin.fr") {
            throw $this->createAccessDeniedException();
        }

        $clientManager->deleteClient($client);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet d'ajouter un client.
     *
     * @OA\RequestBody(@Model(type=Client::class, groups={"createClient"}))
     * @OA\Tag(name="Clients")
     *
     * @Route("/api/clients", name="app_create_client", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour créer un client")
     */
    public function createClient(Request $request, SerializerInterface $serializer, ClientManager $clientManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $context = DeserializationContext::create()->setGroups(["createClient"]);
        $client = $serializer->deserialize($request->getContent(), Client::class, 'json', $context);

        $connectedUser = $this->getUser();
        $admin = $connectedUser->getUserIdentifier();
        if($admin !== "adminBilemo@admin.fr") {
            throw $this->createAccessDeniedException();
        }

        //Vérification des erreurs
        $errors = $validator->validate($client);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $clientManager->createClient($client);
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonClient = $serializer->serialize($client, 'json', $context);

        $location = $urlGenerator->generate('app_details_users', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonClient, Response::HTTP_CREATED, ["location" => $location], true);
    }

    /**
     * Cette méthode permet de mettre à jour un client.
     *
     * @OA\RequestBody(@Model(type=Client::class, groups={"updateClient"}))
     * @OA\Tag(name="Clients")
     *
     * @Route("/api/clients/{id}", name="app_update_client", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour mettre à jour un client")
     */
    public function updateClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, Client $currentClient, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $context = DeserializationContext::create()->setGroups(["updateClient"]);
        $updateClient = $serializer->deserialize($request->getContent(), Client::class, 'json', $context);

        $connectedUser = $this->getUser();
        $admin = $connectedUser->getUserIdentifier();
        if($admin !== "adminBilemo@admin.fr") {
            throw $this->createAccessDeniedException();
        }

        $currentClient->setName($updateClient->getName());
        $currentClient->setEmail($updateClient->getEmail());
        $currentClient->setPassword($updateClient->getPassword());

        // On verifie les erreurs
        $errors = $validator->validate($currentClient);
        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        // On vide le cache
        $cache->invalidateTags(["clientsCache"]);

        $entityManager->persist($updateClient);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
