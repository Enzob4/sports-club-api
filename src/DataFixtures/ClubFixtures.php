<?php

namespace App\DataFixtures;

use App\Entity\Club;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClubFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $club = new Club();
            $club->setName("Club de Sport " . $i);
            
            $owner = $this->getReference(UserFixtures::USER_REFERENCE_PREFIX . $i, \App\Entity\User::class);
            $club->setOwner($owner); 

            $manager->persist($club);
        }

        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}