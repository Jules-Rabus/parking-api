<?php

namespace Api;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Tests\Functional\Api\AbstractTestCase;

final class ResetTokenTest extends AbstractTestCase
{
    private const string ROUTE = '/reset-password';

    public function testResetPassword(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $user->setEmail('melvin.pierre.mp@gmail.com');
        $user->_save();

        $response = $this->createClientWithCredentials($user)->request('POST', self::ROUTE, [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'email' => $user->getEmail(),
            ],
        ]);

        $array = $response->toArray();

        $token = $array['reset_token'] ?? null;

        if ($token) {
            $response = $this->createClientWithCredentials($user)->request('POST', self::ROUTE.'/reset/'.$token,
                [
                    'headers' => [
                        'Accept' => 'application/ld+json',
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'password' => 'Password1234*',
                    ],
                ]
            );

            $array = $response->toArray();

            if (isset($array['success'])) {
                $this->assertResponseIsSuccessful();
                $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
            }
        }
        $this->assertResponseStatusCodeSame(403);
    }
}
