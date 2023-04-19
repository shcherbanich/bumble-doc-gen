<?php

declare(strict_types=1);

namespace BumbleDocGen\Core\Configuration\ValueTransformer;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use function BumbleDocGen\Core\is_associative_array;

final class ValueToClassTransformer implements ValueTransformerInterface
{
    public function __construct(private Container $diContainer)
    {
    }

    public function canTransform(mixed $value): bool
    {
        return is_array($value) && isset($value['class']) && class_exists($value['class']);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function transform(mixed $value): ?object
    {
        if (!$this->canTransform($value)) {
            return null;
        }

        if (!isset($value['arguments'])) {
            return $this->diContainer->get($value['class']);
        }

        $arguments = [];
        foreach ($value['arguments'] as $k => $argument) {
            if ($this->canTransform($argument)) {
                $argument = $this->transform($argument);
            }
            $arguments[$k] = $argument;
        }
        if (!is_associative_array($arguments)) {
            return new $value['class'](...$arguments);
        }
        return $this->diContainer->make($value['class'], $arguments);
    }
}
