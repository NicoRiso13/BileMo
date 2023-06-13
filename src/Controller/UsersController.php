<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UsersController extends AbstractController
{



    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->userPasswordHasher = $passwordHasher;

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
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
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

        $userCache = "getUsersList-" . $page . "-" . $limit;

        $jsonUsersList = $cache->get($userCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
            echo("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            $item->tag("usersCache");
            $usersList = $userRepository->findAllUsersWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            return $serializer->serialize($usersList, 'json', $context);
        });

        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/users/{id}", name="app_details_users", methods={"GET"})
     */
    public function getDetailsUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * @Route("/api/users/{id}", name="app_delete_user", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour supprimer un utilisateur")
     * @throws InvalidArgumentException
     */
    public function deleteUser(User $user, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["productsCache"]);
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/users", name="app_create_user", methods={"POST"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour créer un utilisateur")
     */
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository, ValidatorInterface $validator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        //Vérification des erreurs
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $password = $request->get('password');
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "$password"));
        $entityManager->persist($user);
        $entityManager->flush();

        $content = $request->toArray();
        $idClient = $content['idClient'] ?? -1;

        $user->setClient($clientRepository->find($idClient));
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('app_details_users', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    }

    /**
     * @Route("/api/users/{id}", name="app_update_user", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits suffisants pour mettre à jour un utilisateur")
     */
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ClientRepository $clientRepository, User $currentUser, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {

        $updateUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        $currentUser->setName($updateUser->getName());
        $currentUser->setEmail($updateUser->getEmail());
        $currentUser->setPassword($updateUser->getPassword());

        // On verifie les erreurs
        $errors = $validator->validate($currentUser);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST,[], true);
        }


        $content = $request->toArray();
        $idClient = $content['idClient'] ?? -1;

        $updateUser->setClient($clientRepository->find($idClient));


        $entityManager->flush();

        // On vide le cache
        $cache->invalidateTags(["usersCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
