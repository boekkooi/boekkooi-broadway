<?php
return \Boekkooi\CS\Config::create()
    ->setDir(__DIR__ . '/src')
    ->checkers(array(
        new \Boekkooi\CS\Checker\Psr4Checker(),
    ))
    ->setRules(array(
        '@PSR2' => true
    ))
;
