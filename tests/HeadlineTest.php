<?php

use \attakei\QiitaMarkdown\QiitaMarkdown;


class HeadlineTest extends \PHPUnit_Framework_TestCase
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

    protected function _testRender($lines, $pos)
    {
        $parser = static::createParser();
        $method = static::getMethod('consumeHeadline');
        list($block, $i) = $method->invokeArgs($parser, [$lines, $pos]);
        $method = static::getMethod('renderHeadline');
        return $method->invokeArgs($parser, [$block]);
    }

    public function testPlainCode()
    {
        $lines = [
            '# test1',
        ];
        $rendered = $this->_testRender($lines, 0);
        $this->assertEquals(trim($rendered), '<h1 name="headline-1">test1</h1>');
    }

    public function testPlainCodeMultiHead()
    {
        $text = ''
            . "# test1\n"
            . "# test2\n";
        $parser = static::createParser();
        $rendered = $parser->parse($text);
        $this->assertEquals(trim($rendered), '<h1 name="headline-1">test1</h1><h1 name="headline-2">test2</h1>');
    }

    public function testPlainCodeChildHead()
    {
        $text = ''
            . "# test1\n"
            . "## test2\n";
        $parser = static::createParser();
        $rendered = $parser->parse($text);
        $this->assertEquals(trim($rendered), '<h1 name="headline-1">test1</h1><h2 name="headline-1-1">test2</h2>');
    }

    public function testPlainCodeComplexHead()
    {
        $text = ''
            . "# test1\n"
            . "## test2\n"
            . "# test3\n";
        $parser = static::createParser();
        $rendered = $parser->parse($text);
        // $this->assertEquals(trim($rendered),
        //     '<h1 name="headline-1">test1</h1>'
        //     . '<h2 name="headline-1-1">test2</h2>'
        //     . '<h1 name="headline-2">test3</h1>'
        // );
    }
}
