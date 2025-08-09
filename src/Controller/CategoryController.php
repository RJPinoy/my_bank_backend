<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\CategoryRepository;

use App\DTO\Category\CategoryDTO;

final class CategoryController extends AbstractController
{
    #[Route('/api/categories', name: 'get_categories', methods: ['GET'])]
    public function getCategoryList(
        CategoryRepository $categoryRepository, 
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $categoryList = $categoryRepository->findAll();

        $categoryDtos = array_map(function ($category) {
            return new CategoryDTO(
                $category->getId(),
                $category->getName()
            );
        }, $categoryList);

        $jsonCategoryList = $serializerInterface->serialize($categoryDtos, 'json');

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
            $categoryDto = new CategoryDTO(
                $category->getId(),
                $category->getName()
            );

            $jsonCategory = $serializerInterface->serialize($categoryDto, 'json');

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