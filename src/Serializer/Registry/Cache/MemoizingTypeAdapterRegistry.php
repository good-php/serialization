<?php

namespace GoodPhp\Serialization\Serializer\Registry\Cache;

use GoodPhp\Reflection\Reflection\Attributes\ArrayAttributes;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\Serializer\Registry\TypeAdapterRegistry;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use Illuminate\Support\Arr;
use WeakMap;

final class MemoizingTypeAdapterRegistry implements TypeAdapterRegistry
{
	/** @var array<class-string, array<string, WeakMap<object, array<int, array{ object[], TypeAdapter<mixed, mixed> }>>>> */
	private array $resolved = [];

	public function __construct(
		private readonly TypeAdapterRegistry $delegate,
	) {}

	/**
	 * @template TypeAdapterType of TypeAdapter<mixed, mixed>
	 *
	 * @param class-string<TypeAdapterType>                      $typeAdapterType
	 * @param TypeAdapterFactory<TypeAdapter<mixed, mixed>>|null $skipPast
	 *
	 * @return TypeAdapterType
	 */
	public function forType(string $typeAdapterType, Serializer $serializer, Type $type, Attributes $attributes = new ArrayAttributes(), TypeAdapterFactory $skipPast = null): TypeAdapter
	{
		$this->resolved[$typeAdapterType][(string) $type] ??= new WeakMap();

		$matchingFactories = $this->resolved[$typeAdapterType][(string) $type][$skipPast ?? $serializer] ??= [];

		/** @var array{ object[], TypeAdapter<mixed, mixed> }|null $attributesFactoryPair */
		$attributesFactoryPair = Arr::first($matchingFactories, fn (array $pair) => $attributes->allEqual($pair[0]));

		if ($attributesFactoryPair) {
			/** @var TypeAdapterType */
			return $attributesFactoryPair[1];
		}

		$factory = $this->delegate->forType($typeAdapterType, $serializer, $type, $attributes, $skipPast);

		$this->resolved[$typeAdapterType][(string) $type][$skipPast ?? $serializer][] = [$attributes, $factory];

		return $factory;
	}
}
