<?php

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route(
        path: '/login',
        name: 'app_login',
        methods: ['POST']
    )]
    public function login(IriConverterInterface $iriConverter): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json([
                'error' => "Requete d'authentification invalide",
            ], 400);
        }

        return new Response(null, 204, [
            'Location' => $iriConverter->getIriFromItem($this->getUser()),
        ]);
    }

    #[Route(
        path: '/logout',
        name: 'app_logout',
        methods: ['GET']
    )]
    public function logout()
    {
    }
}
