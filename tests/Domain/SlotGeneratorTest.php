<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\SlotGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SlotGeneratorTest extends TestCase{

    public function testGeneratesTwelveTwentyMinuteSlots(): void{
        $slots = (new SlotGenerator())->generate('08:00', '12:00', 20);
        self::assertCount(12, $slots);
        self::assertSame('08:00:00', $slots[0]);
        self::assertSame('11:40:00', $slots[11]);
    }

    public function testRejectsAnInvalidRange(): void{
        $this->expectException(InvalidArgumentException::class);
        (new SlotGenerator())->generate('12:00', '08:00', 20);
    }
}