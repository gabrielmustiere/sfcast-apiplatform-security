<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method static     User|Proxy createOne(array $attributes = [])
 * @method static     User[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static     User|Proxy find(object|array|mixed $criteria)
 * @method static     User|Proxy findOrCreate(array $attributes)
 * @method static     User|Proxy first(string $sortedField = 'id')
 * @method static     User|Proxy last(string $sortedField = 'id')
 * @method static     User|Proxy random(array $attributes = [])
 * @method static     User|Proxy randomOrCreate(array $attributes = [])
 * @method static     User[]|Proxy[] all()
 * @method static     User[]|Proxy[] findBy(array $attributes)
 * @method static     User[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static     User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static     UserRepository|RepositoryProxy repository()
 * @method User|Proxy create(array|callable $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct();
        $this->userPasswordHasher = $userPasswordHasher;
    }

    protected function getDefaults(): array
    {
        $prenom = self::faker()->lastName();
        $nom = self::faker()->firstName();

        return [
            'id' => Uuid::v4(),
            'email' => self::faker()->email(),
            'roles' => [],
            'password' => 'thisisliving',
            'username' => $nom.' '.$prenom,
        ];
    }

    protected function initialize(): self
    {
        return $this->afterInstantiate(function (User $user) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        });
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
