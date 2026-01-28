<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ClubTest extends ApiTestCase
{
    /**
     * Supprime l'avertissement de dépréciation et assure que le Kernel est prêt.
     */
    protected static ?bool $alwaysBootKernel = true;

    public function testCreateClubCreatesOwnerMembership(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        // --- PRÉPARATION : Nettoyage pour éviter l'erreur "Unique violation" ---
        $email = 'owner@test.com';
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            // On supprime l'ancien utilisateur (et ses memberships par cascade)
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }

        // --- 1. CRÉATION DE L'UTILISATEUR ---
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $container->get('security.user_password_hasher')
                ->hashPassword($user, 'password')
        );
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        // --- 2. AUTHENTIFICATION ---
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => $email,
                'password' => 'password',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $token = $response->toArray()['token'] ?? null;
        $this->assertNotNull($token, 'Le token JWT n\'a pas été généré.');

        // --- 3. CRÉATION DU CLUB ---
        $client->request('POST', '/api/clubs', [
            'auth_bearer' => $token, // Syntaxe plus propre proposée par ApiTestCase
            'json' => [
                'name' => 'Test Club',
                'description' => 'Test Description',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED); // 201

        // --- 4. VÉRIFICATIONS EN BASE ---

        // On force Doctrine à vider son cache interne pour lire les vraies données
        $entityManager->clear();

        $membership = $entityManager
            ->getRepository(Membership::class)
            ->findOneBy([
                'utilisateur' => $user, // Vérifie bien que ta propriété s'appelle 'utilisateur'
            ]);

        $this->assertNotNull($membership, 'Le membership automatique n\'a pas été créé.');
        $this->assertEquals('OWNER', $membership->getRole());
        $this->assertEquals('Test Club', $membership->getClub()->getName());
        $this->assertEquals($email, $membership->getUtilisateur()->getEmail());
    }
}
