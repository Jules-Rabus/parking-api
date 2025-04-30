<?php

namespace App\Factory;

use App\Entity\Phone;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Phone>
 */
final class PhoneFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Phone::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'phoneNumber' => self::faker()->phoneNumber(),
            'owner' => UserFactory::createOne(),
        ];
    }
}
