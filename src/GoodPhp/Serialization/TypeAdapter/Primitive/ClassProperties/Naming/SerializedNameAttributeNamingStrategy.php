<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming;

use GoodPhp\Reflection\Reflection\PropertyReflection;
use Webmozart\Assert\Assert;

class SerializedNameAttributeNamingStrategy implements NamingStrategy
{
	public function __construct(private readonly NamingStrategy $fallback)
	{
	}

	public function translate(PropertyReflection $property): string
	{
		$serializedName = $this->findSerializedName($property);

		if (!$serializedName) {
			return $this->fallback->translate($property);
		}

		if ($serializedName->nameOrStrategy instanceof NamingStrategy) {
			return $serializedName->nameOrStrategy->translate($property);
		}

		return $serializedName->nameOrStrategy;
	}

	private function findSerializedName(PropertyReflection $property): ?SerializedName
	{
		$serializedName = $property->attributes()->sole(SerializedName::class);

		if (!$serializedName) {
			$serializedName = $property->declaringType()->attributes()->sole(SerializedName::class);

			Assert::nullOrIsInstanceOf(
				$serializedName?->nameOrStrategy,
				NamingStrategy::class,
				'Class applied #[SerializedName] must provide a naming strategy rather than a string name.'
			);
		}

		return $serializedName;
	}
}
