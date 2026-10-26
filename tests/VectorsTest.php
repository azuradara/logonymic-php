<?php

namespace Logonymic\Tests;

use Logonymic\Canonical;
use Logonymic\Level;
use Logonymic\Nymic;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VectorsTest extends TestCase
{
    private static function vectors(): object
    {
        return json_decode((string) file_get_contents(__DIR__.'/vectors.json'), flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return iterable<string, array{object}>
     */
    public static function canonicalCases(): iterable
    {
        foreach (self::vectors()->canonical as $i => $case) {
            yield "$i $case->canonical" => [$case];
        }
    }

    /**
     * @return iterable<string, array{object}>
     */
    public static function workerCases(): iterable
    {
        foreach (self::vectors()->workers as $name => $case) {
            yield $name => [$case];
        }
    }

    /**
     * @return iterable<string, array{object, object, object}>
     */
    public static function matchCases(): iterable
    {
        $vectors = self::vectors();
        foreach ($vectors->matches as $case) {
            yield "$case->a $case->b $case->threshold" => [$case, $vectors->workers->{$case->a}, $vectors->workers->{$case->b}];
        }
    }

    #[DataProvider('canonicalCases')]
    public function test_canonical_json(object $case): void
    {
        $this->assertSame($case->canonical, Canonical::encode($case->input));
        $this->assertSame($case->hash, Nymic::hash($case->input));
    }

    #[DataProvider('workerCases')]
    public function test_nymic_from_an_object(object $case): void
    {
        $this->assertEquals(Nymic::from((array) $case->nymic), Nymic::of($case->worker));
    }

    #[DataProvider('workerCases')]
    public function test_nymic_from_an_array(object $case): void
    {
        $this->assertEquals(Nymic::from((array) $case->nymic), Nymic::of(json_decode((string) json_encode($case->worker), true)));
    }

    #[DataProvider('workerCases')]
    public function test_nymic_from_json(object $case): void
    {
        $this->assertEquals(Nymic::from((array) $case->nymic), Nymic::of((string) json_encode($case->worker)));
    }

    #[DataProvider('matchCases')]
    public function test_match(object $case, object $a, object $b): void
    {
        $result = Nymic::from((array) $a->nymic)->match(Nymic::from((array) $b->nymic), $case->threshold);

        $this->assertSame(Level::from($case->level), $result->level);
        $this->assertSame((float) $case->similarity, $result->similarity);
    }
}
