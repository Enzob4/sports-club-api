<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Club;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Post;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ClubProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Club && $operation instanceof Post) {
            $user = $this->security->getUser();

            if (!$user) {
                throw new \RuntimeException('User must be authenticated.');
            }

            $data->setOwner($user);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}