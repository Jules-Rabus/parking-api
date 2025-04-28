<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthChecksController extends AbstractController
{
    #[Route('/livez', name: 'app_health_livez', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return new JsonResponse(['status' => 'alive'], Response::HTTP_OK);
    }

    #[Route('/readyz', name: 'app_health_readyz', methods: ['GET'])]
    public function ready(Connection $connection): JsonResponse
    {
        try {
            $connection->executeQuery('SELECT 1');

            return new JsonResponse(['status' => 'ready'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Database unreachable'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
