<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\UnableToInflectClassName;
use EventSauce\EventSourcing\UnableToInflectEventType;
use RuntimeException;
use Throwable;

use function array_shift;
use function assert;
use function call_user_func_array;
use function is_callable;

final class ChainClassNameInflector implements ClassNameInflector
{
    /** @var array<ClassNameInflector> */
    private readonly array $inflectors;

    public function __construct(
        ClassNameInflector ...$inflectors,
    ) {
        $this->inflectors = $inflectors;
    }

    public function classNameToType(
        string $className,
    ) : string {
        $inflectors = $this->inflectors;

        try {
            return $this->inflect($inflectors, __FUNCTION__, $className);
        } catch (Throwable) {
            throw UnableToInflectClassName::mappingIsNotDefined($className);
        }
    }

    public function typeToClassName(
        string $eventType,
    ) : string {
        $inflectors = $this->inflectors;

        try {
            return $this->inflect($inflectors, __FUNCTION__, $eventType);
        } catch (Throwable) {
            throw UnableToInflectEventType::mappingIsNotDefined($eventType);
        }
    }

    public function instanceToType(
        object $instance,
    ) : string {
        return $this->classNameToType($instance::class);
    }

    /** @param array<ClassNameInflector> $inflectors */
    private function inflect(
        array $inflectors,
        string $method,
        string $classNameOrType,
    ) : string {
        $inflector = array_shift($inflectors);

        if ($inflector === null) {
            throw new RuntimeException('Could not inflect class/type');
        }

        try {
            assert(is_callable($inflectorCallable = [$inflector, $method]));

            return call_user_func_array($inflectorCallable, [$classNameOrType]);
        } catch (Throwable) {
            return $this->inflect($inflectors, $method, $classNameOrType);
        }
    }
}
