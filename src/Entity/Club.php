<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use App\State\ClubProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\Link;
use App\State\JoinClubProcessor; 

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['club:read']],
    denormalizationContext: ['groups' => ['club:write']],
    processor: ClubProcessor::class,
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_USER')"
        ),
        new Put(security: "object.getOwner() == user or is_granted('ROLE_ADMIN')"
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            uriTemplate: '/clubs/{id}/join',
            security: "is_granted('ROLE_USER')",
            processor: JoinClubProcessor::class,
            name: 'join_club',
            input: false, 
            output: Membership::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                summary: 'Join a club by its ID.',
            )
        ),
    ]
)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Groups(['club:read', 'club:write'])]
    private string $name;


    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $description = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['club:read'])]
    private User $owner;

    /**
     * @var Collection<int, Membership>
     */
    #[ORM\OneToMany(targetEntity: Membership::class, mappedBy: 'club')]
    private Collection $memberships;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->memberships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Membership>
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMembership(Membership $membership): static
    {
        if (!$this->memberships->contains($membership)) {
            $this->memberships->add($membership);
            $membership->setClub($this);
        }

        return $this;
    }

    public function removeMembership(Membership $membership): static
    {
        if ($this->memberships->removeElement($membership)) {
            // set the owning side to null (unless already changed)
            if ($membership->getClub() === $this) {
                $membership->setClub(null);
            }
        }

        return $this;
    }
}
