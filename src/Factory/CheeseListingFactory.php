<?php

namespace App\Factory;

use App\Entity\CheeseListing;
use App\Repository\CheeseListingRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<CheeseListing>
 *
 * @method static              CheeseListing|Proxy createOne(array $attributes = [])
 * @method static              CheeseListing[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static              CheeseListing|Proxy find(object|array|mixed $criteria)
 * @method static              CheeseListing|Proxy findOrCreate(array $attributes)
 * @method static              CheeseListing|Proxy first(string $sortedField = 'id')
 * @method static              CheeseListing|Proxy last(string $sortedField = 'id')
 * @method static              CheeseListing|Proxy random(array $attributes = [])
 * @method static              CheeseListing|Proxy randomOrCreate(array $attributes = [])
 * @method static              CheeseListing[]|Proxy[] all()
 * @method static              CheeseListing[]|Proxy[] findBy(array $attributes)
 * @method static              CheeseListing[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static              CheeseListing[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static              CheeseListingRepository|RepositoryProxy repository()
 * @method CheeseListing|Proxy create(array|callable $attributes = [])
 */
final class CheeseListingFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'id' => Uuid::v4(),
            'title' => self::faker()->words(4, true),
            'textDescription' => self::faker()->text(500),
            'price' => self::faker()->numberBetween(7, 45),
            'isPublished' => self::faker()->boolean(),
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return CheeseListing::class;
    }
}
