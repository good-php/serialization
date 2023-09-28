<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property;

use GoodPhp\Reflection\Reflector\Reflection\PropertyReflection;
use GoodPhp\Serialization\Serializer;

interface BoundClassPropertyFactory
{
	public function create(
		string             $typeAdapterType,
		string             $serializedName,
		PropertyReflection $property,
		Serializer         $serializer
	): BoundClassProperty;
}
