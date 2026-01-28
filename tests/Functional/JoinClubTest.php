<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class JoinClubTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testUserCanJoinClubAndCannotJoinTwice(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        // --- 1. NETTOYAGE RADICAL (Anti-ForeignKey Violation) ---
        // On vide les tables et on remet les compteurs d'ID à zéro
        $entityManager->getConnection()->executeStatement(
            'TRUNCATE "user", "club", "membership" RESTART IDENTITY CASCADE'
        );

        // --- 2. CRÉATION DES DONNÉES DE TEST ---

        // Création de l'Owner (celui qui possède le club)
        $owner = new User();
        $owner->setEmail('owner2@test.com');
        $owner->setPassword($container->get('security.user_password_hasher')->hashPassword($owner, 'password'));
        $owner->setRoles(['ROLE_USER']);
        $entityManager->persist($owner);

        // Création du futur Membre (celui qui va rejoindre)
        $member = new User();
        $member->setEmail('member@test.com');
        $member->setPassword($container->get('security.user_password_hasher')->hashPassword($member, 'password'));
        $member->setRoles(['ROLE_USER']);
        $entityManager->persist($member);

        $entityManager->flush();

        // Création du club
        $club = new Club();
        $club->setName('Join Club');
        $club->setDescription('Join Test');
        $club->setOwner($owner);
        $entityManager->persist($club);

        $entityManager->flush();

        // --- 3. AUTHENTIFICATION DU MEMBRE ---
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'member@test.com',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $token = $response->toArray()['token'];

        // --- 4. PREMIER JOIN (DOIT CRÉER LE MEMBERSHIP) ---
        $client->request('POST', '/api/clubs/'.$club->getId().'/join', [
            'auth_bearer' => $token,
        ]);

        // Ton API renvoie 201 Created car elle crée une ressource Membership
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Vérification en base de données
        $entityManager->clear(); // Important pour forcer la relecture SQL
        $membership = $entityManager->getRepository(Membership::class)->findOneBy([
            'utilisateur' => $member,
            'club' => $club,
        ]);

        $this->assertNotNull($membership, 'Le membership n\'a pas été trouvé en base.');
        $this->assertEquals('MEMBER', $membership->getRole());

        // --- 5. DEUXIÈME JOIN (DOIT ÉCHOUER) ---
        $client->request('POST', '/api/clubs/'.$club->getId().'/join', [
            'auth_bearer' => $token,
        ]);

        // Ton contrôleur/processeur doit renvoyer 409 Conflict ici
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }
}
