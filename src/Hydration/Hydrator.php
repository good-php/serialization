<?php

namespace GoodPhp\Serialization\Hydration;

interface Hydrator
{
	public function hydrate(string $className, array $properties): object;
}
