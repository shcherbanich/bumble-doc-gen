<?php

declare(strict_types=1);

namespace SelfDoc\Configuration\Plugin\TwigFunctionClassParser;

use BumbleDocGen\Parser\Entity\ClassEntity;
use BumbleDocGen\Parser\Entity\ClassEntityCollection;
use BumbleDocGen\Plugin\Event\Parser\AfterCreationClassEntityCollection;
use BumbleDocGen\Plugin\Event\Render\OnLoadEntityDocPluginContent;
use BumbleDocGen\Plugin\PluginInterface;
use BumbleDocGen\Render\EntityDocRender\PhpClassToRst\PhpClassToRstDocRender;
use BumbleDocGen\Render\Twig\MainExtension;
use Roave\BetterReflection\Reflector\Reflector;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigFunctionClassParserPlugin implements PluginInterface
{
    private const TWIG_FUNCTION_DIRNAME = '/BumbleDocGen/Render/Twig/Function';
    public const PLUGIN_KEY = 'twigFunctionClassParserPlugin';

    public static function getSubscribedEvents()
    {
        return [
            AfterCreationClassEntityCollection::class => 'afterCreationClassEntityCollection',
            OnLoadEntityDocPluginContent::class => 'onLoadEntityDocPluginContentEvent',
        ];
    }

    public function onLoadEntityDocPluginContentEvent(OnLoadEntityDocPluginContent $event): void
    {
        if (
            $event->getBlockType() !== PhpClassToRstDocRender::BLOCK_AFTER_MAIN_INFO ||
            !$this->isCustomTwigFunction($event->getClassEntity())
        ) {
            return;
        }

        try {
            $pluginResult = $this->getTwig()->render('twigFunctionInfoBlock.twig', [
                'classEntity' => $event->getClassEntity(),
            ]);
        } catch (\Exception) {
            $pluginResult = '';
        }

        $event->addBlockContentPluginResult($pluginResult);
    }

    public function afterCreationClassEntityCollection(AfterCreationClassEntityCollection $event): void
    {
        foreach ($event->getClassEntityCollection() as $classEntity) {
            if ($this->isCustomTwigFunction($classEntity)) {
                $classEntity->loadPluginData(
                    self::PLUGIN_KEY,
                    $this->getFunctionData($event->getClassEntityCollection(), $classEntity->getName())
                );
            }
        }
    }

    private function getTwig(): Environment
    {
        static $twig;
        if (!$twig) {
            $loader = new FilesystemLoader([
                __DIR__ . '/templates/',
            ]);
            $twig = new Environment($loader);
        }
        return $twig;
    }

    private function isCustomTwigFunction(ClassEntity $classEntity): bool
    {
        return str_starts_with($classEntity->getFileName(), self::TWIG_FUNCTION_DIRNAME);
    }

    private function getAllUsedFunctions(Reflector $reflector): array
    {
        static $functions = null;
        if (is_null($functions)) {
            $functions = [];
            $mainExtensionReflection = $reflector->reflectClass(MainExtension::class);
            $bodyCode = $mainExtensionReflection->getMethod('getFunctions')->getBodyCode();
            preg_match_all('/(TwigFunction\(\')(\w+)(.*?)(new )(.*?)(\()/', $bodyCode, $matches);
            foreach ($matches[5] as $k => $match) {
                $functions[$match] = [
                    'name' => $matches[2][$k],
                ];
            }
        }
        return $functions;
    }

    private function getFunctionData(ClassEntityCollection $classEntityCollection, string $className): ?array
    {
        static $functionsData = [];
        $reflector = $classEntityCollection->getReflector();
        if (!array_key_exists($className, $functionsData)) {
            $functions = $this->getAllUsedFunctions($reflector);
            if (!str_starts_with($className, '\\')) {
                $className = "\\{$className}";
            }

            if (!isset($functions[$className])) {
                return null;
            }

            $functionData = $functions[$className];
            $entity = $classEntityCollection->getEntityByClassName($className);
            $method = $entity->getMethodEntityCollection()->get('__invoke');
            $functionData['parameters'] = $method->getParameters();
            $functionsData[$className] = $functionData;
        }
        return $functionsData[$className];
    }
}
