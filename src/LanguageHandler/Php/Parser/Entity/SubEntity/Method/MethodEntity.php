<?php

declare(strict_types=1);

namespace BumbleDocGen\LanguageHandler\Php\Parser\Entity\SubEntity\Method;

use BumbleDocGen\Core\Cache\LocalCache\Exception\ObjectNotFoundException;
use BumbleDocGen\Core\Cache\LocalCache\LocalObjectCache;
use BumbleDocGen\Core\Configuration\Configuration;
use BumbleDocGen\Core\Configuration\Exception\InvalidConfigurationParameterException;
use BumbleDocGen\Core\Parser\Entity\Cache\CacheableMethod;
use BumbleDocGen\LanguageHandler\Php\Parser\Entity\BaseEntity;
use BumbleDocGen\LanguageHandler\Php\Parser\Entity\PhpEntitiesCollection;
use BumbleDocGen\LanguageHandler\Php\Parser\Entity\ClassLikeEntity;
use BumbleDocGen\LanguageHandler\Php\Parser\ParserHelper;
use BumbleDocGen\LanguageHandler\Php\Parser\PhpParser\NodeValueCompiler;
use DI\DependencyException;
use DI\NotFoundException;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use PhpParser\ConstExprEvaluationException;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\PrettyPrinter\Standard;
use Psr\Log\LoggerInterface;

/**
 * Class method entity
 */
class MethodEntity extends BaseEntity implements MethodEntityInterface
{
    /**
     * Indicates that the method is public.
     */
    public const MODIFIERS_FLAG_IS_PUBLIC = 1;

    /**
     * Indicates that the method is protected.
     */
    public const MODIFIERS_FLAG_IS_PROTECTED = 2;

    /**
     * Indicates that the method is private.
     */
    public const MODIFIERS_FLAG_IS_PRIVATE = 4;

    public const VISIBILITY_MODIFIERS_FLAG_ANY =
        self::MODIFIERS_FLAG_IS_PUBLIC |
        self::MODIFIERS_FLAG_IS_PROTECTED |
        self::MODIFIERS_FLAG_IS_PRIVATE;

    private ?ClassMethod $ast = null;

    public function __construct(
        private Configuration $configuration,
        private ClassLikeEntity $classEntity,
        ParserHelper $parserHelper,
        private Standard $astPrinter,
        private LocalObjectCache $localObjectCache,
        private LoggerInterface $logger,
        private string $methodName,
        private string $implementingClassName,
    ) {
        parent::__construct(
            $configuration,
            $localObjectCache,
            $parserHelper,
            $logger
        );
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    public function getAst(): ClassMethod
    {
        if (!$this->ast) {
            $implementingClass = $this->getImplementingClass();
            $this->ast = $implementingClass->getAst()->getMethod($this->methodName);
        }
        if (is_null($this->ast)) {
            throw new \RuntimeException("Method `{$this->methodName}` not found in `{$this->getImplementingClassName()}` class AST");
        }
        return $this->ast;
    }

    public function getRootEntity(): ClassLikeEntity
    {
        return $this->classEntity;
    }

    /**
     * @inheritDoc
     */
    public function getRootEntityCollection(): PhpEntitiesCollection
    {
        return $this->getRootEntity()->getRootEntityCollection();
    }

    /**
     * @inheritDoc
     */
    public function getImplementingClass(): ClassLikeEntity
    {
        return $this->getRootEntityCollection()->getLoadedOrCreateNew($this->getImplementingClassName());
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->methodName;
    }

    /**
     * @inheritDoc
     */
    public function getShortName(): string
    {
        return $this->getName();
    }

    /**
     * @inheritDoc
     */
    public function getNamespaceName(): string
    {
        return $this->getRootEntity()->getNamespaceName();
    }

    /**
     * @inheritDoc
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidConfigurationParameterException
     */
    public function getDocCommentEntity(): MethodEntity
    {
        $objectId = $this->getObjectId();
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, $objectId);
        } catch (ObjectNotFoundException) {
        }
        $docComment = $this->getDocComment();
        $reflectionMethod = $this;
        if ($reflectionMethod->isImplementedInParentClass()) {
            $reflectionMethod = $reflectionMethod->getImplementingClass()->getMethod($this->getName(), true);
        }

