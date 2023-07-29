<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests;

use AssoConnect\PHPDate\LocalizedStringParser;
use PHPUnit\Framework\TestCase;

class LocalizedStringParserTest extends TestCase
{
    /** @dataProvider provideStringsAndLocales */
    public function testCreateFromLocaleWorks(string $formattedDate, string $locale, string $date): void
    {
        $parser = new LocalizedStringParser();
        self::assertSame($date, $parser->create($formattedDate, $locale)->format());
    }

    /** @return array{string, string, string}[] */
    public function provideStringsAndLocales(): iterable
    {
        yield ['09/01/2023', 'en_US', '2023-09-01'];
        yield ['9/1/2023', 'en_US', '2023-09-01'];
        yield ['9/1/23', 'en_US', '2023-09-01'];

        yield ['09/01/2023', 'fr_FR', '2023-01-09'];
        yield ['9/1/2023', 'fr_FR', '2023-01-09'];
        yield ['9/1/23', 'fr_FR', '2023-01-09'];
    }
}
