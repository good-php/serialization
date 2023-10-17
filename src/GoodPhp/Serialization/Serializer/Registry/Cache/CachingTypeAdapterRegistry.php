<?php

namespace GoodPhp\Serialization\Serializer\Registry\Cache;

use Ds\Map;
use GoodPhp\Reflection\Reflection\Attributes\ArrayAttributes;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\Serializer\Registry\TypeAdapterRegistry;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class CachingTypeAdapterRegistry implements TypeAdapterRegistry
{
	/** @var Map<ResolvedKey, TypeAdapterFactory> */
	private Map $resolved;

	public function __construct(
		private readonly TypeAdapterRegistry $delegate,
	) {
		$this->resolved = new Map();
	}

	public function forType(string $typeAdapterType, Serializer $serializer, Type $type, Attributes $attributes = new ArrayAttributes(), TypeAdapterFactory $skipPast = null): TypeAdapter
	{
		$key = new ResolvedKey($typeAdapterType, $type, $attributes, $skipPast);

		if ($factory = $this->resolved[$key] ?? null) {
			return $factory;
		}

		return $this->resolved[$key] = $this->delegate->forType($typeAdapterType, $serializer, $type, $attributes, $skipPast);
	}
}
