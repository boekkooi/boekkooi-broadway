<?php
namespace Tests\Boekkooi\Broadway\CS\Checker;

use Boekkooi\CS\Tests\AbstractCheckerTestCase as TestCase;

class AbstractCheckerTestCase extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getChecker()
    {
        $name = 'Boekkooi\Broadway\CS\Checker'.substr(get_called_class(), strlen(__NAMESPACE__), -strlen('Test'));

        $checker = new $name();
        $checker->configure($this->getCheckerConfiguration());

        return $checker;
    }

    protected function getCheckerConfiguration()
    {
        return null;
    }
}
