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
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\User;

use App\Repository\UserRepository;

final class UserController extends AbstractController
{
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(
        UserRepository $userRepository, 
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializerInterface->serialize($userList, 'json');

        return new JsonResponse(
            $jsonUserList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/user/{id}', name: 'get_userById', methods: ['GET'])]
    public function getCategoryById(
        int $id, 
        UserRepository $userRepository, 
        SerializerInterface $serializerInterface
    ): JsonResponse {
        $user = $userRepository->find($id);
        if ($user) {
            $jsonUser = $serializerInterface->serialize($user, 'json');

            return new JsonResponse(
                $jsonUser,
                Response::HTTP_OK,
                [],
                true
            );
        } else {
            return new JsonResponse(
                ['error' => 'User not found'],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    #[Route('/api/user', name: 'create_user', methods: ['POST'])]
    public function createUser(
        ValidatorInterface $validator,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['password'])) {
            return new JsonResponse(['error' => 'Missing username or password'], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['username' => $data['username']])) {
            return new JsonResponse(['error' => 'Username already exists'], Response::HTTP_CONFLICT);
        }

        $violations = $validator->validate($data['username'], [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 3, 'max' => 255]),
        ]);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setRoles(['ROLE_USER']); // Optional

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $userRepository->add($user); // Add this method in the repo below

        return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
    }
}