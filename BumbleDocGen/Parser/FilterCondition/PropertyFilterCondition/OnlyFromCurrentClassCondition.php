<?php

declare(strict_types=1);

namespace BumbleDocGen\Parser\FilterCondition\PropertyFilterCondition;

use BumbleDocGen\Parser\Entity\PropertyEntity;
use BumbleDocGen\Parser\FilterCondition\ConditionInterface;

/**
 * Only properties that belong to the current class (not parent)
 */
final class OnlyFromCurrentClassCondition implements ConditionInterface
{
    public function __construct(
        private PropertyEntity $propertyEntity
    ) {
    }

    public function canAddToCollection(): bool
    {
        return !$this->propertyEntity->isImplementedInParentClass();
    }
}
