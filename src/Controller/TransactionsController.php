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

use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use App\Repository\TransactionsRepository;

use App\DTO\Category\CategoryDTO;
use App\DTO\Transactions\TransactionDTO;
use App\DTO\Transactions\TransactionInputDTO;

final class TransactionsController extends AbstractController
{
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
            $transaction->getDate()->format(\DateTime::ATOM),
            new CategoryDTO(
                $category->getId(), 
                $category->getName()
            )
        );

        $json = $serializerInterface->serialize($responseDto, 'json');
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }
}