<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Club;
use App\Entity\Membership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class JoinClubProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new \RuntimeException('User must be authenticated.');
        }

        $club = $data; 
        $existingMembership = $this->entityManager
            ->getRepository(Membership::class)
            ->findOneBy([
                'utilisateur' => $user,
                'club' => $club,
            ]);

        if ($existingMembership) {
            throw new ConflictHttpException('You are already a member of this club.');
        }

        $membership = new Membership();
        $membership->setUtilisateur($user);
        $membership->setClub($club);
        $membership->setRole('MEMBER');
        $membership->setCreatedAt(new \DateTimeImmutable());

        return $this->persistProcessor->process($membership, $operation, $uriVariables, $context);
    }
}