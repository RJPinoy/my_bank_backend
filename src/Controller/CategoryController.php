<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\CategoryRepository;

final class CategoryController extends AbstractController
{
    #[Route('/api/categories', name: 'get_categories', methods: ['GET'])]
    public function getCategoryList(
        CategoryRepository $categoryRepository, 
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $categoryList = $categoryRepository->findAll();
        $jsonCategoryList = $serializerInterface->serialize($categoryList, 'json');

        return new JsonResponse(
            $jsonCategoryList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/category/{id}', name: 'get_categoryById', methods: ['GET'])]
    public function getCategoryById(
        int $id, 
        CategoryRepository $categoryRepository, 
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $category = $categoryRepository->find($id);
        if ($category) {
            $jsonCategory = $serializerInterface->serialize($category, 'json');

            return new JsonResponse(
                $jsonCategory,
                Response::HTTP_OK,
                [],
                true
            );
        } else {
            return new JsonResponse(
                ['error' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}