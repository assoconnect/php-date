<?php

declare(strict_types=1);

namespace AssoConnect\PHPDate\Tests\Normalizer;

use AssoConnect\PHPDate\AbsoluteDate;
use AssoConnect\PHPDate\Normalizer\AbsoluteDateNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class AbsoluteDateNormalizerTest extends TestCase
{
    /**
     * @var AbsoluteDateNormalizerTest
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new AbsoluteDateNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new AbsoluteDate()));
    }

    public function testNormalize()
    {
        $this->assertEquals('2016-01-01', $this->normalizer->normalize(new AbsoluteDate('2016-01-01')));
    }

    public function testNormalizeUsingFormatPassedInContext()
    {
        $normalizedValue = $this->normalizer->normalize(
            new AbsoluteDate('2016-01-01'),
            null,
            [AbsoluteDateNormalizer::FORMAT_KEY => 'Y']
        );
        $this->assertEquals('2016', $normalizedValue);
    }

    public function testNormalizeUsingFormatPassedInConstructor()
    {
        $normalizer = new AbsoluteDateNormalizer([AbsoluteDateNormalizer::FORMAT_KEY => 'y']);
        $this->assertEquals('16', $normalizer->normalize(new AbsoluteDate('2016-01-01')));
    }

    public function testNormalizeInvalidObjectThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The object must be an instance of "%s".', AbsoluteDate::class));
        $this->normalizer->normalize(new \stdClass());
    }

    public function testSupportsDenormalization()
    {
        $this->assertTrue($this->normalizer->supportsDenormalization('2016-01-01', AbsoluteDate::class));
        $this->assertFalse($this->normalizer->supportsDenormalization('foo', 'Bar'));
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            new AbsoluteDate('2016-01-01'),
            $this->normalizer->denormalize('2016-01-01', AbsoluteDate::class)
        );
    }

    public function testDenormalizeUsingFormatPassedInContext()
    {
        $this->assertEquals(
            new AbsoluteDate('2016-01-01'),
            $this->normalizer->denormalize('2016.01.01', AbsoluteDate::class, null, [
                AbsoluteDateNormalizer::FORMAT_KEY => 'Y.m.d|'
            ])
        );
    }

    public function testDenormalizeInvalidDataThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->normalizer->denormalize('invalid date', AbsoluteDate::class);
    }

    public function testDenormalizeNullThrowsException()
    {
        $this->expectException(NotNormalizableValueException::class);
        $this->expectExceptionMessage(AbsoluteDateNormalizer::EMPTY_STRING_OR_NULL_EXCEPTION_MESSAGE);
        $this->normalizer->denormalize(null, AbsoluteDate::class);
    }

    public function testDenormalizeEmptyStringThrowsException()
    {
        $this->expectException(NotNormalizableValueException::class);
        $this->expectExceptionMessage(AbsoluteDateNormalizer::EMPTY_STRING_OR_NULL_EXCEPTION_MESSAGE);
        $this->normalizer->denormalize('', AbsoluteDate::class);
    }

    public function testDenormalizeFormatMismatchThrowsException()
    {
        $this->expectException(NotNormalizableValueException::class);
        $this->normalizer->denormalize('2016/01/01', AbsoluteDate::class, null, [
            AbsoluteDateNormalizer::FORMAT_KEY => 'Y-m-d|'
        ]);
    }
}
