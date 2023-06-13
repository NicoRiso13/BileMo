<?php

namespace App\Controller;


use App\Entity\Product;
use App\Repository\ProductRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductsController extends AbstractController
{
    /**
     * @Route("/api/products", name="app_products", methods={"GET"})
     * @throws InvalidArgumentException
     */
    public function getProductsList(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getProductsList-" . $page ."-" . $limit;

        $jsonProductsList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository,$page,$limit,$serializer){
            echo ("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            $item->tag("productsCache");
            $productsList = $productRepository->findAllProductsWithPagination($page,$limit);
            return $serializer->serialize($productsList,'json');
        });

        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/products/{id}", name="app_details_products", methods={"GET"})
     */
    public function getDetailsProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }

}
