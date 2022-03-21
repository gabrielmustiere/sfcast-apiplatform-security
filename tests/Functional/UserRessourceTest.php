<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\CustomApiTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRessourceTest extends CustomApiTestCase
{
    use ResetDatabase;

    public function testCreateUer()
    {
        $this->post('/api/users', [
            'email' => 'monpetitbrie@toto.com',
            'username' => 'monpetitbrie',
            'password' => 'brie',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->login('monpetitbrie@toto.com', 'brie');
    }

    public function testUpdateUser()
    {
        $user = $this->createUserAndLogin();
        $this->put('/api/users/'.$user->getId()->toRfc4122(), [
            'username' => 'newusername',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newusername',
        ]);
    }

    /**
     * @test
     */
    public function enTantQueUserSimpleLeTelephoneNeRemontePas()
    {
        $user = $this->createUser();
        $this->createUser('authenticated@test.test');
        $this->login('authenticated@test.test');
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->find($user->getId()->toRfc4122());
        $user->setPhoneNumber('9876543210');
        $em->flush();

        $client = $this->get('/api/users/'.$user->getId()->toRfc4122());
        $this->assertJsonContains([
            'username' => 'dev',
        ]);

        $data = $client->getResponse()->toArray();

        $this->assertArrayNotHasKey('phoneNumber', $data);
    }

    /**
     * @test
     */
    public function enTantQueAdminLeTelephoneRemonte()
    {
        $user = $this->createUserAndLogin();

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->find($user->getId()->toRfc4122());
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPhoneNumber('9876543210');
        $em->persist($user);
        $em->flush();

        $this->login();

        $data = $this->get('/api/users/'.$user->getId()->toRfc4122());

        $this->assertJsonContains([
            'phoneNumber' => '9876543210',
        ]);
    }
}
