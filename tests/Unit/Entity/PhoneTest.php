<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Phone;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function testGetId(): void
    {
        $this->assertNull((new Phone())->getId());
    }

    #[DataProvider('validPhoneProvider')]
    public function testSetPhoneNumberNormalisation(string $input, string $expected): void
    {
        $phone = new Phone();
        $phone->setPhoneNumber($input);

        $this->assertSame($expected, $phone->getPhoneNumber());
    }

    #[DataProvider('invalidPhoneProvider')]
    public function testSetPhoneNumberWithInvalidValues(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Phone())->setPhoneNumber($input);
    }
    
    public static function validPhoneProvider(): array
    {
        return [
            ['06 88 12 45 56', '+33688124556'],
            ['07-12-34-56-78', '+33712345678'],
            ['0033 6 98 76 54 32', '+33698765432'],
            ['688124556', '+33688124556'],
            ['  06 88 12 45 56 ', '+33688124556'],
            ['+33612345678', '+33612345678'],
        ];
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            ['0512345678'],   // pas un mobile
            ['0688'],         // trop court
            ['061234567890'], // trop long
            ['abcde'],        // non numérique
            ['+441234567890'] // mauvais pays
        ];
    }
}
