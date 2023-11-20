<?php

declare(strict_types=1);

namespace BumbleDocGen\LanguageHandler\Php\Parser\Entity;

/**
 * Object interface
 *
 * @see https://www.php.net/manual/en/language.oop5.interfaces.php
 */
class InterfaceEntity extends ClassLikeEntity
{
    public function isInterface(): bool
    {
        return true;
    }

    public function isAbstract(): bool
    {
        return true;
    }

    public function getModifiersString(): string
    {
        return 'interface';
    }

    public function getTraitsNames(): array
    {
        return [];
    }
}
