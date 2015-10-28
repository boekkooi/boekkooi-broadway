<?php
namespace Tests\Boekkooi\Broadway\CS\Checker;

class EventClassCheckerTest extends AbstractCheckerTestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testCheck($input, $messages)
    {
        $this->makeTest($messages, $input, $this->getTestFile(__DIR__ . '/Event/Finished.php'));
    }

    public function provideCases()
    {
        return [
            [
                '<?php
class Finished {
    public $prop;
    public $check;
    protected $prot;
    private $priv;

    public function __construct($prop, Type $prop1 = null, $prop2) {
        parent::__construct($prop2);
        $this->prop = $prop;
    }

    public function notAllowed() { }
}
                ',
                [
                    '2:1' => [
                        [ E_ERROR, 'check_class_must_be_final_or_abstract', [ 'class' => 'Finished' ]]
                    ],
                    '3:11' => [
                        [ E_ERROR, 'check_class_property_must_have_a_phpdoc', [ 'property' => '$prop' ]],
                    ],
                    '4:11' => [
                        [ E_ERROR, 'check_class_property_must_have_a_phpdoc', [ 'property' => '$check' ]],
                        [ E_ERROR, 'check_class_property_must_be_know_as_constructor_argument', [ 'property' => '$check' ] ]
                    ],
                    '5:14' => [
                        [ E_ERROR, 'check_class_property_must_be_public', [ 'property' => '$prot', 'visibility' => 'protected' ]],
                    ],
                    '6:12' => [
                        [ E_ERROR, 'check_class_property_must_be_public', [ 'property' => '$priv', 'visibility' => 'private' ]],
                    ],
                    '8:12' => [
                        [ E_ERROR, 'check_class_constructor_has_arguments_without_related_property', [ 'arguments' => '$prop1, $prop2' ] ]
                    ],
                    '8:45' => [
                        [ E_ERROR, 'check_class_method_argument_not_used', [ 'method' => '__construct', 'argument' => '$prop1' ] ]
                    ],
                    '13:12' => [
                        [ E_ERROR, 'check_class_must_not_contain_method', [ 'method' => 'notAllowed' ]]
                    ]
                ]
            ],
            [
                '<?php final class test {
                    /**
                     * @var string
                     */
                    public $prop;
                }',
                [
                    '1:13' => [
                        [ E_ERROR, 'check_class_must_have_constructor', [ 'class' => 'test' ] ]
                    ]
                ]
            ],
            [
                '<?php
final class test {
    /**
     * @var string
     */
    public $prop;

    public function __construct($prop)
    {
        $this->prop;
    }
}',
                [
                    '8:33' => [
                        [ E_ERROR, 'check_class_method_argument_not_used', [ 'method' => '__construct', 'argument' => '$prop' ] ]
                    ]
                ]
            ],
            [
                '<?php
final class InvalidDoc {
    public $missing;
    /**
     * @var
     */
    public $invalid1;
    /**
     * @var A
     * @var B
     */
    public $invalid2;
    /**
     */
    public $empty;

    public function __construct($missing, $invalid1, $invalid2, $empty) {
        $this->missing = $missing;
        $this->invalid1 = $invalid1;
        $this->invalid2 = $invalid2;
        $this->empty = $empty;
    }
}
',
                [
                    '3:11' => [
                        [ E_ERROR, 'check_class_property_must_have_a_phpdoc', [ 'property' => '$missing' ]],
                    ],
                    '4:4' => [
                        [ E_ERROR, 'check_class_property_phpdoc_invalid_var', [ 'property' => '$invalid1' ] ]
                    ],
                    '8:4' => [
                        [ E_ERROR, 'check_class_property_phpdoc_invalid_var', [ 'property' => '$invalid2' ] ]
                    ],
                    '13:4' => [
                        [ E_ERROR, 'check_class_property_phpdoc_must_have_var', [ 'property' => '$empty' ] ]
                    ],
                ]

            ],
            [
                '<?php
                final class Finished {
                    /**
                     * @var string
                     */
                    public $prop;

                    public function __construct($prop) {
                        $this->prop = $prop;
                    }
                }
                ',
                []
            ],
            [
                '<?php
                abstract class test { }
                ',
                []
            ],
            [
                '<?php final class test { function __construct() { } }
                ',
                [
                    '1:26' => [
                        [ E_ERROR, 'check_class_method_must_have_a_visibility', [ 'method' => '__construct' ]]
                    ]
                ]
            ],
            [
                '<?php final class test { private function __construct() { } }
                ',
                [
                    '1:34' => [
                        [ E_ERROR, 'check_class_method_must_be_public', [ 'method' => '__construct', 'visibility' => 'private' ]]
                    ]
                ]
            ],
            [
                '<?php',
                []
            ],
        ];
    }
}
