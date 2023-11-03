<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property;

use GoodPhp\Reflection\Reflection\Properties\HasProperties;
use GoodPhp\Reflection\Reflection\PropertyReflection;
use GoodPhp\Reflection\Type\Combinatorial\UnionType;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Special\NullableType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\MissingValue;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\Flattening\Flatten;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\Flattening\FlatteningBoundClassProperty;
use Webmozart\Assert\Assert;

class DefaultBoundClassPropertyFactory implements BoundClassPropertyFactory
{
	private readonly NamedType $missingValueType;

	public function __construct()
	{
		$this->missingValueType = new NamedType(MissingValue::class);
	}

	public function create(
		string $typeAdapterType,
		string $serializedName,
		PropertyReflection $property,
		Serializer $serializer
	): BoundClassProperty {
		$type = $property->type();

		Assert::notNull($type, 'Property [' . $property->location() . '] must have it\'s type specified.');

		[$type, $optional] = $this->removeMissingValueType($type, $serializer);

		$typeAdapter = $serializer->adapter($typeAdapterType, $type, $property->attributes());

		if ($property->attributes()->has(Flatten::class)) {
			return new FlatteningBoundClassProperty($property, $typeAdapter);
		}

		return new DefaultBoundClassProperty(
			property: $property,
			typeAdapter: $typeAdapter,
			serializedName: $serializedName,
			optional: $optional,
			hasDefaultValue: $this->hasDefaultValue($property),
			nullable: $type instanceof NullableType,
			useDefaultForUnexpected: $property->attributes()->has(UseDefaultForUnexpected::class),
		);
	}

	/**
	 * Checks if type accepts `MissingValue` special type, and if so - removes it from the union
	 *
	 * For type `MissingValue|int` returns [int, true]
	 * For type `int|null` returns [int|null, false]
	 *
	 * @return array{ Type, bool }
	 */
	private function removeMissingValueType(Type $type, Serializer $serializer): array
	{
		$accepts = $serializer->reflector()
			->typeComparator()
			->accepts($type, $this->missingValueType);

		if ($type instanceof NullableType) {
			$type = $type->traverse(fn (Type $type) => $this->removeMissingValueType($type, $serializer)[0]);

			return [$type, $accepts];
		}

		if (!$accepts || !$type instanceof UnionType) {
			return [$type, false];
		}

		return [$type->withoutType($this->missingValueType), true];
	}

	/**
	 * @param PropertyReflection<object, HasProperties<object>> $property
	 */
	private function hasDefaultValue(PropertyReflection $property): bool
	{
		return $property->hasDefaultValue() || $property->promotedParameter()?->hasDefaultValue();
	}
}
