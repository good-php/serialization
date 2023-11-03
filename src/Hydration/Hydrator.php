<?php

namespace GoodPhp\Serialization\Hydration;

interface Hydrator
{
	/**
	 * @template T of object
	 *
	 * @param class-string<T>      $className
	 * @param array<string, mixed> $properties
	 *
	 * @return T
	 */
	public function hydrate(string $className, array $properties): object;
}
