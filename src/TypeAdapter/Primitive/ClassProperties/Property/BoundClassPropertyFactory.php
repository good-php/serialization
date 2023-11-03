<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property;

use GoodPhp\Reflection\Reflection\Properties\HasProperties;
use GoodPhp\Reflection\Reflection\PropertyReflection;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;

interface BoundClassPropertyFactory
{
	/**
	 * @param class-string<TypeAdapter<mixed, mixed>>           $typeAdapterType
	 * @param PropertyReflection<object, HasProperties<object>> $property
	 *
	 * @return BoundClassProperty<object>
	 */
	public function create(
		string $typeAdapterType,
		string $serializedName,
		PropertyReflection $property,
		Serializer $serializer
	): BoundClassProperty;
}
