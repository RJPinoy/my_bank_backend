<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This method is intentionally left blank. The security layer will intercept
        // the request and handle authentication.
        return new JsonResponse(['message' => 'Login endpoint']);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // This method is intentionally left blank. The security layer will intercept
        // the request and handle logout.
        return new JsonResponse(['message' => 'Logout endpoint']);
    }
}