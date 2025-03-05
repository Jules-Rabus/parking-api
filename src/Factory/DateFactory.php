<?php

namespace App\Factory;

use App\Entity\Date;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Date>
 */
final class DateFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Date::class;
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
            'date' => self::faker()->dateTimeBetween('-1 year', '+1 year'),
        ];
    }

}

