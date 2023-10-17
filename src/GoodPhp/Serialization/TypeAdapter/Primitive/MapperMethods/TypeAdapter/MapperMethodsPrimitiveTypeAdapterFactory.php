<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod\MapperMethod;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

final class MapperMethodsPrimitiveTypeAdapterFactory implements TypeAdapterFactory
{
	public function __construct(
		/** @var Collection<int, MapperMethod> */
		private readonly Collection $toMappers,
		/** @var Collection<int, MapperMethod> */
		private readonly Collection $fromMappers,
	) {
		Assert::true($this->toMappers->isNotEmpty() || $this->fromMappers->isNotEmpty());
	}

	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NamedType) {
			return null;
		}

		$toMapper = $this->findMapper($this->toMappers, $type, $attributes, $serializer);
		$fromMapper = $this->findMapper($this->fromMappers, $type, $attributes, $serializer);

		if (!$toMapper && !$fromMapper) {
			return null;
		}

		$fallbackDelegate = !$toMapper || !$fromMapper ? $serializer->adapter($typeAdapterType, $type, $attributes, $this) : null;

		return new MapperMethodsPrimitiveTypeAdapter(
			toMapper: $toMapper,
			fromMapper: $fromMapper,
			fallbackDelegate: $fallbackDelegate,
			type: $type,
			serializer: $serializer,
			skipPast: $this,
		);
	}

	/**
	 * @param Collection<int, MapperMethod> $mappers
	 */
	private function findMapper(Collection $mappers, NamedType $type, Attributes $attributes, Serializer $serializer): ?MapperMethod
	{
		return $mappers->first(fn (MapperMethod $method) => $method->accepts($type, $attributes, $serializer));
	}
}
