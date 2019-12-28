<?php


use Futape\Utility\String\Strings;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\String\Strings
 */
class StringsTest extends TestCase
{
    public function testEscape()
    {
        $this->assertEquals('Fo\\\\o \\\'Bar\\" \\$baz', Strings::escape('Fo\\o \'Bar" $baz'));
    }

    public function testEscapeHandlesExceedingBackslashes()
    {
        $this->assertEquals('Foo \\\\\\$bar', Strings::escape('Foo \\\\\\$bar', '"\'$'));
        $this->assertEquals('Foo ', Strings::escape('Foo \\', '"\'$'));
    }

    public function testStripLeft()
    {
        $this->assertEquals('Bar', Strings::stripLeft('Foo/Bar', 'Foo/'));
        $this->assertEquals('Foo/Bar', Strings::stripLeft('Foo/Bar', 'Baz/'));
    }

    public function testStripLeftIgnoreCase()
    {
        $this->assertEquals('Bar', Strings::stripLeft('Foo/Bar', 'foo/', true));
    }

    public function testStripRight()
    {
        $this->assertEquals('Foo', Strings::stripRight('Foo/Bar', '/Bar'));
        $this->assertEquals('Foo/Bar', Strings::stripRight('Foo/Bar', '/Baz'));
    }

    public function testStripRightIgnoreCase()
    {
        $this->assertEquals('Foo', Strings::stripRight('Foo/Bar', '/bar', true));
    }

    public function testResolve()
    {
        $this->assertEquals("Foo\nBar", Strings::resolve('Foo\nBar'));
    }

    public function testResolveDoesntResolveVariables()
    {
        $this->assertEquals('Foo $bar', Strings::resolve('Foo $bar'));
    }

    public function testNormalizeNewlines()
    {
        $this->assertEquals("Foo\nBar\nBaz\nBam", Strings::normalizeNewlines("Foo\nBar\r\nBaz\rBam"));
    }

    public function testStartsWith()
    {
        $this->assertTrue(Strings::startsWith('Foo/Bar', 'Foo/'));
        $this->assertFalse(Strings::startsWith('Foo/Bar', 'Baz/'));
    }

    public function testStartsWithIgnoresCase()
    {
        $this->assertTrue(Strings::startsWith('Foo/Bar', 'foo/', true));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Strings::endsWith('Foo/Bar', '/Bar'));
        $this->assertFalse(Strings::endsWith('Foo/Bar', '/Baz'));
    }

    public function testEndsWithIgnoresCase()
    {
        $this->assertTrue(Strings::endsWith('Foo/Bar', '/bar', true));
    }

    public function testInline()
    {
        $this->assertEquals('Foo Bar Baz ', Strings::inline("Foo Bar\nBaz\t"));
    }

    /**
     * @dataProvider supstrDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testSupstr(array $input, string $expected)
    {
        $this->assertEquals($expected, Strings::supstr(...$input));
    }

    public function supstrDataProvider(): array
    {
        return [
            'Positive start and length' => [
                ['FooBarBaz', 3, 3],
                'FooBaz'
            ],
            'Positive start, no length' => [
                ['FooBarBaz', 3],
                'Foo'
            ],
            'Negative start, no length' => [
                ['FooBarBaz', -3],
                'FooBar'
            ],
            'Negative start, positive length' => [
                ['FooBarBaz', -6, 3],
                'FooBaz'
            ],
            'Positive start, negative length' => [
                ['FooBarBaz', 3, -3],
                'FooBaz'
            ]
        ];
    }

    /**
     * @dataProvider createSeriesDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testCreateSeries(array $input, string $expected)
    {
        $this->assertEquals($expected, Strings::createSeries(...$input));
    }

    public function createSeriesDataProvider(): array
    {
        return [
            'Basic' => [
                [[4, 9, 1, 6, 2, 7, 5]],
                '1, 2, 4 - 7, 9'
            ],
            'Minimal threshold' => [
                [[4, 9, 1, 6, 2, 7, 5], 2],
                '1 - 2, 4 - 7, 9'
            ],
            'Too low threshold' => [
                [[4, 9, 1, 6, 2, 7, 5], 1],
                '1 - 2, 4 - 7, 9'
            ],
            'Increased threshold' => [
                [[4, 9, 6, 11, 7, 5, 10], 4],
                '4 - 7, 9, 10, 11'
            ],
            'No series due to too high threshold' => [
                [[4, 9, 6, 11, 7, 5, 10], 5],
                '4, 5, 6, 7, 9, 10, 11'
            ],
            'Negative numbers' => [
                [[-5, 1, 4, -1, -6, 0, -4, 2]],
                '-6 - -4, -1 - 2, 4'
            ],
            'Cast values to integers and eliminate duplicate numbers' => [
                [[null, true, false, '']],
                '0, 1'
            ]
        ];
    }
}
