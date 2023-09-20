<?php

namespace GoodPhp\Serialization\TypeAdapter\Registry\Cache;

use Ds\Hashable;
use GoodPhp\Reflection\Reflector\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use RuntimeException;

final class ResolvedKey implements Hashable
{
	public function __construct(
		public readonly string $typeAdapterType,
		public readonly Type $type,
		public readonly Attributes $attributes,
		public readonly ?TypeAdapterFactory $skipPast
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function hash()
	{
		throw new RuntimeException('Not implemented.');
	}

	/**
	 * @inheritDoc
	 *
	 * @param ResolvedKey $obj
	 */
	public function equals($obj): bool
	{
		// Non-strict attributes comparison intended, should be safe.
		/* @noinspection TypeUnsafeComparisonInspection */
		return $this->typeAdapterType === $obj->typeAdapterType &&
			$this->type->equals($obj->type) &&
			$this->attributes->equals($obj->attributes) &&
			$this->skipPast === $obj->skipPast;
	}
}
