[BumbleDocGen](/docs/README.md) **/**
[Technical description of the project](/docs/tech/readme.md) **/**
[Parser](/docs/tech/02_parser/readme.md) **/**
[Entity filter conditions](/docs/tech/02_parser/entityFilterCondition.md) **/**
TrueCondition

---


# [TrueCondition](https://github.com/bumble-tech/bumble-doc-gen/blob/master/src/Core/Parser/FilterCondition/CommonFilterCondition/TrueCondition.php#L13) class:

```php
namespace BumbleDocGen\Core\Parser\FilterCondition\CommonFilterCondition;

final class TrueCondition implements \BumbleDocGen\Core\Parser\FilterCondition\ConditionInterface
```
True conditions, any object is available

## Methods

1. [canAddToCollection](#mcanaddtocollection) 

## Methods details:

<a name="mcanaddtocollection" href="#mcanaddtocollection">#</a> `canAddToCollection`  **|** [source code](https://github.com/bumble-tech/bumble-doc-gen/blob/master/src/Core/Parser/FilterCondition/CommonFilterCondition/TrueCondition.php#L15)
```php
public function canAddToCollection(\BumbleDocGen\Core\Parser\Entity\EntityInterface $entity): bool;
```

***Parameters:***

| Name | Type | Description |
|:-|:-|:-|
$entity | [\BumbleDocGen\Core\Parser\Entity\EntityInterface](https://github.com/bumble-tech/bumble-doc-gen/blob/master/src/Core/Parser/Entity/EntityInterface.php) | - |

***Return value:*** [bool](https://www.php.net/manual/en/language.types.boolean.php)

---