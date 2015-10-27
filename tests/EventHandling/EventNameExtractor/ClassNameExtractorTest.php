<?php
namespace Tests\Boekkooi\Broadway\EventHandling\EventNameExtractor;

use Boekkooi\Broadway\EventHandling\EventNameExtractor\ClassNameExtractor;

class ClassNameExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassNameExtractor
     */
    private $extractor;

    protected function setUp()
    {
        $this->extractor = new ClassNameExtractor();
    }

    public function testExtractsNameFromACommand()
    {
        self::assertEquals(
            'stdClass',
            $this->extractor->extract(new \stdClass)
        );
    }
}
