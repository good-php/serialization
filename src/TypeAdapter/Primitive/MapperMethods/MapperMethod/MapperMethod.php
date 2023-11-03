<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter\MapperMethodsPrimitiveTypeAdapterFactory;

interface MapperMethod
{
	public function accepts(NamedType $type, Attributes $attributes, Serializer $serializer): bool;

	public function invoke(mixed $value, Type $type, Attributes $attributes, Serializer $serializer, MapperMethodsPrimitiveTypeAdapterFactory $skipPast): mixed;
}
