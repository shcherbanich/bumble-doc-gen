<?php

declare(strict_types=1);

namespace BumbleDocGen\Plugin\CorePlugin\BasePhpStubber;

use BumbleDocGen\Plugin\Event\Entity\OnCheckIsClassEntityCanBeLoad;
use BumbleDocGen\Plugin\Event\Render\OnGettingResourceLink;
use BumbleDocGen\Plugin\PluginInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Exception\PcreException;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Utils;

final class PhpDocumentorStubberPlugin implements PluginInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OnGettingResourceLink::class => 'onGettingResourceLink',
            OnCheckIsClassEntityCanBeLoad::class => 'onCheckIsClassEntityCanBeLoad',
        ];
    }

    final public function onGettingResourceLink(OnGettingResourceLink $event): void
    {
        if (!$event->getResourceUrl()) {
            $resourceName = $event->getResourceName();
            if (!str_starts_with($resourceName, '\\')) {
                $resourceName = "\\{$resourceName}";
            }
            if (str_starts_with($resourceName, '\\phpDocumentor\\Reflection\\')) {
                if (in_array(ltrim($resourceName, '\\'), [
                        DocBlock::class,
                        DocBlockFactory::class,
                        DocBlockFactoryInterface::class,
                        Utils::class,
                        PcreException::class,
                    ]) || str_starts_with($resourceName, '\\phpDocumentor\\Reflection\\DocBlock\\')) {
                    $resource = str_replace(['\\phpDocumentor\\Reflection\\', '\\'], ['', '/'], $resourceName);
                    $event->setResourceUrl("https://github.com/phpDocumentor/ReflectionDocBlock/blob/master/src/{$resource}.php");
                    return;
                }

                if (
                    in_array(ltrim($resourceName, '\\'), [
                        Type::class,
                        TypeResolver::class,
                        PseudoType::class,
                        FqsenResolver::class,
                    ]) ||
                    str_starts_with($resourceName, '\\phpDocumentor\\Reflection\\Types\\') ||
                    str_starts_with($resourceName, '\\phpDocumentor\\Reflection\\PseudoTypes\\')
                ) {
                    $resource = str_replace(['\\phpDocumentor\\Reflection\\', '\\'], ['', '/'], $resourceName);
                    $event->setResourceUrl("https://github.com/phpDocumentor/TypeResolver/blob/master/src/{$resource}.php");
                    return;
                }
            }
        }
    }

    final public function onCheckIsClassEntityCanBeLoad(OnCheckIsClassEntityCanBeLoad $event): void
    {
        if (
            str_starts_with($event->getEntity()->getName(), 'phpDocumentor\\') ||
            str_starts_with($event->getEntity()->getName(), '\\phpDocumentor\\')
        ) {
            $event->disableClassLoading();
        }
    }
}