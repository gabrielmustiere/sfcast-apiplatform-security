<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CustomApiTestCase extends ApiTestCase
{
    private ?Client $client = null;
    private ?EntityManagerInterface $em = null;

    private function getClient(): Client
    {
        if (null === $this->client) {
            $this->client = self::createClient();
        }

        return $this->client;
    }

    protected function executeRequest(string $httpMethods = 'GET', string $url = '', array $json = []): Client
    {
        $client = $this->getClient();

        if ('GET' === $httpMethods) {
            $client->request('GET', $url);

            return $client;
        }

        $client->request($httpMethods, $url, [
            'headers' => [
                'Content-type' => 'application/json',
            ],
            'json' => $json,
        ]);

        return $client;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if (null === $this->em) {
            $this->em = $this->getContainer()->get('doctrine')->getManager();
        }

        return $this->em;
    }

    protected function get(string $url): Client
    {
        return $this->executeRequest(url: $url);
    }

    protected function post(string $url, array $json = []): Client
    {
        return $this->executeRequest('POST', $url, $json);
    }

    protected function put(string $url, array $json = []): Client
    {
        return $this->executeRequest('PUT', $url, $json);
    }

    protected function createUser(string $email = 'dev@gemy-athena.com', $password = 'Gemyathena44*'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strpos($email, '@')));
        $user->setPassword($this->getContainer()->get('security.password_hasher')->hashPassword($user, $password));

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function login(string $email = 'dev@gemy-athena.com', $password = 'Gemyathena44*')
    {
        $this->getClient()->request('POST', 'login', ['headers' => [
            'Content-type' => 'application/json',
        ],
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    protected function createUserAndLogin(string $email = 'dev@gemy-athena.com', $password = 'Gemyathena44*'): User
    {
        $user = $this->createUser($email, $password);
        $this->login($email, $password);

        return $user;
    }
}
