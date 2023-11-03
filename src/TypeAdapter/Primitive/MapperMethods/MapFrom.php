<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods;

use Attribute;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\AcceptanceStrategy;

#[Attribute(Attribute::TARGET_METHOD)]
final class MapFrom
{
	public function __construct(
		public readonly string $adapterType,
		public readonly ?AcceptanceStrategy $acceptanceStrategy = null,
	) {}
}
