<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExternalApiController extends AbstractController
{
    /**
     * Cette méthode fait appel à la route https://github.com/NicoRiso13/BileMo.git
     * pour récupérer les données du projet
     *
     * Pour plus d'information sur le client http:
     * https://symfony.com/doc/current/http_client.html
     *
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     *
     * @Route("/api/external/getSfDoc", name="app_external_api", methods={"GET"})
     * @throws TransportExceptionInterface
     */
    public function getSymfonyDoc(HttpClientInterface $httpClient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://github.com/NicoRiso13/BileMo.git'
        );
        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }
}
