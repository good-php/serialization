<?php

namespace GoodPhp\Serialization\TypeAdapter\Registry\Factory;

use GoodPhp\Reflection\Reflector\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Registry\TypeAdapterNotFoundException;
use GoodPhp\Serialization\TypeAdapter\Registry\TypeAdapterRegistry;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class FactoryTypeAdapterRegistry implements TypeAdapterRegistry
{
	/**
	 * @param TypeAdapterFactory[] $factories
	 */
	public function __construct(
		private readonly array $factories,
	) {
	}

	public function forType(string $typeAdapterType, Serializer $serializer, Type $type, Attributes $attributes = new Attributes(), TypeAdapterFactory $skipPast = null): TypeAdapter
	{
		for (
			$i = $skipPast ? array_search($skipPast, $this->factories, true) + 1 : 0, $total = count($this->factories);
			$i < $total;
			$i++
		) {
			$factory = $this->factories[$i];

			if ($adapter = $factory->create($typeAdapterType, $type, $attributes, $serializer)) {
				return $adapter;
			}
		}

		throw new TypeAdapterNotFoundException($typeAdapterType, $type, $attributes, $skipPast);
	}
}
