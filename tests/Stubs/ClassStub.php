<?php

namespace Tests\Stubs;

use Carbon\CarbonImmutable;
use GoodPhp\Serialization\MissingValue;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\SerializedName;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\Flattening\Flatten;

/**
 * @template T
 */
class ClassStub
{
	/**
	 * @param T $generic
	 */
	public function __construct(
		public int                   $primitive,
		public NestedStub            $nested,
		#[SerializedName('date')]
		public mixed                 $generic,
		public int|null|MissingValue $optional,
		public ?int                  $nullable,
		public int|MissingValue      $nonNullOptional,
		#[Flatten]
		public NestedStub            $flattened,
		public readonly CarbonImmutable $carbonImmutable,
	)
	{
	}
}
