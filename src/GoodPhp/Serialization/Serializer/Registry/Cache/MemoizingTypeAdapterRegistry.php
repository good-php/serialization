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
use Illuminate\Support\Arr;

final class MemoizingTypeAdapterRegistry implements TypeAdapterRegistry
{
	/** @var array<class-string, array<string, \WeakMap<object, array<int, array{ object[], TypeAdapter }>>>> */
	private array $resolved = [];

	public function __construct(
		private readonly TypeAdapterRegistry $delegate,
	) {
	}

	public function forType(string $typeAdapterType, Serializer $serializer, Type $type, Attributes $attributes = new ArrayAttributes(), TypeAdapterFactory $skipPast = null): TypeAdapter
	{
		$this->resolved[$typeAdapterType][(string) $type] ??= new \WeakMap();

		$matchingFactories = $this->resolved[$typeAdapterType][(string) $type][$skipPast ?? $serializer] ??= [];

		$attributesFactoryPair = Arr::first($matchingFactories, fn (array $pair) => $attributes->allEqual($pair[0]));

		if ($attributesFactoryPair) {
			return $attributesFactoryPair[1];
		}

		$factory = $this->delegate->forType($typeAdapterType, $serializer, $type, $attributes, $skipPast);

		$this->resolved[$typeAdapterType][(string) $type][$skipPast ?? $serializer][] = [$attributes, $factory];

		return $factory;
	}
}
