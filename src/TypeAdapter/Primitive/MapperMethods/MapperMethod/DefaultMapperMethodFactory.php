<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod;

use GoodPhp\Reflection\Reflection\MethodReflection;
use GoodPhp\Reflection\Reflection\Methods\HasMethods;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\AcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\BaseTypeEqualsAcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use Webmozart\Assert\Assert;

class DefaultMapperMethodFactory implements MapperMethodFactory
{
	public function createTo(object $adapter, MethodReflection $method, MapTo $mapTo): MapperMethod
	{
		Assert::minCount($method->parameters(), 1, 'Mapper method #[MapTo] [' . $method . '] must have at least one parameter.');

		$valueParameter = $method->parameters()[0];

		Assert::notNull($valueParameter->type(), 'Mapper method #[MapTo] [' . $method . '] must have its first parameter type specified.');

		return $this->create(
			$adapter,
			$method,
			$valueParameter->type(),
			$mapTo->acceptanceStrategy,
		);
	}

	public function createFrom(object $adapter, MethodReflection $method, MapFrom $mapFrom): MapperMethod
	{
		Assert::notNull($method->returnType(), 'Mapper method #[MapFrom] [' . $method . '] must have a return type specified.');

		return $this->create(
			$adapter,
			$method,
			$method->returnType(),
			$mapFrom->acceptanceStrategy,
		);
	}

	/**
	 * @template AdapterType of object
	 *
	 * @param AdapterType                                            $adapter
	 * @param MethodReflection<AdapterType, HasMethods<AdapterType>> $method
	 */
	public function create(
		object $adapter,
		MethodReflection $method,
		Type $methodValueType,
		?AcceptanceStrategy $acceptanceStrategy,
	): MapperMethod {
		return new InstanceMapperMethod(
			$method,
			$adapter,
			$methodValueType,
			$acceptanceStrategy ?? BaseTypeEqualsAcceptanceStrategy::get(),
		);
	}
}
