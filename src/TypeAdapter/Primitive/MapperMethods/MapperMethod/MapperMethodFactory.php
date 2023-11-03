<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod;

use GoodPhp\Reflection\Reflection\MethodReflection;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;

interface MapperMethodFactory
{
	public function createTo(
		object $adapter,
		MethodReflection $method,
		MapTo $mapTo,
	): MapperMethod;

	public function createFrom(
		object $adapter,
		MethodReflection $method,
		MapFrom $mapFrom,
	): MapperMethod;
}