        if (!$docComment || str_contains(mb_strtolower($docComment), '@inheritdoc')) {
            $implementingClass = $this->getImplementingClass();
            $parentClass = $this->getImplementingClass()->getParentClass();
            $methodName = $this->getName();
            if ($parentClass && $parentClass->isEntityDataCanBeLoaded() && $parentClass->hasMethod($methodName)) {
                $parentReflectionMethod = $parentClass->getMethod($methodName, true);
                $reflectionMethod = $parentReflectionMethod->getDocCommentEntity();
            } else {
                foreach ($implementingClass->getInterfacesEntities() as $interface) {
                    if ($interface->isEntityDataCanBeLoaded() && $interface->hasMethod($methodName)) {
                        $reflectionMethod = $interface->getMethod($methodName, true);
                        break;
                    }
                }
            }
        }
        $this->localObjectCache->cacheMethodResult(__METHOD__, $objectId, $reflectionMethod);
        return $reflectionMethod;
    }

    /**
     * Get the parent method for this method
     *
     * @api
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidConfigurationParameterException
     */
    public function getParentMethod(): ?MethodEntity
    {
        $objectId = $this->getObjectId();
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, $objectId);
        } catch (ObjectNotFoundException) {
        }
        $parentClass = $this->getImplementingClass()->getParentClass();
        $parentMethod = $parentClass->getMethod($this->getName(), true);
        $this->localObjectCache->cacheMethodResult(__METHOD__, $objectId, $parentMethod);
        return $parentMethod;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function getDocCommentLine(): ?int
    {
        return $this->getAst()->getDocComment()?->getStartLine();
    }

    /**
     * @inheritDoc
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidConfigurationParameterException
     */
    public function getSignature(): string
    {
        return "{$this->getModifiersString()} {$this->getName()}({$this->getParametersString()})" . (!$this->isConstructor() ? ": {$this->getReturnType()}" : '');
    }

    /**
     * Checking that a method is a constructor
     *
     * @api
     */
    public function isConstructor(): bool
    {
        return $this->getName() === '__construct';
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    public function getRelativeFileName(): ?string
    {
        return $this->getImplementingClass()->getRelativeFileName();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    public function getModifiersString(): string
    {
        $modifiersString = [];
        if ($this->isPrivate()) {
            $modifiersString[] = 'private';
        } elseif ($this->isProtected()) {
            $modifiersString[] = 'protected';
        } elseif ($this->isPublic()) {
            $modifiersString[] = 'public';
        }

        if ($this->isStatic()) {
            $modifiersString[] = 'static';
        }

        $modifiersString[] = 'function';

        return implode(' ', $modifiersString);
    }

    /**
     * @inheritDoc
     *
     * @throws NotFoundException
     * @throws DependencyException
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function getReturnType(): string
    {
        $type = $this->getAst()->getReturnType();
        if ($type) {
            $typeString = $this->astPrinter->prettyPrint([$type]);
            $typeString = str_replace('?', 'null|', $typeString);
        } else {
            $docBlock = $this->getDocBlock();
            $returnType = $docBlock->getTagsByName('return');
            $returnType = $returnType[0] ?? null;

            if ($returnType && is_a($returnType, InvalidTag::class)) {
                if (str_starts_with((string)$returnType, 'array')) {
                    return 'array';
                }
                return 'mixed';
            }
            $typeString = $returnType ? (string)$returnType->getType() : 'mixed';
            $typeString = preg_replace_callback(['/({)([^{}]*)(})/', '/(\[)([^\[\]]*)(\])/'], function ($condition) {
                return str_replace(' ', '', $condition[0]);
            }, $typeString);
        }
        return $this->prepareTypeString($typeString);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     * @throws \Exception
     */
    #[CacheableMethod] public function getParameters(): array
    {
        $parameters = [];
        $docBlock = $this->getDocBlock();

        $isArrayAnnotationType = function (string $annotationType): bool {
            return preg_match('/^([a-zA-Z\\_]+)(\[\])$/', $annotationType) ||
            preg_match('/^(array)(<|{)(.*)(>|})$/', $annotationType);
        };

        /**
         * @var Param[] $params
         */
        $params = $docBlock->getTagsByName('param');
        $typesFromDoc = [];
        foreach ($params as $param) {
            try {
                if (method_exists($param, 'getVariableName')) {
                    $typesFromDoc[$param->getVariableName()] = [
                        'name' => (string)$param->getVariableName(),
                        'type' => (string)$param->getType(),
                        'description' => (string)$param->getDescription(),
                        'defaultValue' => null,
                    ];
                }
            } catch (\Exception) {
            }
        }
        try {
            /** @var \PhpParser\Node\Param[] $params */
            $params = $this->getAst()->getParams();
            foreach ($params as $param) {
                if (!$param->var instanceof \PhpParser\Node\Expr\Variable) {
                    continue;
                }

                $type = '';
                $defaultValue = '';
                $annotationType = '';
                $description = '';
                $name = $param->var->name;

                $paramAst = $param->jsonSerialize();
                if ($paramAst['type']) {
                    $type = $this->astPrinter->prettyPrint([$paramAst['type']]);
                    if (str_starts_with($type, '?')) {
                        $type = str_replace('?', '', $type) . '|null';
                    }
                }
                if ($paramAst['default']) {
                    $defaultValue = $this->astPrinter->prettyPrint([$paramAst['default']]);
                    $defaultValue = str_replace('array()', '[]', $defaultValue);
                }
                if (isset($typesFromDoc[$name])) {
                    $annotationType = $typesFromDoc[$name]['type'] ?? '';
                    $type = $type ?: $annotationType;
                    $description = $typesFromDoc[$name]['description'];
                }
                $type = $type ?: 'mixed';
                $expectedType = $type;
                if ($type === 'array' && $isArrayAnnotationType($annotationType)) {
                    $expectedType = $annotationType;
                }

                $defaultValue = str_replace(
                    [
                        $this->configuration->getWorkingDir(),
                        $this->configuration->getProjectRoot(),
                    ],
                    '',
                    $defaultValue
                );
                $parameters[] = [
                    'type' => $this->prepareTypeString($type),
                    'expectedType' => $expectedType,
                    'isVariadic' => $param->variadic,
                    'name' => $name,
                    'defaultValue' => $this->prepareTypeString($defaultValue),
                    'description' => $description,
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $parameters;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    public function getParametersString(): string
    {
        $parameters = [];
        foreach ($this->getParameters() as $parameterData) {
            $variadicPart = ($parameterData['isVariadic'] ?? false) ? '...' : '';
            $parameters[] = "{$parameterData['type']} {$variadicPart}\${$parameterData['name']}" .
                ($parameterData['defaultValue'] ? " = {$parameterData['defaultValue']}" : '');
        }
        return implode(', ', $parameters);
    }

    /**
     * @inheritDoc
     */
    public function isImplementedInParentClass(): bool
    {
        return $this->getImplementingClassName() !== $this->classEntity->getName();
    }

    /**
     * @inheritDoc
     */
    public function getImplementingClassName(): string
    {
        return $this->implementingClassName;
    }

    /**
     * @inheritDoc
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidConfigurationParameterException
     */
    public function isInitialization(): bool
    {
        if ($this->isConstructor()) {
            return true;
        }

        $nameParts = explode('\\', $this->getName());
        $implementingClassShortName = end($nameParts);

        $initializationReturnTypes = [
            'self',
            'static',
            'this',
            $this->getImplementingClassName(),
            $implementingClassShortName,
        ];
        return $this->isStatic() && in_array($this->getReturnType(), $initializationReturnTypes);
    }

    /**
     * @inheritDoc
     */
    public function isDynamic(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function isPublic(): bool
    {
        return $this->getAst()->isPublic();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function isStatic(): bool
    {
        return $this->getAst()->isStatic();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function isProtected(): bool
    {
        return $this->getAst()->isProtected();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function isPrivate(): bool
    {
        return $this->getAst()->isPrivate();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function getStartLine(): int
    {
        return $this->getAst()->getStartLine();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function getStartColumn(): int
    {
        return $this->getAst()->getStartFilePos();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function getEndLine(): int
    {
        return $this->getAst()->getEndLine();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     * @throws ConstExprEvaluationException
     */
    #[CacheableMethod] public function getFirstReturnValue(): mixed
    {
        $nodeFinder = new NodeFinder();
        /** @var Return_|null $firstReturn */
        $firstReturn = $nodeFinder->findFirstInstanceOf($this->getAst()->stmts, Return_::class);
        if (!$firstReturn) {
            return null;
        }
        return NodeValueCompiler::compile($firstReturn->expr, $this);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationParameterException
     */
    #[CacheableMethod] public function getBodyCode(): string
    {
        $stmts = $this->getAst()->getStmts();
        if (!is_array($stmts)) {
            $stmts = [];
        }
        return $this->astPrinter->prettyPrint($stmts);
    }
}