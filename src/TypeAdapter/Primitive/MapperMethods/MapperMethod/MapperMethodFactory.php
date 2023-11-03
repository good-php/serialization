<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod;

use GoodPhp\Reflection\Reflection\MethodReflection;
use GoodPhp\Reflection\Reflection\Methods\HasMethods;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;

interface MapperMethodFactory
{
	/**
	 * @template AdapterType of object
	 *
	 * @param AdapterType                                            $adapter
	 * @param MethodReflection<AdapterType, HasMethods<AdapterType>> $method
	 */
	public function createTo(
		object $adapter,
		MethodReflection $method,
		MapTo $mapTo,
	): MapperMethod;

	/**
	 * @template AdapterType of object
	 *
	 * @param AdapterType                                            $adapter
	 * @param MethodReflection<AdapterType, HasMethods<AdapterType>> $method
	 */
	public function createFrom(
		object $adapter,
		MethodReflection $method,
		MapFrom $mapFrom,
	): MapperMethod;
}
