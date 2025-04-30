<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    // php bin/console doctrine:fixtures:load
    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(10);
    }
}
