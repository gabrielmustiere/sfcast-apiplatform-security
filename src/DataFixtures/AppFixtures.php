<?php

namespace App\DataFixtures;

use App\Factory\CheeseListingFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'email' => 'gmustiere@progicar.fr',
            'cheeseListings' => CheeseListingFactory::new()->many(5, 15),
        ]);
        UserFactory::createMany(10, ['cheeseListings' => CheeseListingFactory::new()->many(5, 15)]);
    }
}
