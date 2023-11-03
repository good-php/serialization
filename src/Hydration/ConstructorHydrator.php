<?php

namespace GoodPhp\Serialization\Hydration;

class ConstructorHydrator implements Hydrator
{
	public function hydrate(string $className, array $properties): object
	{
		return new $className(...$properties);
	}
}
