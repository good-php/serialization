<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class SerializedName
{
	public function __construct(public readonly string|NamingStrategy $nameOrStrategy) {}
}
