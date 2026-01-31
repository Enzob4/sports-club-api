<?php

namespace App\Controller;

use App\Repository\MembershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MeController extends AbstractController
{
        public function __construct(
        private Security $security
    ) {}

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Maintenant PHP sait que $user possède la méthode getId()
        return new JsonResponse([
            '@id' => '/api/users/' . $user->getId(),
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/me/clubs', name: 'api_me_clubs', methods: ['GET'])]
    public function getMyClubs(MembershipRepository $membershipRepository): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $memberships = $membershipRepository->findBy(['utilisateur' => $user]);

        $data = array_map(function ($membership) {
            $club = $membership->getClub();
            return [
                '@id' => '/api/clubs/' . $club->getId(), 
                'id' => $club->getId(),
                'name' => $club->getName(),
                'description' => $club->getDescription(),
                'userRole' => $membership->getRole(),
            ];
        }, $memberships);

        return new JsonResponse($data);
    }
}