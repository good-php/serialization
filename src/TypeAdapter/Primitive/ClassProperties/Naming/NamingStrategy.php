<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming;

use GoodPhp\Reflection\Reflection\Properties\HasProperties;
use GoodPhp\Reflection\Reflection\PropertyReflection;

interface NamingStrategy
{
	/**
	 * @param PropertyReflection<object, HasProperties<object>> $property
	 */
	public function translate(PropertyReflection $property): string;
}
