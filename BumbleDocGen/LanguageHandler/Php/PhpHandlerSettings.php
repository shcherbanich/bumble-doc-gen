<?php

declare(strict_types=1);

namespace BumbleDocGen\LanguageHandler\Php;

use BumbleDocGen\Core\Cache\LocalCache\Exception\ObjectNotFoundException;
use BumbleDocGen\Core\Cache\LocalCache\LocalObjectCache;
use BumbleDocGen\Core\Configuration\ConfigurationParameterBag;
use BumbleDocGen\Core\Configuration\Exception\InvalidConfigurationParameterException;
use BumbleDocGen\Core\Parser\FilterCondition\ConditionInterface;
use BumbleDocGen\Core\Renderer\EntityDocRender\EntityDocRendererInterface;
use BumbleDocGen\Core\Renderer\EntityDocRender\EntityDocRenderersCollection;
use BumbleDocGen\Core\Renderer\Twig\Filter\CustomFilterInterface;
use BumbleDocGen\Core\Renderer\Twig\Filter\CustomFiltersCollection;
use BumbleDocGen\Core\Renderer\Twig\Function\CustomFunctionInterface;
use BumbleDocGen\Core\Renderer\Twig\Function\CustomFunctionsCollection;

final class PhpHandlerSettings
{
    public const SETTINGS_PREFIX = 'language_handlers.php.settings';
    public const DEFAULT_SETTINGS_FILE = __DIR__ . '/phpHandlerDefaultSettings.yaml';

    public function __construct(
        private ConfigurationParameterBag $parameterBag,
        private LocalObjectCache          $localObjectCache
    )
    {
        $parameterBag->addValueFromFileIfNotExists(
            self::SETTINGS_PREFIX,
            self::DEFAULT_SETTINGS_FILE,
        );
    }

    private function getSettingsKey(string $key): string
    {
        return self::SETTINGS_PREFIX . ".{$key}";
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getClassEntityFilter(): ConditionInterface
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        /** @var ConditionInterface $classEntityFilter */
        $classEntityFilter = $this->parameterBag->validateAndGetClassValue(
            $this->getSettingsKey('class_filter'),
            ConditionInterface::class
        );
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $classEntityFilter);
        return $classEntityFilter;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getClassConstantEntityFilter(): ConditionInterface
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        /** @var ConditionInterface $constantEntityFilter */
        $constantEntityFilter = $this->parameterBag->validateAndGetClassValue(
            $this->getSettingsKey('class_constant_filter'),
            ConditionInterface::class
        );
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $constantEntityFilter);
        return $constantEntityFilter;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getMethodEntityFilter(): ConditionInterface
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        /** @var ConditionInterface $methodEntityFilter */
        $methodEntityFilter = $this->parameterBag->validateAndGetClassValue(
            $this->getSettingsKey('method_filter'),
            ConditionInterface::class
        );
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $methodEntityFilter);
        return $methodEntityFilter;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getPropertyEntityFilter(): ConditionInterface
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        /** @var ConditionInterface $propertyEntityFilter */
        $propertyEntityFilter = $this->parameterBag->validateAndGetClassValue(
            $this->getSettingsKey('property_filter'),
            ConditionInterface::class
        );
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $propertyEntityFilter);
        return $propertyEntityFilter;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getEntityDocRendersCollection(): EntityDocRenderersCollection
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        $entityDocRendersCollection = new EntityDocRenderersCollection();
        $entityDocRenders = $this->parameterBag->validateAndGetClassListValue(
            $this->getSettingsKey('doc_renders'),
            EntityDocRendererInterface::class
        );
        foreach ($entityDocRenders as $entityDocRender) {
            $entityDocRendersCollection->add($entityDocRender);
        }
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $entityDocRendersCollection);
        return $entityDocRendersCollection;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getFileSourceBaseUrl(): ?string
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        $fileSourceBaseUrl = $this->parameterBag->validateAndGetStringValue(
            $this->getSettingsKey('file_source_base_url')
        );
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $fileSourceBaseUrl);
        return $fileSourceBaseUrl;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function asyncSourceLoadingEnabled(): bool
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        $asyncSourceLoadingEnabled = $this->parameterBag->validateAndGetBooleanValue(
            $this->getSettingsKey('async_source_loading_enabled')
        );
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $asyncSourceLoadingEnabled);
        return $asyncSourceLoadingEnabled;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getCustomTwigFunctions(): CustomFunctionsCollection
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        $customFunctions = $this->parameterBag->validateAndGetClassListValue(
            $this->getSettingsKey('custom_twig_functions'),
            CustomFunctionInterface::class
        );
        $customFunctionsCollection = new CustomFunctionsCollection();
        foreach ($customFunctions as $customFunction) {
            $customFunctionsCollection->add($customFunction);
        }
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $customFunctionsCollection);
        return $customFunctionsCollection;
    }

    /**
     * @throws InvalidConfigurationParameterException
     */
    public function getCustomTwigFilters(): CustomFiltersCollection
    {
        try {
            return $this->localObjectCache->getMethodCachedResult(__METHOD__, '');
        } catch (ObjectNotFoundException) {
        }
        $customFilters = $this->parameterBag->validateAndGetClassListValue(
            $this->getSettingsKey('custom_twig_filters'),
            CustomFilterInterface::class

        );
        $customFiltersCollection = new CustomFiltersCollection();
        foreach ($customFilters as $customFilter) {
            $customFiltersCollection->add($customFilter);
        }
        $this->localObjectCache->cacheMethodResult(__METHOD__, '', $customFiltersCollection);
        return $customFiltersCollection;
    }
}
