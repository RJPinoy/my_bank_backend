<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\User;

use App\Repository\UserRepository;
use App\Repository\TransactionsRepository;

use App\DTO\User\UserDTO;
use App\DTO\User\UserInputDTO;
use App\DTO\Category\CategoryDTO;
use App\DTO\Transactions\TransactionDTO;

final class UserController extends AbstractController
{
    // #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    // public function getUsers(
    //     UserRepository $userRepository, 
    //     SerializerInterface $serializerInterface
    // ): JsonResponse {
    //     $userList = $userRepository->findAll();

    //     $userDtos = array_map(function ($user) {
    //         return new UserDTO(
    //             $user->getId(),
    //             $user->getUsername()
    //         );
    //     }, $userList);

    //     $jsonUserList = $serializerInterface->serialize($userDtos, 'json');

    //     return new JsonResponse(
    //         $jsonUserList,
    //         Response::HTTP_OK,
    //         [],
    //         true
    //     );
    // }

    #[Route('/api/user', name: 'get_user', methods: ['GET'])]
    public function getUserById(
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $user = $this->getUser();
        if ($user) {
            $userDto = new UserDTO(
                $user->getId(),
                $user->getUsername()
            );

            $jsonUser = $serializerInterface->serialize($userDto, 'json');

            return new JsonResponse(
                $jsonUser,
                Response::HTTP_OK,
                [],
                true
            );
        } else {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
    }
    
    #[Route('/api/user/transactions', name: 'get_user_transactions', methods: ['GET'])]
    public function getUserTransactions(
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
                $transaction->getDate()->format(\DateTime::ATOM),
                new CategoryDTO(
                    $transaction->getCategory()->getId(),
                    $transaction->getCategory()->getName()
                ),
            );
        }, $transactions);

        $json = $serializerInterface->serialize($transactionDtos, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/user', name: 'create_user', methods: ['POST'])]
    public function createUser(
        ValidatorInterface $validator,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $userInputDto = $serializerInterface->deserialize($request->getContent(), UserInputDTO::class, 'json');

        $violations = $validator->validate($userInputDto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($userInputDto->username);
        $hashedPassword = $passwordHasher->hashPassword($user, $userInputDto->password);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $userRepository->add($user);

        $userDto = new UserDTO(
            $user->getId(),
            $user->getUsername()
        );

        $jsonUser = $serializerInterface->serialize($userDto, 'json');

        return new JsonResponse(
            $jsonUser,
            Response::HTTP_CREATED,
            [],
            true
        );
    }
}