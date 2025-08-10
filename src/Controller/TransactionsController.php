<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\Transactions;

use App\Repository\CategoryRepository;
use App\Repository\TransactionsRepository;

use App\DTO\Category\CategoryDTO;
use App\DTO\Transactions\TransactionDTO;
use App\DTO\Transactions\TransactionInputDTO;

final class TransactionsController extends AbstractController
{
    #[Route('/api/transactions', name: 'get_transactions', methods: ['GET'])]
    public function getTransactions(
        TransactionsRepository $transactionsRepository,
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $transactions = $transactionsRepository->findBy(['user' => $user]);

        $transactionDtos = array_map(function ($transaction) {
            return new TransactionDTO(
                $transaction->getId(),
                $transaction->getName(),
                $transaction->getAmount(),
                $transaction->getDate()->format(\DateTime::RFC1123),
                new CategoryDTO(
                    $transaction->getCategory()->getId(),
                    $transaction->getCategory()->getName()
                ),
            );
        }, $transactions);

        $json = $serializerInterface->serialize($transactionDtos, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/transaction', name: 'create_transaction', methods: ['POST'])]
    public function createTransaction(
        Request $request,
        CategoryRepository $categoryRepository,
        TransactionsRepository $transactionsRepository,
        ValidatorInterface $validatorInterface,
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $transactionInputDto = $serializerInterface->deserialize($request->getContent(), TransactionInputDTO::class, 'json');

        $violations = $validatorInterface->validate($transactionInputDto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $category = $categoryRepository->find($transactionInputDto->category);
        if (!$category) {
            return new JsonResponse(['error' => 'Invalid category ID'], Response::HTTP_BAD_REQUEST);
        }

        $transaction = new Transactions();
        $transaction->setName($transactionInputDto->name);
        $transaction->setAmount($transactionInputDto->amount);
        $transaction->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $transaction->setUser($user);
        $transaction->setCategory($category);

        $transactionsRepository->add($transaction);

        $responseDto = new TransactionDTO(
            $transaction->getId(),
            $transaction->getName(),
            $transaction->getAmount(),
            $transaction->getDate()->format(\DateTime::RFC1123),
            new CategoryDTO(
                $category->getId(), 
                $category->getName()
            )
        );

        $json = $serializerInterface->serialize($responseDto, 'json');
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/transaction/{id}', name: 'delete_transaction', methods: ['DELETE'])]
    public function deleteTransaction(
        int $id,
        TransactionsRepository $transactionsRepository,
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $transaction = $transactionsRepository->find($id);
        // Check if the transaction exists and belongs to the user
        if (!$transaction) {
            return new JsonResponse(['error' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }
        // Check if the transaction belongs to the authenticated user
        if ($transaction->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $transactionsRepository->remove($transaction);

        $transactions = $transactionsRepository->findBy(['user' => $user]);

        $transactionDtos = array_map(function ($transaction) {
            return new TransactionDTO(
                $transaction->getId(),
                $transaction->getName(),
                $transaction->getAmount(),
                $transaction->getDate()->format(\DateTime::RFC1123),
                new CategoryDTO(
                    $transaction->getCategory()->getId(),
                    $transaction->getCategory()->getName()
                ),
            );
        }, $transactions);

        $json = $serializerInterface->serialize($transactionDtos, 'json');

        return new JsonResponse([
            'success' => "Transaction $id deleted successfully.",
            'transactions' => json_decode($json)
        ], Response::HTTP_OK);
    }

    #[Route('/api/transaction/{id}', name: 'update_transaction', methods: ['PUT'])]
    public function updateTransaction(
        int $id,
        Request $request,
        CategoryRepository $categoryRepository,
        TransactionsRepository $transactionsRepository,
        ValidatorInterface $validatorInterface,
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $transaction = $transactionsRepository->find($id);
        // Check if the transaction exists and belongs to the user
        if (!$transaction) {
            return new JsonResponse(['error' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }
        // Check if the transaction belongs to the authenticated user
        if ($transaction->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $transactionInputDto = $serializerInterface->deserialize($request->getContent(), TransactionInputDTO::class, 'json');

        $violations = $validatorInterface->validate($transactionInputDto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryRepository->find($transactionInputDto->category);
        if (!$category) {
            return new JsonResponse(['error' => 'Invalid category ID'], Response::HTTP_BAD_REQUEST);
        }

        $transaction->setName($transactionInputDto->name);
        $transaction->setAmount($transactionInputDto->amount);
        $transaction->setCategory($category);
        $transactionsRepository->add($transaction);

        $responseDto = new TransactionDTO(
            $transaction->getId(),
            $transaction->getName(),
            $transaction->getAmount(),
            $transaction->getDate()->format(\DateTime::RFC1123),
            new CategoryDTO(
                $category->getId(), 
                $category->getName()
            )
        );

        $json = $serializerInterface->serialize($responseDto, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}