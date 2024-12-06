<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    private const string route = '/users';

    public function testGetCollection(): void
    {
        UserFactory::CreateMany(50);

        $response = static::createClient()->request('GET', self::route,
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/users',
            '@type' => 'Collection',
            'totalItems' => 50,
            'view' => [
                '@id' => '/users?page=1',
                '@type' => 'PartialCollectionView',
                'first' => '/users?page=1',
                'last' => '/users?page=2',
                'next' => '/users?page=2',
            ],
        ]);

        $this->assertCount(30, $response->toArray()['member']);

        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testGetUser(): void
    {
        $user = UserFactory::new()->create();

        $iri = $this->findIriBy(User::class, ['id' => $user->getId()]);

        $response = static::createClient()->request('GET', $iri,
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => $iri,
            '@type' => 'User',
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);

        $this->assertNotContains($user->getPassword(), $response->toArray());

        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testCreateUser(): void
    {
        $user = UserFactory::new()->withoutPersisting()->create();

        $response = static::createClient()->request('POST', self::route,
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => [
                    'email' => $user->getEmail(),
                    'plainPassword' => $user->getPassword(),
                    'roles' => $user->getRoles(),
                ],
            ],
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);

        $this->assertNotContains($user->getPassword(), $response->toArray());

        $this->assertMatchesRegularExpression('~^/users/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testCreateInvalidUser(): void
    {
        $userInvalid = UserFactory::new()->withoutPersisting()->create(['email' => 'not_a_valid_email']);

        static::createClient()->request('POST', self::route,
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                    'Content-Type' => 'application/ld+json',
                ],
                'json' => [
                    'email' => $userInvalid->getEmail(),
                    'plainPassword' => $userInvalid->getPassword(),
                    'roles' => $userInvalid->getRoles(),
                ],
            ],
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'email',
                    'message' => 'This value is not a valid email address.',
                    'code' => 'bd79c0ab-ddba-46cc-a703-a7a4b08de310',
                ],
            ],
            'detail' => 'email: This value is not a valid email address.',
            'description' => 'email: This value is not a valid email address.',
        ]);
    }

    public function testUpdateUser(): void
    {
        $user = UserFactory::new()->create();
        $newEmail = UserFactory::new()->withoutPersisting()->create()->getEmail();

        $iri = $this->findIriBy(User::class, ['id' => $user->getId()]);

        $response = static::createClient()->request('PATCH', $iri,
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                    'Content-Type' => 'application/merge-patch+json',
                ],
                'json' => [
                    'email' => $newEmail,
                ],
            ],
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'email' => $newEmail,
            'roles' => $user->getRoles(),
        ]);

        $this->assertNotContains($user->getPassword(), $response->toArray());

        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testDeleteUser(): void
    {
        $user = UserFactory::new()->create();

        $iri = $this->findIriBy(User::class, ['id' => $user->getId()]);

        static::createClient()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(User::class)->find($user->getId())
        );
    }
}
