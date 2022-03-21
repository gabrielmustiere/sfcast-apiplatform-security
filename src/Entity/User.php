<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid as UuidAlias;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email', 'username'])]
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            'security' => "is_granted('PUBLIC_ACCESS')",
            'validation_groups' => ['Default', self::USER_CREATE],
        ],
    ],
    itemOperations: [
        'get',
        'put' => [
            'security' => "is_granted('ROLE_USER') and object == user",
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN')",
        ],
    ],
    shortName: 'user',
)]
#[ApiFilter(PropertyFilter::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const USER_WRITE = 'user:write';
    public const USER_CREATE = 'user:create';
    public const USER_READ = 'user:read';
    public const ADMIN_READ = 'admin:read';
    public const ADMIN_WRITE = 'admin:write';
    public const OWNER_READ = 'owner:read';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[UuidAlias]
    private ?Uuid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups([self::USER_READ, self::USER_WRITE])]
    #[Email]
    #[NotBlank]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    #[Groups([self::USER_WRITE])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[Groups([self::USER_WRITE])]
    #[SerializedName('password')]
    #[NotBlank(groups: [self::USER_CREATE])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups([self::USER_READ, self::USER_WRITE, CheeseListing::CHEESE_ITEM_GET])]
    #[NotBlank]
    private string $username;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: CheeseListing::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $cheeseListings;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups([self::ADMIN_READ, self::USER_WRITE, self::OWNER_READ])]
    private ?string $phoneNumber;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->cheeseListings = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getCheeseListings(): Collection|array
    {
        return $this->cheeseListings;
    }

    #[Groups([self::USER_READ, self::USER_WRITE])]
    #[SerializedName('cheeseListings')]
    public function getPublishCheeseListing(): Collection|array
    {
        return $this->cheeseListings->filter(function (CheeseListing $cheeseListing) {
            return $cheeseListing->getIsPublished();
        });
    }

    public function addCheeseListing(CheeseListing $cheeseListing): self
    {
        if (!$this->cheeseListings->contains($cheeseListing)) {
            $this->cheeseListings[] = $cheeseListing;
            $cheeseListing->setOwner($this);
        }

        return $this;
    }

    public function removeCheeseListing(CheeseListing $cheeseListing): self
    {
        if ($this->cheeseListings->removeElement($cheeseListing)) {
            if ($cheeseListing->getOwner() === $this) {
                $cheeseListing->setOwner(null);
            }
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
