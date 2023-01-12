<?php

declare(strict_types=1);

namespace BumbleDocGen\Parser\Entity;

use BumbleDocGen\ConfigurationInterface;
use BumbleDocGen\Parser\AttributeParser;
use BumbleDocGen\Render\Context\DocumentTransformableEntityInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\Reflector;

/**
 * Class entity
 */
class ClassEntity extends BaseEntity implements DocumentTransformableEntityInterface
{
    private array $pluginsData = [];

    protected function __construct(
        protected ConfigurationInterface $configuration,
        protected Reflector              $reflector,
        protected ReflectionClass        $reflection,
        protected AttributeParser        $attributeParser
    )
    {
        parent::__construct($configuration, $reflector, $attributeParser);
    }

    public static function create(
        ConfigurationInterface $configuration,
        Reflector              $reflector,
        ReflectionClass        $reflectionClass,
        AttributeParser        $attributeParser,
        bool                   $reloadCache = false
    ): ClassEntity
    {
        static $classEntities = [];
        $objectId = static::generateObjectIdByReflection($reflectionClass);
        if (!isset($classEntities[$objectId]) || $reloadCache) {
            $classEntities[$objectId] = new static(
                $configuration,
                $reflector,
                $reflectionClass,
                $attributeParser
            );
        }
        return $classEntities[$objectId];
    }

