<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Club;
use App\Entity\Membership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ClubProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Club && $operation instanceof Post) {
            $user = $this->security->getUser();

            if (!$user) {
                throw new \RuntimeException('User must be authenticated.');
            }

            $data->setOwner($user);

            $membership = new Membership();
            $membership->setUtilisateur($user);
            $membership->setClub($data);
            $membership->setRole('OWNER');
            $membership->setCreatedAt(new \DateTimeImmutable());

            $data->addMembership($membership);

            $this->entityManager->persist($membership);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
