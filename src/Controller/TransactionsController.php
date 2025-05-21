<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\TransactionsRepository;

final class TransactionsController extends AbstractController
{
    #[Route('/api/transactions', name: 'get_transactions', methods: ['GET'])]
    public function getTransactionList(
        TransactionsRepository $transactionsRepository, 
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $transactionList = $transactionsRepository->findAll();
        $jsonTransactionList = $serializerInterface->serialize($transactionList, 'json');

        return new JsonResponse(
            $jsonTransactionList,
            Response::HTTP_OK,
            [],
            true
        );
    }
}