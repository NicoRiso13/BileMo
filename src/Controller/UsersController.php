<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Manager\UserManager;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UsersController extends AbstractController
{


    private UserPasswordHasherInterface $userPasswordHasher;
    private TokenStorageInterface $tokenStorage;

    public function __construct(UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage)
    {
        $this->userPasswordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des Utilisateurs",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="clientId",
     *     in="query",
     *     description="La liste des urilisateurs en fonction du client",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Users")
     *
     * @Route("/api/users", name="app_users", methods={"GET"})
     */
    public function getUsersList(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $clientId = $request->get('clientId');

        $userCache = "getUsersList-" . $page . "-" . $limit . "-" . $clientId;

        $jsonUsersList = $cache->get($userCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer, $clientId) {
            echo("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            $item->tag("usersCache");
            $usersList = $userRepository->findAllUsersWithPaginationAndClient($page, $limit, $clientId);
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            return $serializer->serialize($usersList, 'json', $context);
        });

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    /**
     *  Cette méthode permet de voir les details d'un utilisateur.
     *
     * @OA\RequestBody(@Model(type=User::class))
     *
     * @OA\Tag(name="Users")
     * @Route("/api/users/{id}", name="app_details_users", methods={"GET"})
     */
    public function getDetailsUser(User $user, SerializerInterface $serializer): JsonResponse
    {

        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur.
     *
     * @OA\RequestBody(@Model(type=User::class))
     * @OA\Tag(name="Users")
     *
     * @Route("/api/users/{id}", name="app_delete_user", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour supprimer un utilisateur")
     * @throws InvalidArgumentException
     */
    public function deleteUser(User $user, TagAwareCacheInterface $cache, UserManager $userManager): JsonResponse
    {
        $connectedUser = $this->getUser();
        if(!$connectedUser instanceof Client) {
            throw $this->createAccessDeniedException();
        }
        if($user->getClient()->getId() !== $connectedUser->getId()) {
            throw $this->createAccessDeniedException();
        }
        $cache->invalidateTags(["productsCache"]);
        $userManager->deleteUser($user);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet d'ajouter un utilisateur.
     *
     * @OA\RequestBody(@Model(type=User::class, groups={"createUser"}))
     * @OA\Tag(name="Users")
     *
     * @Route("/api/users", name="app_create_user", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour créer un utilisateur")
     */
    public function createUser(Request $request, SerializerInterface $serializer, UserManager $userManager, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository, ValidatorInterface $validator): JsonResponse
    {
        $context = DeserializationContext::create()->setGroups(["createUser"]);
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', $context);

        //Vérification des erreurs
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $userManager->createUser($user);
        $content = $request->toArray();
        $idClient = $content['idClient'] ?? -1;

        $user->setClient($clientRepository->find($idClient));
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('app_details_users', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    }

    /**
     * Cette méthode permet de mettre à jour un utilisateur.
     *
     * @OA\RequestBody(@Model(type=User::class, groups={"updateUser"}))
     * @OA\Tag(name="Users")
     *
     * @Route("/api/users/{id}", name="app_update_user", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour mettre à jour un utilisateur")
     */
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, User $currentUser, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {


        $context = DeserializationContext::create()->setGroups(["updateUser"]);
        $updateUser = $serializer->deserialize($request->getContent(), User::class, 'json', $context);

        $token = $this->tokenStorage->getToken();
        if($token !== null) {
            $client = $token->getUser();
            if($client instanceof Client) {
                $updateUser->setClient($client);
            }
        }
        $password = $updateUser->getPassword();
        $currentUser->setName($updateUser->getName());
        $currentUser->setEmail($updateUser->getEmail());
        $currentUser->setPassword($this->userPasswordHasher->hashPassword($updateUser, "$password"));

        // On verifie les erreurs
        $errors = $validator->validate($currentUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($currentUser);
        $entityManager->flush();
        // On vide le cache
        $cache->invalidateTags(["usersCache"]);

        $context = SerializationContext::create()->setGroups(["getUsers"]);

        $location = $urlGenerator->generate('app_details_users', ['id' => $currentUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonUpdateUser = $serializer->serialize($currentUser, 'json', $context);

        return new JsonResponse($jsonUpdateUser, Response::HTTP_OK, ["location" => $location], true);
    }
}