    protected function getDocCommentReflectionRecursive(): ReflectionClass
    {
        static $docCommentsReflectionCache = [];
        $objectId = $this->getObjectId();
        if (!isset($docCommentsReflectionCache[$objectId])) {
            $getDocCommentReflection = function (ReflectionClass $reflectionClass) use (&$getDocCommentReflection
            ): ReflectionClass {
                $docComment = $reflectionClass->getDocComment();
                if (!$docComment || str_contains(mb_strtolower($docComment), '@inheritdoc')) {
                    try {
                        $parentReflectionClass = $reflectionClass->getParentClass();
                        if ($parentReflectionClass) {
                            $reflectionClass = $getDocCommentReflection($parentReflectionClass);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
                return $reflectionClass;
            };
            $docCommentsReflectionCache[$objectId] = $getDocCommentReflection($this->getReflection());
        }
        return $docCommentsReflectionCache[$objectId];
    }

    protected function getDocCommentRecursive(): string
    {
        static $docCommentsCache = [];
        $objectId = $this->getObjectId();
        if (!isset($docCommentsCache[$objectId])) {
            $reflectionClass = $this->getDocCommentReflectionRecursive();
            $docCommentsCache[$objectId] = $reflectionClass->getDocComment() ?: ' ';
        }

        return $docCommentsCache[$objectId];
    }

    public function loadPluginData(string $pluginKey, array $data): void
    {
        $this->pluginsData[$pluginKey] = $data;
    }

    public function getPluginData(string $pluginKey): ?array
    {
        return $this->pluginsData[$pluginKey] ?? null;
    }

    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    public function getImplementingReflectionClass(): ReflectionClass
    {
        return $this->getReflection();
    }

    public function hasAnnotationKey(string $annotationKey): bool
    {
        return false;
    }

    public function getName(): string
    {
        return $this->getReflection()->getName();
    }

    public function getShortName(): string
    {
        return $this->getReflection()->getShortName();
    }

    public function getNamespaceName(): string
    {
        return $this->getReflection()->getNamespaceName();
    }

    public function getFileName(): string
    {
        return str_replace($this->configuration->getProjectRoot(), '', $this->getReflection()->getFileName());
    }

    public function getFilePath(): string
    {
        $shortFileName = array_reverse(explode(DIRECTORY_SEPARATOR, $this->getFileName()))[0];
        return rtrim(str_replace($shortFileName, '', $this->getFileName()), DIRECTORY_SEPARATOR);
    }

    public function getStartLine(): int
    {
        return $this->getReflection()->getStartLine();
    }

    public function getEndLine(): int
    {
        return $this->getReflection()->getEndLine();
    }

    public function getModifiersString(): string
    {
        $modifiersString = [];

        $reflection = $this->getReflection();
        if ($reflection->isFinal() && !$reflection->isEnum()) {
            $modifiersString[] = 'final';
        }

        $isInterface = $reflection->isInterface();
        if ($isInterface) {
            $modifiersString[] = 'interface';
            return implode(' ', $modifiersString);
        } elseif ($reflection->isAbstract()) {
            $modifiersString[] = 'abstract';
        }

        if ($reflection->isTrait()) {
            $modifiersString[] = 'trait';
        } elseif ($reflection->isEnum()) {
            $modifiersString[] = 'enum';
        } else {
            $modifiersString[] = 'class';
        }

        return implode(' ', $modifiersString);
    }

    public function getExtends(): ?string
    {
        static $extends = [];
        $objectId = $this->getObjectId();
        if (!isset($extends[$objectId])) {
            $reflection = $this->getReflection();
            if ($reflection->isInterface()) {
                $extends[$objectId] = $reflection->getInterfaceNames()[0] ?? null;
            } else {
                $extends[$objectId] = $reflection->getParentClass()?->getName();
            }
        }
        return $extends[$objectId];
    }

    public function getInterfaces(): array
    {
        static $interfaces = [];
        $objectId = $this->getObjectId();
        if (!isset($interfaces[$objectId])) {
            $reflection = $this->getReflection();
            $interfaces[$objectId] = !$reflection->isInterface() ? $reflection->getInterfaceNames() : [];
        }
        return $interfaces[$objectId];
    }

    /**
     * @return string[]
     */
    public function getParentClassNames(): array
    {
        static $parentClassNames = [];
        $objectId = $this->getObjectId();
        if (!isset($parentClassNames[$objectId])) {
            $reflection = $this->getReflection();
            if ($reflection->isInterface()) {
                $parentClassNames[$objectId] = $reflection->getInterfaceNames();
            } else {
                $parentClassNames[$objectId] = $reflection->getParentClassNames();
            }
        }
        return $parentClassNames[$objectId];
    }

    public function getInterfacesString(): string
    {
        return implode(', ', $this->getInterfaces());
    }

    public function getTraitsNames(): array
    {
        static $traits = [];
        $objectId = $this->getObjectId();
        if (!isset($traits[$objectId])) {
            $traits[$objectId] = $this->getReflection()->getTraitNames();
        }
        return $traits[$objectId];
    }

    public function hasTraits(): bool
    {
        return count($this->getTraitsNames()) > 0;
    }

    public function getConstantEntityCollection(): ConstantEntityCollection
    {
        static $constantEntityCollection = [];
        if (!isset($constantEntityCollection[$this->getObjectId()])) {
            $constantEntityCollection[$this->getObjectId()] = ConstantEntityCollection::createByReflectionClass(
                $this->configuration,
                $this->reflector,
                $this->getReflection(),
                $this->attributeParser
            );
        }
        return $constantEntityCollection[$this->getObjectId()];
    }

    public function getPropertyEntityCollection(): PropertyEntityCollection
    {
        static $propertyEntityCollection = [];
        if (!isset($propertyEntityCollection[$this->getObjectId()])) {
            $propertyEntityCollection[$this->getObjectId()] = PropertyEntityCollection::createByReflectionClass(
                $this->configuration,
                $this->reflector,
                $this->getReflection(),
                $this->attributeParser
            );
        }
        return $propertyEntityCollection[$this->getObjectId()];
    }

    public function getMethodEntityCollection(): MethodEntityCollection
    {
        static $methodEntityCollection = [];
        if (!isset($methodEntityCollection[$this->getObjectId()])) {
            $methodEntityCollection[$this->getObjectId()] = MethodEntityCollection::createByClassEntity(
                $this->configuration,
                $this->reflector,
                $this,
                $this->attributeParser
            );
        }
        return $methodEntityCollection[$this->getObjectId()];
    }

    public function getDescription(): string
    {
        $docBlock = $this->getDocBlock();
        return $docBlock->getSummary();
    }

    public function isEnum(): bool
    {
        return $this->getReflection()->isEnum();
    }
}
