<?php

namespace GoodPhp\Serialization\Serializer\Registry\Factory;

use GoodPhp\Reflection\Reflection\Attributes\ArrayAttributes;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\Serializer\Registry\TypeAdapterNotFoundException;
use GoodPhp\Serialization\Serializer\Registry\TypeAdapterRegistry;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use RuntimeException;
use Webmozart\Assert\Assert;

final class FactoryTypeAdapterRegistry implements TypeAdapterRegistry
{
	/**
	 * @param array<int, TypeAdapterFactory<TypeAdapter<mixed, mixed>>> $factories
	 */
	public function __construct(
		private readonly array $factories,
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
		if ($skipPast) {
			$skipPastIndex = array_search($skipPast, $this->factories, true);

			if ($skipPastIndex === false) {
				throw new RuntimeException('Trying to skip past a factory that was not registered.');
			}
		} else {
			$skipPastIndex = -1;
		}

		for (
			$i = $skipPastIndex + 1, $total = count($this->factories);
			$i < $total;
			$i++
		) {
			$factory = $this->factories[$i];

			if ($adapter = $factory->create($typeAdapterType, $type, $attributes, $serializer)) {
				Assert::isInstanceOf($adapter, $typeAdapterType);

				/** @var TypeAdapterType */
				return $adapter;
			}
		}

		throw new TypeAdapterNotFoundException($typeAdapterType, $type, $attributes, $skipPast);
	}
}
