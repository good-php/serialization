<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn;

use Exception;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

final class ArrayMapper
{
	/**
	 * @template T
	 *
	 * @param array<T> $value
	 *
	 * @return array<mixed>
	 */
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function to(array $value, Type $type, Serializer $serializer): array
	{
		$itemAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, $type->arguments[1]);

		return MultipleMappingException::map(
			$value,
			false,
			fn (mixed $item, string|int $key) => CollectionItemMappingException::rethrow(
				$key,
				fn () => $itemAdapter->serialize($item)
			)
		);
	}

	/**
	 * @template T
	 *
	 * @param array<mixed> $value
	 *
	 * @return array<T>
	 */
	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function from(array $value, Type $type, Serializer $serializer): array
	{
		$itemAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, $type->arguments[1]);

		return MultipleMappingException::map(
			$value,
			false,
			fn (mixed $item, string|int $key) => CollectionItemMappingException::rethrow(
				$key,
				fn () => $itemAdapter->deserialize($item)
			)
		);
	}
}
