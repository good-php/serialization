<?php

namespace GoodPhp\Serialization\Serializer;

use GoodPhp\Reflection\Reflection\Attributes\ArrayAttributes;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Reflector;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\Serializer\Registry\TypeAdapterRegistry;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class TypeAdapterRegistrySerializer implements Serializer
{
	public function __construct(
		private readonly TypeAdapterRegistry $typeAdapterRegistry,
		private readonly Reflector $reflector,
	) {}

	public function adapter(string $typeAdapterType, Type|string $type, Attributes $attributes = new ArrayAttributes(), ?TypeAdapterFactory $skipPast = null): TypeAdapter
	{
		if (is_string($type)) {
			$type = new NamedType($type);
		}

		return $this->typeAdapterRegistry->forType($typeAdapterType, $this, $type, $attributes, $skipPast);
	}

	public function reflector(): Reflector
	{
		return $this->reflector;
	}
}
