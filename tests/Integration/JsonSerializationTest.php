<?php

namespace Tests\Integration;

use Carbon\CarbonImmutable;
use DateMalformedStringException;
use DateTime;
use GoodPhp\Reflection\Type\Combinatorial\UnionType;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\PrimitiveType;
use GoodPhp\Reflection\Type\Special\MixedType;
use GoodPhp\Reflection\Type\Special\NullableType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\MissingValue;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\SerializerBuilder;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedEnumValueException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedPolymorphicTypeException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedTypeException;
use GoodPhp\Serialization\TypeAdapter\Json\JsonTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\PropertyMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\Polymorphic\ClassPolymorphicTypeAdapterFactory;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\BackedEnumStub;
use Tests\Stubs\ClassStub;
use Tests\Stubs\NestedStub;
use Tests\Stubs\Polymorphic\Change;
use Tests\Stubs\Polymorphic\FromToChange;
use Tests\Stubs\Polymorphic\RemovedChange;
use Tests\Stubs\UseDefaultStub;
use Tests\Stubs\ValueEnumStub;
use Throwable;

class JsonSerializationTest extends TestCase
{
	#[DataProvider('serializesProvider')]
	public function testSerializes(string|Type $type, mixed $data, string $expectedSerialized): void
	{
		$adapter = $this->serializer()->adapter(JsonTypeAdapter::class, $type);

		self::assertJsonStringEqualsJsonString($expectedSerialized, $adapter->serialize($data));
	}

	public static function serializesProvider(): iterable
	{
		yield 'int' => [
			'int',
			123,
			<<<'JSON'
				123
				JSON,
		];

		yield 'float' => [
			'float',
			123.45,
			<<<'JSON'
				123.45
				JSON,
		];

		yield 'bool' => [
			'bool',
			true,
			<<<'JSON'
				true
				JSON,
		];

		yield 'string' => [
			'string',
			'text',
			<<<'JSON'
				"text"
				JSON,
		];

		yield 'nullable string' => [
			new NullableType(PrimitiveType::string()),
			'text',
			<<<'JSON'
				"text"
				JSON,
		];

		yield 'nullable string with null value' => [
			new NullableType(PrimitiveType::string()),
			null,
			<<<'JSON'
				null
				JSON,
		];

		yield 'DateTime' => [
			DateTime::class,
			new DateTime('2020-01-01 00:00:00'),
			<<<'JSON'
				"2020-01-01T00:00:00.000000Z"
				JSON,
		];

		yield 'nullable DateTime' => [
			new NullableType(new NamedType(DateTime::class)),
			new DateTime('2020-01-01 00:00:00'),
			<<<'JSON'
				"2020-01-01T00:00:00.000000Z"
				JSON,
		];

		yield 'nullable DateTime with null value' => [
			new NullableType(new NamedType(DateTime::class)),
			null,
			<<<'JSON'
				null
				JSON,
		];

		yield 'backed enum' => [
			BackedEnumStub::class,
			BackedEnumStub::ONE,
			<<<'JSON'
				"one"
				JSON,
		];

		yield 'value enum' => [
			ValueEnumStub::class,
			ValueEnumStub::$ONE,
			<<<'JSON'
				"one"
				JSON,
		];

		yield 'array of DateTime' => [
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			[new DateTime('2020-01-01 00:00:00')],
			<<<'JSON'
				[
					"2020-01-01T00:00:00.000000Z"
				]
				JSON,
		];

		yield 'Collection of DateTime' => [
			new NamedType(Collection::class, [
				PrimitiveType::integer(),
				new NamedType(DateTime::class),
			]),
			new Collection([new DateTime('2020-01-01 00:00:00')]),
			<<<'JSON'
				[
					"2020-01-01T00:00:00.000000Z"
				]
				JSON,
		];

		yield 'ClassStub with all fields' => [
			new NamedType(ClassStub::class, [
				new NamedType(DateTime::class),
			]),
			new ClassStub(
				1,
				new NestedStub(),
				new DateTime('2020-01-01 00:00:00'),
				123,
				123,
				MissingValue::INSTANCE,
				new NestedStub('flattened'),
				new CarbonImmutable('2020-01-01 00:00:00'),
				['Some key' => 'Some value'],
				[
					new FromToChange(from: 'fr', to: 't'),
					new RemovedChange(field: 'avatar'),
				]
			),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {
						"Field": "something"
					},
					"date": "2020-01-01T00:00:00.000000Z",
					"optional": 123,
					"nullable": 123,
					"Field": "flattened",
					"carbonImmutable": "2020-01-01T00:00:00.000000Z",
					"other": {
						"Some key": "Some value"
					},
					"changes": [
						{
							"__typename": "from_to",
							"from": "fr",
							"to": "t"
						},
						{
							"__typename": "removed",
							"field": "avatar"
						}
					]
				}
				JSON,
		];

