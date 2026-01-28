<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Entity\Club;
use App\Entity\Membership;
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

        $entityManager->getConnection()->executeStatement(
            'TRUNCATE "user", "club", "membership" RESTART IDENTITY CASCADE'
        );

        $owner = new User();
        $owner->setEmail('owner2@test.com');
        $owner->setPassword($container->get('security.user_password_hasher')->hashPassword($owner, 'password'));
        $owner->setRoles(['ROLE_USER']);
        $entityManager->persist($owner);

        $member = new User();
        $member->setEmail('member@test.com');
        $member->setPassword($container->get('security.user_password_hasher')->hashPassword($member, 'password'));
        $member->setRoles(['ROLE_USER']);
        $entityManager->persist($member);

        $entityManager->flush();

        $club = new Club();
        $club->setName('Join Club');
        $club->setDescription('Join Test');
        $club->setOwner($owner);
        $entityManager->persist($club);
        
        $entityManager->flush();

        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'member@test.com',
                'password' => 'password'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $token = $response->toArray()['token'];

        $client->request('POST', '/api/clubs/'.$club->getId().'/join', [
            'auth_bearer' => $token
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);


        $entityManager->clear(); 
        $membership = $entityManager->getRepository(Membership::class)->findOneBy([
            'utilisateur' => $member,
            'club' => $club
        ]);
        
        $this->assertNotNull($membership, 'Le membership n\'a pas été trouvé en base.');
        $this->assertEquals('MEMBER', $membership->getRole());

        $client->request('POST', '/api/clubs/'.$club->getId().'/join', [
            'auth_bearer' => $token
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }
}