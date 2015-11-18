<?php
namespace Boekkooi\Broadway\Testing\Comparator;

use Doctrine\Common\Collections\Collection;
use SebastianBergmann\Comparator\ArrayComparator;

class DoctrineCollectionComparator extends ArrayComparator
{
    /**
     * @inheritdoc
     */
    public function accepts($expected, $actual)
    {
        return $expected instanceof Collection && $actual instanceof Collection;
    }

    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = array())
    {
        /** @var Collection $expected */
        $expected = $expected->toArray();
        /** @var Collection $actual */
        $actual = $actual->toArray();

        parent::assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase, $processed);
    }
}
