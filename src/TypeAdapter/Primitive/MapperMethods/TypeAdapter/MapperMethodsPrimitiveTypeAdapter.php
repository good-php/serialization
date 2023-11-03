<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod\MapperMethod;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use Webmozart\Assert\Assert;

/**
 * @template T
 *
 * @implements PrimitiveTypeAdapter<T>
 */
final class MapperMethodsPrimitiveTypeAdapter implements PrimitiveTypeAdapter
{
	/**
	 * @param PrimitiveTypeAdapter<T>|null $fallbackDelegate
	 */
	public function __construct(
		private readonly ?MapperMethod $toMapper,
		private readonly ?MapperMethod $fromMapper,
		private readonly ?PrimitiveTypeAdapter $fallbackDelegate,
		private readonly Type $type,
		private readonly Attributes $attributes,
		private readonly Serializer $serializer,
		private readonly MapperMethodsPrimitiveTypeAdapterFactory $skipPast,
	) {
		// Make sure there's either both mappers or one of the mappers and a fallback.
		Assert::true($this->toMapper || $this->fallbackDelegate);
		Assert::true($this->fromMapper || $this->fallbackDelegate);
	}

	public function serialize(mixed $value): mixed
	{
		if (!$this->toMapper) {
			Assert::notNull($this->fallbackDelegate);

			return $this->fallbackDelegate->serialize($value);
		}

		return $this->toMapper->invoke($value, $this->type, $this->attributes, $this->serializer, $this->skipPast);
	}

	public function deserialize(mixed $value): mixed
	{
		if (!$this->fromMapper) {
			Assert::notNull($this->fallbackDelegate);

			return $this->fallbackDelegate->deserialize($value);
		}

		return $this->fromMapper->invoke($value, $this->type, $this->attributes, $this->serializer, $this->skipPast);
	}
}
