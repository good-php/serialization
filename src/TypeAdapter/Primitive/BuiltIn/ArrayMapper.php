<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn;

use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\PrimitiveType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use stdClass;

final class ArrayMapper
{
	/**
	 * @template T
	 *
	 * @param array<T>  $value
	 * @param NamedType $type
	 *
	 * @return array<mixed>
	 */
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function to(array $value, Type $type, Serializer $serializer): array|stdClass
	{
		if ($value === []) {
			// Make sure that map arrays are serialized as object. To do so, we'll return an stdClass instead of
			// an array so the upper layer can serialize it as an object, not an empty array.
			if ($type->arguments[0]->equals(PrimitiveType::string())) {
				return new stdClass();
			}

			return $value;
		}

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
	 * @param array<mixed> $value
	 * @param NamedType    $type
	 *
	 * @return array<mixed>
	 */
	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function from(array $value, Type $type, Serializer $serializer): array
	{
		if ($value === []) {
			return $value;
		}

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
