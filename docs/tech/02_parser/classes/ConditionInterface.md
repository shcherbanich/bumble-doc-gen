[BumbleDocGen](/docs/README.md) **/**
[Technical description of the project](/docs/tech/readme.md) **/**
[Parser](/docs/tech/02_parser/readme.md) **/**
[Entity filter conditions](/docs/tech/02_parser/entityFilterCondition.md) **/**
ConditionInterface

---


# [ConditionInterface](https://github.com/bumble-tech/bumble-doc-gen/blob/master/src/Core/Parser/FilterCondition/ConditionInterface.php#L9) class:

```php
namespace BumbleDocGen\Core\Parser\FilterCondition;

interface ConditionInterface
```

## Methods

1. [canAddToCollection](#mcanaddtocollection) 

## Methods details:

<a name="mcanaddtocollection" href="#mcanaddtocollection">#</a> `canAddToCollection`  **|** [source code](https://github.com/bumble-tech/bumble-doc-gen/blob/master/src/Core/Parser/FilterCondition/ConditionInterface.php#L11)
```php
public function canAddToCollection(\BumbleDocGen\Core\Parser\Entity\EntityInterface $entity): bool;
```

***Parameters:***

| Name | Type | Description |
|:-|:-|:-|
$entity | [\BumbleDocGen\Core\Parser\Entity\EntityInterface](https://github.com/bumble-tech/bumble-doc-gen/blob/master/src/Core/Parser/Entity/EntityInterface.php) | - |

***Return value:*** [bool](https://www.php.net/manual/en/language.types.boolean.php)

---