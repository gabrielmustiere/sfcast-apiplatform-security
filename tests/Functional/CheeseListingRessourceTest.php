<?php

namespace App\Tests\Functional;

use App\Entity\CheeseListing;
use App\Tests\CustomApiTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CheeseListingRessourceTest extends CustomApiTestCase
{
    use ResetDatabase;

    public function testCreateCheeseListing(): void
    {
        $this->post('/api/cheeses', []);

        $this->assertResponseStatusCodeSame(401, 'data missing');

        $authUser = $this->createUserAndLogin();
        $otherUser = $this->createUser('otherUser@progicar.fr');

        $cheesyData = [
            'title' => 'Mystery Cheese',
            'description' => 'What mysteries does it hold ?',
            'price' => 5000,
        ];

        $this->post('/api/cheeses', $cheesyData);

        $this->assertResponseStatusCodeSame(201, 'missing owner');

        $this->post('/api/cheeses', $cheesyData + ['owner' => '/api/users/'.$otherUser->getId()->toRfc4122()]);

        $this->assertResponseStatusCodeSame(422, 'not passing the correct owner');

        $this->post('/api/cheeses', $cheesyData + ['owner' => '/api/users/'.$authUser->getId()->toRfc4122()]);

        $this->assertResponseStatusCodeSame(201, 'insertion cheese + owner ok');
    }

    public function testUpdateCheeseListing()
    {
        $user = $this->createUser();
        $user2 = $this->createUser('toto@toto.toto');

        $cheese = new CheeseListing();
        $cheese->setOwner($user);
        $cheese->setTitle('Le cheddar');
        $cheese->setPrice(2000);
        $cheese->setTextDescription('Mon petit cheddar');
        $cheese->setIsPublished(true);

        $em = $this->getEntityManager();
        $em->persist($cheese);
        $em->flush();

        $this->login();
        $this->put('/api/cheeses/'.$cheese->getId()->toRfc4122(), [
            'json' => [
                'price' => 2500,
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);

        $this->login($user2->getEmail());
        $this->put('/api/cheeses/'.$cheese->getId()->toRfc4122(), [
            'json' => [
                'price' => 2500,
            ],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCheeseListingCollection()
    {
        $user = $this->createUser();

        $cheese = new CheeseListing();
        $cheese->setOwner($user);
        $cheese->setTitle('Le cheddar1');
        $cheese->setPrice(2000);
        $cheese->setTextDescription('Mon petit cheddar');

        $cheese2 = new CheeseListing();
        $cheese2->setOwner($user);
        $cheese2->setTitle('Le cheddar2');
        $cheese2->setPrice(2000);
        $cheese2->setTextDescription('Mon petit cheddar');
        $cheese2->setIsPublished(true);

        $cheese3 = new CheeseListing();
        $cheese3->setOwner($user);
        $cheese3->setTitle('Le cheddar3');
        $cheese3->setPrice(2000);
        $cheese3->setTextDescription('Mon petit cheddar');
        $cheese3->setIsPublished(true);

        $em = $this->getEntityManager();
        $em->persist($cheese);
        $em->persist($cheese2);
        $em->persist($cheese3);
        $em->flush();

        $this->get('/api/cheeses');

        $this->assertJsonContains(['hydra:totalItems' => 2]);
    }

    public function testGetCheeseListingItem()
    {
        $user = $this->createUser();

        $cheese = new CheeseListing();
        $cheese->setOwner($user);
        $cheese->setTitle('Le cheddar1');
        $cheese->setPrice(2000);
        $cheese->setTextDescription('Mon petit cheddar');
        $cheese->setIsPublished(false);

        $em = $this->getEntityManager();
        $em->persist($cheese);
        $em->flush();

        $this->login();

        $this->get('/api/cheeses/'.$cheese->getId()->toRfc4122());
        $this->assertResponseStatusCodeSame(404);

        $client = $this->get('/api/users/'.$user->getId()->toRfc4122());
        $data = $client->getResponse()->toArray();

        $this->assertEmpty($data['cheeseListings']);
    }
}
