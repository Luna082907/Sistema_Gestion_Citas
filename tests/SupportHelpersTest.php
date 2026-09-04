<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../src/Support/helpers.php';

use PHPUnit\Framework\TestCase;

final class SupportHelpersTest extends TestCase
{
    public function testFormatDateHandlesEmptyValueWithoutThrowing(): void
    {
        $this->assertSame('', format_date(null));
    }
}