		yield 'ClassStub with empty optional and null nullable' => [
			new NamedType(
				ClassStub::class,
				[
					new NamedType(DateTime::class),
				]
			),
			new ClassStub(
				1,
				new NestedStub(),
				new DateTime('2020-01-01 00:00:00'),
				MissingValue::INSTANCE,
				null,
				MissingValue::INSTANCE,
				new NestedStub('flattened'),
				new CarbonImmutable('2020-01-01 00:00:00')
			),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {
						"Field": "something"
					},
					"date": "2020-01-01T00:00:00.000000Z",
					"nullable": null,
					"Field": "flattened",
					"carbonImmutable": "2020-01-01T00:00:00.000000Z",
					"other": {},
					"changes": []
				}
				JSON,
		];
	}

	#[DataProvider('deserializesProvider')]
	public function testDeserializes(string|Type $type, mixed $expectedData, string $serialized): void
	{
		$adapter = $this->serializer()->adapter(JsonTypeAdapter::class, $type);

		self::assertEquals($expectedData, $adapter->deserialize($serialized));
	}

	public static function deserializesProvider(): iterable
	{
		yield 'int' => [
			'int',
			123,
			<<<'JSON'
				123
				JSON,
		];

		yield 'float' => [
			'float',
			123.45,
			<<<'JSON'
				123.45
				JSON,
		];

		yield 'float with int value' => [
			'float',
			123.0,
			<<<'JSON'
				123
				JSON,
		];

		yield 'bool' => [
			'bool',
			true,
			<<<'JSON'
				true
				JSON,
		];

		yield 'string' => [
			'string',
			'text',
			<<<'JSON'
				"text"
				JSON,
		];

		yield 'nullable string' => [
			new NullableType(PrimitiveType::string()),
			'text',
			<<<'JSON'
				"text"
				JSON,
		];

		yield 'nullable string with null value' => [
			new NullableType(PrimitiveType::string()),
			null,
			<<<'JSON'
				null
				JSON,
		];

		yield 'DateTime' => [
			DateTime::class,
			new DateTime('2020-01-01 00:00:00'),
			<<<'JSON'
				"2020-01-01T00:00:00.000000Z"
				JSON,
		];

		yield 'nullable DateTime' => [
			new NullableType(new NamedType(DateTime::class)),
			new DateTime('2020-01-01 00:00:00'),
			<<<'JSON'
				"2020-01-01T00:00:00.000000Z"
				JSON,
		];

		yield 'nullable DateTime with null value' => [
			new NullableType(new NamedType(DateTime::class)),
			null,
			<<<'JSON'
				null
				JSON,
		];

		yield 'backed enum' => [
			BackedEnumStub::class,
			BackedEnumStub::ONE,
			<<<'JSON'
				"one"
				JSON,
		];

		yield 'value enum' => [
			ValueEnumStub::class,
			ValueEnumStub::$ONE,
			<<<'JSON'
				"one"
				JSON,
		];

		yield 'array of DateTime' => [
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			[new DateTime('2020-01-01 00:00:00')],
			<<<'JSON'
				[
					"2020-01-01T00:00:00.000000Z"
				]
				JSON,
		];

		yield 'Collection of DateTime' => [
			new NamedType(
				Collection::class,
				[
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				]
			),
			new Collection([new DateTime('2020-01-01 00:00:00')]),
			<<<'JSON'
				[
					"2020-01-01T00:00:00.000000Z"
				]
				JSON,
		];

		yield 'ClassStub with all fields' => [
			new NamedType(
				ClassStub::class,
				[
					new NamedType(DateTime::class),
				]
			),
			new ClassStub(
				1,
				new NestedStub(),
				new DateTime('2020-01-01 00:00:00'),
				123,
				123,
				MissingValue::INSTANCE,
				new NestedStub('flattened'),
				new CarbonImmutable('2020-01-01 00:00:00'),
				['Some key' => 'Some value'],
				[
					new FromToChange(from: 'fr', to: 't'),
					new RemovedChange(field: 'avatar'),
				]
			),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {
						"Field": "something"
					},
					"date": "2020-01-01T00:00:00.000000Z",
					"optional": 123,
					"nullable": 123,
					"Field": "flattened",
					"carbonImmutable": "2020-01-01T00:00:00.000000Z",
					"other": {
						"Some key": "Some value"
					},
					"changes": [
						{
							"__typename": "from_to",
							"from": "fr",
							"to": "t"
						},
						{
							"__typename": "removed",
							"field": "avatar"
						}
					]
				}
				JSON,
		];

		yield 'ClassStub with empty optional and null nullable' => [
			new NamedType(
				ClassStub::class,
				[
					new NamedType(DateTime::class),
				]
			),
			new ClassStub(
				1,
				new NestedStub(),
				new DateTime('2020-01-01 00:00:00'),
				MissingValue::INSTANCE,
				null,
				MissingValue::INSTANCE,
				new NestedStub('flattened'),
				new CarbonImmutable('2020-01-01 00:00:00')
			),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {
						"Field": "something"
					},
					"date": "2020-01-01T00:00:00.000000Z",
					"nullable": null,
					"Field": "flattened",
					"carbonImmutable": "2020-01-01T00:00:00.000000Z"
				}
				JSON,
		];

		yield 'ClassStub with the least default fields' => [
			new NamedType(ClassStub::class, [
				new NamedType(DateTime::class),
			]),
			new ClassStub(
				1,
				new NestedStub(),
				new DateTime('2020-01-01 00:00:00'),
				MissingValue::INSTANCE,
				null,
				MissingValue::INSTANCE,
				new NestedStub(),
				new CarbonImmutable('2020-01-01 00:00:00')
			),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {},
					"date": "2020-01-01T00:00:00.000000Z",
					"carbonImmutable": "2020-01-01T00:00:00.000000Z"
				}
				JSON,
		];

		yield '#[UseDefaultForUnexpected] with unexpected values' => [
			new NamedType(UseDefaultStub::class),
			new UseDefaultStub(),
			<<<'JSON'
				{
					"null": "unknown value",
					"enum": "also unknown"
				}
				JSON,
		];

		yield '#[UseDefaultForUnexpected] with expected values' => [
			new NamedType(UseDefaultStub::class),
			new UseDefaultStub(BackedEnumStub::ONE, BackedEnumStub::TWO),
			<<<'JSON'
				{
					"null": "one",
					"enum": "two"
				}
				JSON,
		];
	}

	#[DataProvider('deserializesWithAnExceptionProvider')]
	public function testDeserializesWithAnException(Throwable $expectedException, string|Type $type, string $serialized): void
	{
		$adapter = $this->serializer()->adapter(JsonTypeAdapter::class, $type);

		try {
			$adapter->deserialize($serialized);

			self::fail('Expected exception to be thrown, got none.');
		} catch (Throwable $e) {
			self::assertEquals($expectedException, $e);
		}
	}

	public static function deserializesWithAnExceptionProvider(): iterable
	{
		yield 'int' => [
			new UnexpectedTypeException('123', PrimitiveType::integer()),
			'int',
			<<<'JSON'
				"123"
				JSON,
		];

		yield 'float' => [
			new UnexpectedTypeException(true, PrimitiveType::float()),
			'float',
			<<<'JSON'
				true
				JSON,
		];

		yield 'bool' => [
			new UnexpectedTypeException(0, PrimitiveType::boolean()),
			'bool',
			<<<'JSON'
				0
				JSON,
		];

		yield 'string' => [
			new UnexpectedTypeException(123, PrimitiveType::string()),
			'string',
			<<<'JSON'
				123
				JSON,
		];

		yield 'null' => [
			new UnexpectedTypeException(null, PrimitiveType::string()),
			'string',
			<<<'JSON'
				null
				JSON,
		];

		yield 'nullable string' => [
			new UnexpectedTypeException(123, PrimitiveType::string()),
			new NullableType(PrimitiveType::string()),
			<<<'JSON'
				123
				JSON,
		];

		if (version_compare(PHP_VERSION, '8.3', '>=')) {
			yield 'DateTime' => [
				new DateMalformedStringException('Failed to parse time string (2020 dasd) at position 5 (d): The timezone could not be found in the database'),
				DateTime::class,
				<<<'JSON'
				"2020 dasd"
				JSON,
			];
		}

		yield 'backed enum type' => [
			new UnexpectedTypeException(true, new UnionType([PrimitiveType::string(), PrimitiveType::integer()])),
			BackedEnumStub::class,
			<<<'JSON'
				true
				JSON,
		];

		yield 'backed enum value' => [
			new UnexpectedEnumValueException('five', ['one', 'two']),
			BackedEnumStub::class,
			<<<'JSON'
				"five"
				JSON,
		];

		yield 'value enum type' => [
			new UnexpectedTypeException(true, new UnionType([PrimitiveType::string(), PrimitiveType::integer()])),
			ValueEnumStub::class,
			<<<'JSON'
				true
				JSON,
		];

		yield 'value enum value' => [
			new UnexpectedEnumValueException('five', ['one', 'two']),
			ValueEnumStub::class,
			<<<'JSON'
				"five"
				JSON,
		];

		if (version_compare(PHP_VERSION, '8.3', '>=')) {
			yield 'array of DateTime #1' => [
				new CollectionItemMappingException(0, new DateMalformedStringException('Failed to parse time string (2020 dasd) at position 5 (d): The timezone could not be found in the database')),
				PrimitiveType::array(
					new NamedType(DateTime::class)
				),
				<<<'JSON'
				["2020 dasd"]
				JSON,
			];
		}

		yield 'array of DateTime #2' => [
			new CollectionItemMappingException(1, new UnexpectedTypeException(null, PrimitiveType::string())),
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			<<<'JSON'
				["2020-01-01T00:00:00.000000Z", null]
				JSON,
		];

		yield 'associative array of DateTime' => [
			new CollectionItemMappingException('nested', new UnexpectedTypeException(null, PrimitiveType::string())),
			PrimitiveType::array(
				new NamedType(DateTime::class),
				PrimitiveType::string(),
			),
			<<<'JSON'
				{
					"nested": null
				}
				JSON,
		];

		yield 'Collection of DateTime #1' => [
			new CollectionItemMappingException(0, new UnexpectedTypeException(null, PrimitiveType::string())),
			new NamedType(
				Collection::class,
				[
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				]
			),
			<<<'JSON'
				[null]
				JSON,
		];

		yield 'Collection of DateTime #2' => [
			new MultipleMappingException([
				new CollectionItemMappingException(0, new UnexpectedTypeException(null, PrimitiveType::string())),
				new CollectionItemMappingException(1, new UnexpectedTypeException(null, PrimitiveType::string())),
			]),
			new NamedType(
				Collection::class,
				[
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				]
			),
			<<<'JSON'
				[null, null]
				JSON,
		];

		yield 'ClassStub with wrong primitive type' => [
			new PropertyMappingException('primitive', new UnexpectedTypeException('1', PrimitiveType::integer())),
			new NamedType(
				ClassStub::class,
				[
					new NamedType(DateTime::class),
				]
			),
			<<<'JSON'
				{
					"primitive": "1",
					"nested": {
						"Field": "something"
					},
					"date": "2020-01-01T00:00:00.000000Z",
					"carbonImmutable": "2020-01-01T00:00:00.000000Z"
				}
				JSON,
		];

		yield 'ClassStub with wrong nested field type' => [
			new PropertyMappingException('nested.Field', new UnexpectedTypeException(123, PrimitiveType::string())),
			new NamedType(
				ClassStub::class,
				[
					new NamedType(DateTime::class),
				]
			),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {
						"Field": 123
					},
					"date": "2020-01-01T00:00:00.000000Z",
					"nullable": null,
					"carbonImmutable": "2020-01-01T00:00:00.000000Z"
				}
				JSON,
		];

		yield 'ClassStub with wrong nested array field type' => [
			new PropertyMappingException('date.0.Field', new UnexpectedTypeException(123, PrimitiveType::string())),
			NamedType::wrap(ClassStub::class, [PrimitiveType::array(NestedStub::class)]),
			<<<'JSON'
				{
					"primitive": 1,
					"nested": {
						"Field": "something"
					},
					"date": [
						{
							"Field": 123
						}
					],
					"nullable": null,
					"carbonImmutable": "2020-01-01T00:00:00.000000Z"
				}
				JSON,
		];

		yield 'non object polymorphic type' => [
			new UnexpectedTypeException('five', PrimitiveType::array(MixedType::get(), PrimitiveType::string())),
			Change::class,
			<<<'JSON'
				"five"
				JSON,
		];

		yield 'polymorphic object without type name field' => [
			new UnexpectedTypeException(null, PrimitiveType::string()),
			Change::class,
			<<<'JSON'
				{
					"field": "avatar"
				}
				JSON,
		];

		yield 'polymorphic object with type name field of invalid type' => [
			new UnexpectedTypeException([
				'not a string for sure',
			], PrimitiveType::string()),
			Change::class,
			<<<'JSON'
				{
					"__typename": ["not a string for sure"],
					"field": "avatar"
				}
				JSON,
		];

		yield 'polymorphic object with a non-existent sub type' => [
			new UnexpectedPolymorphicTypeException(
				'__typename',
				'something_else',
				['from_to', 'removed']
			),
			Change::class,
			<<<'JSON'
				{
					"__typename": "something_else",
					"field": "avatar"
				}
				JSON,
		];
	}

	private function serializer(): Serializer
	{
		return (new SerializerBuilder())
			->addFactoryLast(
				ClassPolymorphicTypeAdapterFactory::for(Change::class)
					->subClass(FromToChange::class, 'from_to')
					->subClass(RemovedChange::class, 'removed')
					->build()
			)
			->build();
	}
}
