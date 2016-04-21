<?php

use \attakei\QiitaMarkdown\QiitaMarkdown;


class QiitaMarkdownTest extends \PHPUnit_Framework_TestCase
{
    protected static function createParser()
    {
        return new QiitaMarkdown();
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass(QiitaMarkdown::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function _testPattern($lines, $language, $filename)
    {
        $parser = static::createParser();
        $method = static::getMethod('consumeFencedCode');
        list($block, $i) = $method->invokeArgs($parser, [$lines, 0]);
        $this->assertArrayHasKey('content', $block);
        $this->assertArrayHasKey('language', $block);
        $this->assertArrayHasKey('filename', $block);
        $this->assertEquals($block['language'], $language);
        $this->assertEquals($block['filename'], $filename);
    }

    public function testPlainCode()
    {
        $lines = [
            '```',
            'test',
            '```',
        ];
        $this->_testPattern($lines, '', '');
    }

    public function testCodeWithName()
    {
        $lines = [
            '```testcode.c',
            'test',
            '```',
        ];
        $this->_testPattern($lines, '', 'testcode.c');
    }

    public function testCodeWithNameAndLanguage()
    {
        $lines = [
            '```c:testcode.c',
            'test',
            '```',
        ];
        $this->_testPattern($lines, 'c', 'testcode.c');
    }
}
