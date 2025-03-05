<?php

namespace App\Factory;

use App\Entity\Date;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Reservation>
 */
final class ReservationFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Reservation::class;
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
            'startDate' => self::faker()->dateTimeBetween('+1 days', '+1 month'),
            'endDate' => self::faker()->dateTimeBetween('+1 month', '+2 month'),
            'vehicleCount' => self::faker()->numberBetween(1, 5),
            'status' => self::faker()->randomElement([ReservationStatusEnum::CONFIRMED, ReservationStatusEnum::PENDING]),
            'dates' => [DateFactory::createOne()]
        ];
    }
}

