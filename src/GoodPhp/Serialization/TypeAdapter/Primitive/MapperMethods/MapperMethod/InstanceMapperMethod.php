<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Reflection\FunctionParameterReflection;
use GoodPhp\Reflection\Reflection\MethodReflection;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedTypeException;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\AcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter\MapperMethodsPrimitiveTypeAdapterFactory;
use TypeError;
use Webmozart\Assert\Assert;

final class InstanceMapperMethod implements MapperMethod
{
	public function __construct(
		private readonly MethodReflection $method,
		private readonly object $adapter,
		private readonly Type $methodValueType,
		private readonly AcceptanceStrategy $acceptanceStrategy,
	) {
	}

	public function accepts(NamedType $type, Attributes $attributes, Serializer $serializer): bool
	{
		return $this->acceptanceStrategy->accepts($this->methodValueType, $type, $serializer);
	}

	public function invoke(mixed $value, Type $type, Attributes $attributes, Serializer $serializer, MapperMethodsPrimitiveTypeAdapterFactory $skipPast): mixed
	{
		$map = [
			MapperMethodsPrimitiveTypeAdapterFactory::class => $skipPast,
			Serializer::class                               => $serializer,
			Type::class                                     => $type,
			Attributes::class => $attributes,
		];

		try {
			return $this->method->invoke(
				$this->adapter,
				$value,
				...$this->method
					->parameters()
					->slice(1)
					->map(function (FunctionParameterReflection $parameter) use ($map) {
						$type = $parameter->type();

						Assert::isInstanceOf($type, NamedType::class);
						Assert::keyExists($map, $type->name);

						return $map[$type->name];
					})
			);
		} catch (TypeError $e) {
			if (!str_contains($e->getMessage(), 'Argument #1')) {
				throw $e;
			}

			throw new UnexpectedTypeException($value, $this->method->parameters()->first()->type());
		}
	}
}

