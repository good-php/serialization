<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\Illuminate;

use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use Illuminate\Support\Collection;

final class CollectionMapper
{
	/**
	 * @template TKey of array-key
	 * @template TValue
	 *
	 * @param Collection<TKey, TValue> $value
	 *
	 * @return array<TKey, TValue>
	 */
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function to(Collection $value, Type $type, Serializer $serializer): array
	{
		$arrayAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, new NamedType('array', $type->arguments));

		return $arrayAdapter->serialize($value->toArray());
	}

	/**
	 * @template TKey of array-key
	 * @template TValue
	 *
	 * @param array<TKey, TValue> $value
	 *
	 * @return Collection<TKey, TValue>
	 */
	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function from(array $value, Type $type, Serializer $serializer): Collection
	{
		$arrayAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, new NamedType('array', $type->arguments));

		return new Collection($arrayAdapter->deserialize($value));
	}
}
