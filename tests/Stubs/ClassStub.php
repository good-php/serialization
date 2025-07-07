<?php

namespace Tests\Stubs;

use Carbon\CarbonImmutable;
use GoodPhp\Serialization\MissingValue;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\SerializedName;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\Flattening\Flatten;
use Tests\Stubs\Polymorphic\Change;

/**
 * @template T
 */
class ClassStub
{
	/**
	 * @param T $generic
	 */
	public function __construct(
		public int $primitive,
		public NestedStub $nested,
		#[SerializedName('date')]
		public mixed $generic,
		public int|MissingValue|null $optional,
		public ?int $nullable,
		public int|MissingValue $nonNullOptional,
		#[Flatten]
		public NestedStub $flattened,
		public readonly CarbonImmutable $carbonImmutable,
		/** @var array<string, string> */
		public readonly array $other = [],
		/** @var list<Change> */
		public readonly array $changes = [],
	) {}
}
