<?php

namespace App\Factory;

use App\Entity\Message;
use App\Entity\Reservation;
use App\Enum\MessageStatusEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Reservation>
 */
final class MessageFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Message::class;
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
            'content' => self::faker()->text(),
            'status' => self::faker()->randomElement(MessageStatusEnum::getValues()),
            'reservation' => ReservationFactory::createOne(),
            'phone' => PhoneFactory::createOne()
        ];
    }
}
