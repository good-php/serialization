<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Reflection\FunctionParameterReflection;
use GoodPhp\Reflection\Reflection\MethodReflection;
use GoodPhp\Reflection\Reflection\Methods\HasMethods;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedTypeException;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\AcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter\MapperMethodsPrimitiveTypeAdapterFactory;
use TypeError;
use Webmozart\Assert\Assert;

/**
 * @template AdapterType of object
 */
final class InstanceMapperMethod implements MapperMethod
{
	/**
	 * @param AdapterType                                            $adapter
	 * @param MethodReflection<AdapterType, HasMethods<AdapterType>> $method
	 */
	public function __construct(
		private readonly MethodReflection $method,
		private readonly object $adapter,
		private readonly Type $methodValueType,
		private readonly AcceptanceStrategy $acceptanceStrategy,
	) {}

	public function accepts(NamedType $type, Attributes $attributes, Serializer $serializer): bool
	{
		return $this->acceptanceStrategy->accepts($this->methodValueType, $type, $serializer);
	}

	public function invoke(mixed $value, Type $type, Attributes $attributes, Serializer $serializer, MapperMethodsPrimitiveTypeAdapterFactory $skipPast): mixed
	{
		$injectables = [
			MapperMethodsPrimitiveTypeAdapterFactory::class => $skipPast,
			Serializer::class                               => $serializer,
			Type::class                                     => $type,
			NamedType::class                                => $type,
			Attributes::class                               => $attributes,
		];

		try {
			return $this->method->invoke(
				$this->adapter,
				$value,
				...$this->invokeParameters($injectables),
			);
		} catch (TypeError $e) {
			if (!str_contains($e->getMessage(), 'Argument #1')) {
				throw $e;
			}

			/* @phpstan-ignore-next-line argument.type */
			throw new UnexpectedTypeException($value, $this->method->parameters()[0]->type());
		}
	}

	/**
	 * @param array<class-string, mixed> $injectables
	 *
	 * @return list<mixed>
	 */
	private function invokeParameters(array $injectables): array
	{
		$parameters = array_slice($this->method->parameters(), 1);

		return array_map(function (FunctionParameterReflection $parameter) use ($injectables) {
			$type = $parameter->type();

			Assert::isInstanceOf($type, NamedType::class);
			Assert::keyExists($injectables, $type->name);

			return $injectables[$type->name];
		}, $parameters);
	}
}
