<?php
namespace Boekkooi\Broadway\Testing\Constraint;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use Symfony\Component\Validator\Constraint;

class isEqualConstraints extends \PHPUnit_Framework_Constraint
{
    /**
     * @var Constraint[]
     */
    private $constraints;
    /**
     * @var string[]
     */
    private $constraintTypes;
    /**
     * @var bool
     */
    private $autoConfigureGroups;
    /**
     * @var null
     */
    private $implicitGroupName;

    public function __construct(array $constraints, $autoConfigureGroups = true, $implicitGroupName = null)
    {
        parent::__construct();

        $this->constraintTypes = array_filter($constraints, 'is_string');
        $this->constraints = array_filter($constraints, 'is_object');
        $this->autoConfigureGroups = $autoConfigureGroups;
        $this->implicitGroupName = $implicitGroupName;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        $constraints = $other;

        /** @var string[] $expectedConstraintTypes */
        $expectedConstraintTypes = $this->constraintTypes;
        $hasExpectedConstraintTypes = !empty($this->constraintTypes);
        /** @var Constraint[] $expectedConstraints */
        $expectedConstraints = $this->constraints;
        $hasExpectedConstraints = !empty($this->constraints);

        $this->configureConstraintGroups($expectedConstraints);

        if ($hasExpectedConstraints) {
            list($constraints, $expectedConstraints) = $this->excludeKnownConstraints($constraints, $expectedConstraints);
        }

        if ($hasExpectedConstraintTypes) {
            list($constraints, $expectedConstraintTypes) = $this->excludeKnownTypes($constraints, $expectedConstraintTypes);
        }

        // Return if valid
        if (empty($constraints) && empty($expectedConstraints) && empty($expectedConstraintTypes)) {
            if ($returnResult) {
                return true;
            }
            return;
        }

        $comparatorFactory = ComparatorFactory::getInstance();
        try {
            if (empty($expectedConstraints)) {
                $constraintTypes = array_map('get_class', $constraints);

                $comparatorFactory
                    ->getComparatorFor($expectedConstraintTypes, $constraintTypes)
                    ->assertEquals($expectedConstraintTypes, $constraintTypes, 0.0, true);
            } elseif (empty($expectedConstraintTypes)) {
                $comparatorFactory
                    ->getComparatorFor($expectedConstraints, $constraints)
                    ->assertEquals($expectedConstraints, $constraints);
            } else {
                $expected = array_merge($expectedConstraints, $expectedConstraintTypes);
                $comparatorFactory
                    ->getComparatorFor($expected, $constraints)
                    ->assertEquals($expected, $constraints);
            }
        } catch (ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }

            throw new \PHPUnit_Framework_ExpectationFailedException(
                trim($description . "\n" . $f->getMessage()),
                $f
            );
        }

        throw new \LogicException('This stage should never be reached!');
    }


    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'set of constraints are equal';
    }

    /**
     * @param Constraint[] $constraints
     */
    protected function configureConstraintGroups(array $constraints)
    {
        if (!$this->autoConfigureGroups) {
            return;
        }

        $defaultGroups = [ Constraint::DEFAULT_GROUP ];
        if ($this->implicitGroupName !== null) {
            $defaultGroups[] = $this->implicitGroupName;
        }

        foreach ($constraints as $constraint) {
            if (empty($constraint->groups)) {
                $constraint->groups = $defaultGroups;
            } elseif ($this->implicitGroupName !== null) {
                $constraint->addImplicitGroupName($this->implicitGroupName);
            }
        }
    }

    protected function excludeKnownConstraints(array $firstSet, array $secondSet)
    {
        /** @var \PHPUnit_Framework_Constraint_IsEqual[] $equals */
        $equals = array_map(
            function ($constraint) {
                return new \PHPUnit_Framework_Constraint_IsEqual($constraint);
            },
            $firstSet
        );

        foreach ($equals as $i => $equal) {
            foreach ($secondSet as $j => $constraint) {
                if (!$equal->evaluate($constraint, '', true)) {
                    continue;
                }

                unset($firstSet[$i], $secondSet[$j]);
                continue 2;
            }
        }

        return [$firstSet, $secondSet];
    }

    protected function excludeKnownTypes(array $constraints, array $types)
    {
        foreach ($constraints as $i => $constraint) {
            $j = array_search(get_class($constraint), $types, true);
            if ($j === false) {
                continue;
            }

            unset($constraints[$i], $types[$j]);
        }

        return [$constraints, $types];
    }
}
