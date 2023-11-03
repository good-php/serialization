<?php

namespace GoodPhp\Serialization\TypeAdapter\MatchingDelegate;

use Closure;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class MatchingDelegateTypeAdapterFactory implements TypeAdapterFactory
{
	/**
	 * @param Type $type
	 * @param TypeAdapter|(Closure(Type, array<object> $attributes, Serializer $serializer): TypeAdapter) $adapter
	 */
	public function __construct(
		private readonly string $typeAdapterType,
		private readonly Type $type,
		private readonly string $attribute,
		private readonly TypeAdapter|Closure $adapter,
	) {
	}

	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter
	{
		if ($typeAdapterType !== $this->typeAdapterType) {
			return null;
		}

		if (!$serializer->reflector()->typeComparator()->accepts($this->type, $type)) {
			return null;
		}

		if (!$attributes->has($this->attribute)) {
			return null;
		}

		return $this->adapter instanceof Closure ? ($this->adapter)($type, $attributes, $serializer) : $this->adapter;
	}
}
