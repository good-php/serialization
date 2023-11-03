<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance;

use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;

class BaseTypeAcceptedByAcceptanceStrategy implements AcceptanceStrategy
{
	public function __construct(
		private readonly string $baseType,
	) {}

	public function accepts(Type $mapperType, Type $type, Serializer $serializer): bool
	{
		return $type instanceof NamedType &&
			($type->name === $this->baseType || is_a($type->name, $this->baseType, true));
	}
}
