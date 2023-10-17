<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property;

use GoodPhp\Reflection\Reflection\PropertyReflection;
use GoodPhp\Serialization\MissingValue;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedEnumValueException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedValueException;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\MissingValueException;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use Illuminate\Support\Arr;
use Webmozart\Assert\Assert;

/**
 * This handles the built-in default mechanism of binding properties. Specifically, it handles:
 *   - null values
 *   - default property values
 *   - optional values
 *
 * @template T of object
 *
 * @template-implements BoundClassProperty<T>
 */
final class DefaultBoundClassProperty implements BoundClassProperty
{
	public function __construct(
		private readonly PropertyReflection $property,
		private readonly TypeAdapter        $typeAdapter,
		private readonly string             $serializedName,
		private readonly bool               $optional,
		private readonly bool               $hasDefaultValue,
		private readonly bool               $nullable,
		private readonly bool               $useDefaultForUnexpected,
	)
	{
		if ($this->useDefaultForUnexpected) {
			Assert::true($this->hasDefaultValue, "When using #[UseDefaultForUnexpected], the property must have a default value.");
		}
	}

	public function serializedName(): string
	{
		return $this->serializedName;
	}

	/**
	 * @param T $object
	 */
	public function serialize(object $object): array
	{
		$value = $this->property->get($object);

		if ($this->optional && $value === MissingValue::INSTANCE) {
			return [];
		}

		return [
			$this->serializedName => $this->typeAdapter->serialize($value),
		];
	}

	public function deserialize(array $data): array
	{
		if (!Arr::has($data, $this->serializedName)) {
			if ($this->optional) {
				return [
					$this->property->name() => MissingValue::INSTANCE,
				];
			}

			if ($this->hasDefaultValue) {
				return [];
			}

			if ($this->nullable) {
				return [
					$this->property->name() => null,
				];
			}

			throw new MissingValueException();
		}

		try {
			return [
				$this->property->name() => $this->typeAdapter->deserialize($data[$this->serializedName])
			];
		} catch (UnexpectedValueException $e) {
			if ($this->useDefaultForUnexpected) {
				return [];
			}

			throw $e;
		}
	}
}
