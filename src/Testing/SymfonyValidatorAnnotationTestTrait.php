<?php
namespace Boekkooi\Broadway\Testing;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Validator\Validation;

trait SymfonyValidatorAnnotationTestTrait
{
    private static $registered = false;

    protected static function registerValidatorAnnotations($force = false)
    {
        if (!$force && self::$registered) {
            return;
        }

        $refl = new \ReflectionClass(Validation::class);
        if ($refl->getFileName() === false) {
            // We can't setup the auto loading without knowing the path
            return;
        }
        $filePath = str_replace('\\', '/', $refl->getFileName());

        // Detect PSR-0 loading
        $psr0Path = '/Symfony/Component/Validator/Validation.php';
        if (substr($filePath, -strlen($psr0Path)) === $psr0Path) {
            AnnotationRegistry::registerAutoloadNamespace(
                'Symfony\\Component\\Validator\\Constraints',
                substr($filePath, 0, -strlen($psr0Path))
            );

            self::$registered = true;
            return;
        }

        // Custom PSR-4 loader
        $constraintsDir = dirname($filePath) . '/Constraints/';
        AnnotationRegistry::registerLoader(function($class) use ($constraintsDir) {
            $ns = 'Symfony\Component\Validator\Constraints\\';
            if (strpos($class, $ns) !== 0) {
                return;
            }

            $filePath = $constraintsDir . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($ns))) . '.php';
            if (file_exists($filePath)) {
                include $filePath;

                return true;
            }
        });

        self::$registered = true;
    }
}
