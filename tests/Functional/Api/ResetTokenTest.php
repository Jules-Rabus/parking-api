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
        UserFactory::CreateMany(48);

        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);

        $response = $this->createClientWithCredentials($user)->request('GET', self::ROUTE,
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                ],
            ]
        );

        $token = $response['reset_token'] ?? null;

        if ($token) {
            $response = $this->createClientWithCredentials($user)->request('GET', self::ROUTE.'/reset/'.$token,
                [
                    'headers' => [
                        'Accept' => 'application/ld+json',
                    ],
                ]
            );

            if ($response['success']) {
                $this->assertResponseIsSuccessful();
                $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
            }
        }
        $this->assertResponseStatusCodeSame(403);
    }
}
