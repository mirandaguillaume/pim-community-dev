<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Spellcheck\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Filter\HTMLFilter;
use PHPUnit\Framework\TestCase;

/**
 * @license   https://opensource.org/licenses/MIT MIT
 * @source    https://github.com/mekras/php-speller
 */
class HTMLFilterTest extends TestCase
{
    private HTMLFilter $sut;

    protected function setUp(): void
    {
    }

    public function test_it_tests_basics(): void
    {
        $html = "<br>foo&reg; <a\nhref = '#' title='bar'>\nbaz</a>";
        $this->sut = new HTMLFilter($html);
        $text = "    foo        \n                  bar  \nbaz    ";
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_meta_content(): void
    {
        $html
                    = '<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html" />' . "\n"
                    . '<meta name="Keywords" content="Foo">' . "\n"
                    . '<meta name="foo" content="Foobar">' . "\n"
                    . '<meta name="description" content="Bar">';
        $text
                    = "                                                      \n"
                    . "                               Foo  \n"
                    . "                                  \n"
                    . '                                  Bar  ';
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_script(): void
    {
        $html = "<p>Foo</p>\n<script type=\"text/javascript\">Bar Baz\nBuz</script>";
        $text = "   Foo    \n                                      \n            ";
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_malformed_attribute(): void
    {
        $html = '<p ""="">test</p>';
        $text = '         test    ';
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_malformed_attribute_2(): void
    {
        $html = '<p ">test</p>';
        $text = '     test    ';
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_malformed_attribute_3(): void
    {
        $html = '<p name=">test</p>';
        $text = '          test    ';
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_malformed_attribute_4(): void
    {
        $html = '<p name"=">test</p>';
        $text = '           test    ';
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }

    public function test_it_tests_malformed_attribute_5(): void
    {
        $html = '<p "name=">test</p>';
        $text = '           test    ';
        $this->sut = new HTMLFilter($html);
        $this->assertEquals($text, $this->sut->filter($html));
    }
}
