<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Entity\Membership;
use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ClubTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testCreateClubCreatesOwnerMembership(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        $email = 'owner@test.com';
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $container->get('security.user_password_hasher')
                ->hashPassword($user, 'password')
        );
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => $email,
                'password' => 'password'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $token = $response->toArray()['token'] ?? null;
        $this->assertNotNull($token, 'Le token JWT n\'a pas été généré.');

        $client->request('POST', '/api/clubs', [
            'auth_bearer' => $token,
            'json' => [
                'name' => 'Test Club',
                'description' => 'Test Description'
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED); // 201

        $entityManager->clear();

        $membership = $entityManager
            ->getRepository(Membership::class)
            ->findOneBy([
                'utilisateur' => $user,
            ]);

        $this->assertNotNull($membership, 'Le membership automatique n\'a pas été créé.');
        $this->assertEquals('OWNER', $membership->getRole());
        $this->assertEquals('Test Club', $membership->getClub()->getName());
        $this->assertEquals($email, $membership->getUtilisateur()->getEmail());
    }
}