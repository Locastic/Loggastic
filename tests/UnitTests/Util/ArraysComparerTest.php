<?php

namespace Locastic\Loggastic\Tests\UnitTests\Util;

use Locastic\Loggastic\Util\ArraysComparer;
use PHPUnit\Framework\TestCase;

class ArraysComparerTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetCompared(array $previousData, array $currentData, $expectedResult): void
    {
        $actualResult = ArraysComparer::getCompared($currentData, $previousData);

        if($expectedResult === null) {
            self::assertNull($actualResult);

            return;
        }

        self::assertEquals($expectedResult, $actualResult);
    }

    public static function dataProvider(): iterable
    {
        yield[
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3],
            null
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => 4],
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['previousValues' => ['c' => 4], 'currentValues' => ['c' => 3]]
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 5],
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['previousValues' => ['c' => 4, 'd' => 5], 'currentValues' => ['c' => 3]]
        ];
        yield[
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['previousValues' => [], 'currentValues' => ['c' => 3]]
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 2, 'd' => 5],
            ['previousValues' => ['c' => 3], 'currentValues' => ['d' => 5]]
        ];
        //test with nested arrays
        yield[
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 5]],
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 5]],
            null
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 6]],
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 5]],
            ['previousValues' => ['c' => ['e' => 6]], 'currentValues' => ['c' => ['e' => 5]]]
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 6, 'f' => 7]],
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 5]],
            ['previousValues' => ['c' => ['e' => 6, 'f' => 7]], 'currentValues' => ['c' => ['e' => 5]]]
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 5]],
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4]],
            ['previousValues' => ['c' => ['e' => 5]], 'currentValues' => []]
        ];
        yield[
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'f' => 7]],
            ['a' => 1, 'b' => 2, 'c' => ['d' => 4, 'e' => 5]],
            ['previousValues' => ['c' => ['f' => 7]], 'currentValues' => ['c' => ['e' => 5]]]
        ];
    }
}
