<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\Illuminate;

use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use Illuminate\Support\Collection;

final class CollectionMapper
{
	/**
	 * @template TKey of array-key
	 * @template TValue
	 *
	 * @param Collection<TKey, TValue> $value
	 * @param NamedType                $type
	 *
	 * @return array<TKey, mixed>
	 */
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function to(Collection $value, Type $type, Serializer $serializer): array
	{
		/** @var TypeAdapter<array<TKey, TValue>, array<TKey, mixed>> $arrayAdapter */
		$arrayAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, new NamedType('array', $type->arguments));

		return $arrayAdapter->serialize($value->all());
	}

	/**
	 * @template TKey of array-key
	 *
	 * @param array<TKey, mixed> $value
	 * @param NamedType          $type
	 *
	 * @return Collection<TKey, mixed>
	 */
	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function from(array $value, Type $type, Serializer $serializer): Collection
	{
		/** @var TypeAdapter<array<TKey, mixed>, array<TKey, mixed>> $arrayAdapter */
		$arrayAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, new NamedType('array', $type->arguments));

		return new Collection($arrayAdapter->deserialize($value));
	}
}
