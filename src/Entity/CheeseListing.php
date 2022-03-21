<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Doctrine\CheeseListingSetOwnerListener;
use App\Repository\CheeseListingRepository;
use App\Validator\IsValidOwner;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid as UuidConstraint;

#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            'security' => "is_granted('ROLE_USER')",
        ],
    ],
    itemOperations: [
        'get',
        'put' => [
            'security' => "is_granted('EDIT', object)",
            'security_message' => 'only the creator can edit a cheese listing',
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN')",
        ],
    ],
    shortName: 'cheese',
    attributes: [
        'pagination_items_per_page' => 10,
        'formats' => ['jsonld', 'json', 'html', 'csv' => 'text/csv'],
    ]
)]
#[ApiFilter(BooleanFilter::class, properties: ['isPublished'])]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => SearchFilterInterface::STRATEGY_PARTIAL,
    'description' => SearchFilterInterface::STRATEGY_PARTIAL,
    'owner' => SearchFilterInterface::STRATEGY_EXACT,
    'owner.username' => SearchFilterInterface::STRATEGY_PARTIAL,
])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(PropertyFilter::class)]
#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ORM\EntityListeners([CheeseListingSetOwnerListener::class])]
class CheeseListing
{
    public const CHEESE_WRITE = 'cheese:write';
    public const CHEESE_READ = 'cheese:read';
    public const CHEESE_ITEM_GET = 'cheese:item:get';
    public const CHEESE_ITEM_POST = 'cheese:item:post';
    public const CHEESE_COLLECTION_POST = 'cheese:collection:post';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[UuidConstraint]
    private ?Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[NotBlank]
    #[Length(min: 5, max: 50, maxMessage: 'Décrivé votre fromage en 50 caractères ou moins')]
    #[ApiProperty(description: 'la description courte de mon fromage')]
    #[Groups([self::CHEESE_READ, self::CHEESE_WRITE, self::CHEESE_ITEM_POST, User::USER_READ, User::USER_WRITE])]
    private ?string $title;

    #[ORM\Column(type: 'text')]
    #[NotBlank]
    #[ApiProperty(description: 'La description du fromage')]
    #[Groups([self::CHEESE_READ])]
    private ?string $description;

    #[ORM\Column(type: 'integer')]
    #[Type('int')]
    #[NotBlank]
    #[ApiProperty(description: 'Le prix du fromage')]
    #[Groups([self::CHEESE_READ, self::CHEESE_WRITE, self::CHEESE_ITEM_POST, User::USER_READ, User::USER_WRITE])]
    private ?int $price;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean')]
    #[ApiProperty]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(description: 'Le propriétaire du fromage')]
    #[Groups([self::CHEESE_READ, self::CHEESE_COLLECTION_POST])]
    #[IsValidOwner]
    private ?User $owner = null;

    public function __construct(string $title = null)
    {
        $this->id = Uuid::v4();
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    #[Groups([self::CHEESE_WRITE])]
    #[SerializedName('title')]
    #[ApiProperty(description: 'La description du fromage en tant que texte brute')]
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[ApiProperty]
    #[Groups([self::CHEESE_READ])]
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }

        return substr($this->description, 0, 40).'...';
    }

    #[Groups([self::CHEESE_WRITE, User::USER_WRITE, self::CHEESE_ITEM_POST])]
    #[SerializedName('description')]
    #[ApiProperty(description: 'La description du fromage en tant que texte brute')]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[Groups([self::CHEESE_READ])]
    #[ApiProperty(description: "Depuis combien de temps en texte le fromage a-t'il été ajouté")]
    public function getCreatedAtAgo(): string
    {
        return (Carbon::instance($this->getCreatedAt()))->locale('fr_FR')->diffForHumans();
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
