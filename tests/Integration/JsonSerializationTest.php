<?php

namespace Tests\Integration;

use Carbon\CarbonImmutable;
use DateTime;
use Exception;
use GoodPhp\Reflection\Type\Combinatorial\UnionType;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\PrimitiveType;
use GoodPhp\Reflection\Type\Special\NullableType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\MissingValue;
use GoodPhp\Serialization\SerializerBuilder;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedEnumValueException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedTypeException;
use GoodPhp\Serialization\TypeAdapter\Json\JsonTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\PropertyMappingException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\BackedEnumStub;
use Tests\Stubs\ClassStub;
use Tests\Stubs\NestedStub;
use Tests\Stubs\ValueEnumStub;
use Throwable;

class JsonSerializationTest extends TestCase
{
	/**
	 * @dataProvider serializesProvider
	 */
	public function testSerializes(string|Type $type, mixed $data, string $expectedSerialized): void
	{
		$adapter = (new SerializerBuilder())
			->build()
			->adapter(JsonTypeAdapter::class, $type);

		self::assertSame($expectedSerialized, $adapter->serialize($data));
	}

	public static function serializesProvider(): iterable
	{
		yield 'int' => [
			'int',
			123,
			'123',
		];

		yield 'float' => [
			'float',
			123.45,
			'123.45',
		];

		yield 'bool' => [
			'bool',
			true,
			'true',
		];

		yield 'string' => [
			'string',
			'text',
			'"text"',
		];

		yield 'nullable string' => [
			new NullableType(PrimitiveType::string()),
			'text',
			'"text"',
		];

		yield 'nullable string with null value' => [
			new NullableType(PrimitiveType::string()),
			null,
			'null',
		];

		yield 'DateTime' => [
			DateTime::class,
			new DateTime('2020-01-01 00:00:00'),
			'"2020-01-01T00:00:00.000000Z"',
		];

		yield 'nullable DateTime' => [
			new NullableType(new NamedType(DateTime::class)),
			new DateTime('2020-01-01 00:00:00'),
			'"2020-01-01T00:00:00.000000Z"',
		];

		yield 'nullable DateTime with null value' => [
			new NullableType(new NamedType(DateTime::class)),
			null,
			'null',
		];

		yield 'backed enum' => [
			BackedEnumStub::class,
			BackedEnumStub::ONE,
			'"one"',
		];

		yield 'value enum' => [
			ValueEnumStub::class,
			ValueEnumStub::$ONE,
			'"one"',
		];

		yield 'array of DateTime' => [
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			[new DateTime('2020-01-01 00:00:00')],
			'["2020-01-01T00:00:00.000000Z"]',
		];

		yield 'Collection of DateTime' => [
			new NamedType(
				Collection::class,
				new Collection([
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				])
			),
			new Collection([new DateTime('2020-01-01 00:00:00')]),
			'["2020-01-01T00:00:00.000000Z"]',
		];

		yield 'ClassStub with all fields' => [
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
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
				['Some key' => 'Some value']
			),
			'{"primitive":1,"nested":{"Field":"something"},"date":"2020-01-01T00:00:00.000000Z","optional":123,"nullable":123,"Field":"flattened","carbonImmutable":"2020-01-01T00:00:00.000000Z","other":{"Some key":"Some value"}}',
		];

		yield 'ClassStub with empty optional and null nullable' => [
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
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
			'{"primitive":1,"nested":{"Field":"something"},"date":"2020-01-01T00:00:00.000000Z","nullable":null,"Field":"flattened","carbonImmutable":"2020-01-01T00:00:00.000000Z","other":{}}',
		];
	}

	/**
	 * @dataProvider deserializesProvider
	 */
	public function testDeserializes(string|Type $type, mixed $expectedData, string $serialized): void
	{
		$adapter = (new SerializerBuilder())
			->build()
			->adapter(JsonTypeAdapter::class, $type);

		self::assertEquals($expectedData, $adapter->deserialize($serialized));
	}

	public static function deserializesProvider(): iterable
	{
		yield 'int' => [
			'int',
			123,
			'123',
		];

		yield 'float' => [
			'float',
			123.45,
			'123.45',
		];

		yield 'float with int value' => [
			'float',
			123.0,
			'123',
		];

		yield 'bool' => [
			'bool',
			true,
			'true',
		];

		yield 'string' => [
			'string',
			'text',
			'"text"',
		];

		yield 'nullable string' => [
			new NullableType(PrimitiveType::string()),
			'text',
			'"text"',
		];

		yield 'nullable string with null value' => [
			new NullableType(PrimitiveType::string()),
			null,
			'null',
		];

		yield 'DateTime' => [
			DateTime::class,
			new DateTime('2020-01-01 00:00:00'),
			'"2020-01-01T00:00:00.000000Z"',
		];

		yield 'nullable DateTime' => [
			new NullableType(new NamedType(DateTime::class)),
			new DateTime('2020-01-01 00:00:00'),
			'"2020-01-01T00:00:00.000000Z"',
		];

		yield 'nullable DateTime with null value' => [
			new NullableType(new NamedType(DateTime::class)),
			null,
			'null',
		];

		yield 'backed enum' => [
			BackedEnumStub::class,
			BackedEnumStub::ONE,
			'"one"',
		];

		yield 'value enum' => [
			ValueEnumStub::class,
			ValueEnumStub::$ONE,
			'"one"',
		];

		yield 'array of DateTime' => [
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			[new DateTime('2020-01-01 00:00:00')],
			'["2020-01-01T00:00:00.000000Z"]',
		];

		yield 'Collection of DateTime' => [
			new NamedType(
				Collection::class,
				new Collection([
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				])
			),
			new Collection([new DateTime('2020-01-01 00:00:00')]),
			'["2020-01-01T00:00:00.000000Z"]',
		];

		yield 'ClassStub with all fields' => [
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
			),
			new ClassStub(
				1,
				new NestedStub(),
				new DateTime('2020-01-01 00:00:00'),
				123,
				123,
				MissingValue::INSTANCE,
				new NestedStub('flattened'),
				new CarbonImmutable('2020-01-01 00:00:00')
			),
			'{"primitive":1,"nested":{"Field":"something"},"date":"2020-01-01T00:00:00.000000Z","optional":123,"nullable":123,"Field":"flattened","carbonImmutable":"2020-01-01T00:00:00.000000Z"}',
		];

		yield 'ClassStub with empty optional and null nullable' => [
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
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
			'{"primitive":1,"nested":{"Field":"something"},"date":"2020-01-01T00:00:00.000000Z","nullable":null,"Field":"flattened","carbonImmutable":"2020-01-01T00:00:00.000000Z"}',
		];

		yield 'ClassStub with the least default fields' => [
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
			),
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
			'{"primitive":1,"nested":{},"date":"2020-01-01T00:00:00.000000Z","carbonImmutable":"2020-01-01T00:00:00.000000Z"}',
		];
	}

	/**
	 * @dataProvider deserializesWithAnExceptionProvider
	 */
	public function testDeserializesWithAnException(Throwable $expectedException, string|Type $type, string $serialized): void
	{
		$adapter = (new SerializerBuilder())
			->build()
			->adapter(JsonTypeAdapter::class, $type);

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
			'"123"',
		];

		yield 'float' => [
			new UnexpectedTypeException(true, PrimitiveType::float()),
			'float',
			'true',
		];

		yield 'bool' => [
			new UnexpectedTypeException(0, PrimitiveType::boolean()),
			'bool',
			'0',
		];

		yield 'string' => [
			new UnexpectedTypeException(123, PrimitiveType::string()),
			'string',
			'123',
		];

		yield 'null' => [
			new UnexpectedTypeException(null, PrimitiveType::string()),
			'string',
			'null',
		];

		yield 'nullable string' => [
			new UnexpectedTypeException(123, PrimitiveType::string()),
			new NullableType(PrimitiveType::string()),
			'123',
		];

		yield 'DateTime' => [
			new Exception('Failed to parse time string (2020 dasd) at position 5 (d): The timezone could not be found in the database'),
			DateTime::class,
			'"2020 dasd"',
		];

		yield 'backed enum type' => [
			new UnexpectedTypeException(true, new UnionType(new Collection([PrimitiveType::string(), PrimitiveType::integer()]))),
			BackedEnumStub::class,
			'true',
		];

		yield 'backed enum value' => [
			new UnexpectedEnumValueException('five', ['one', 'two']),
			BackedEnumStub::class,
			'"five"',
		];

		yield 'value enum type' => [
			new UnexpectedTypeException(true, new UnionType(new Collection([PrimitiveType::string(), PrimitiveType::integer()]))),
			ValueEnumStub::class,
			'true',
		];

		yield 'value enum value' => [
			new UnexpectedEnumValueException('five', ['one', 'two']),
			ValueEnumStub::class,
			'"five"',
		];

		yield 'array of DateTime #1' => [
			new CollectionItemMappingException(0, new Exception('Failed to parse time string (2020 dasd) at position 5 (d): The timezone could not be found in the database')),
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			'["2020 dasd"]',
		];

		yield 'array of DateTime #2' => [
			new CollectionItemMappingException(1, new UnexpectedTypeException(null, PrimitiveType::string())),
			PrimitiveType::array(
				new NamedType(DateTime::class)
			),
			'["2020-01-01T00:00:00.000000Z", null]',
		];

		yield 'associative array of DateTime' => [
			new CollectionItemMappingException('nested', new UnexpectedTypeException(null, PrimitiveType::string())),
			PrimitiveType::array(
				new NamedType(DateTime::class),
				PrimitiveType::string(),
			),
			'{"nested": null}',
		];

		yield 'Collection of DateTime #1' => [
			new CollectionItemMappingException(0, new UnexpectedTypeException(null, PrimitiveType::string())),
			new NamedType(
				Collection::class,
				new Collection([
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				])
			),
			'[null]',
		];

		yield 'Collection of DateTime #2' => [
			new MultipleMappingException([
				new CollectionItemMappingException(0, new UnexpectedTypeException(null, PrimitiveType::string())),
				new CollectionItemMappingException(1, new UnexpectedTypeException(null, PrimitiveType::string())),
			]),
			new NamedType(
				Collection::class,
				new Collection([
					PrimitiveType::integer(),
					new NamedType(DateTime::class),
				])
			),
			'[null, null]',
		];

		yield 'ClassStub with wrong primitive type' => [
			new PropertyMappingException('primitive', new UnexpectedTypeException('1', PrimitiveType::integer())),
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
			),
			'{"primitive":"1","nested":{"Field":"something"},"date":"2020-01-01T00:00:00.000000Z","carbonImmutable":"2020-01-01T00:00:00.000000Z"}',
		];

		yield 'ClassStub with wrong nested field type' => [
			new PropertyMappingException('nested.Field', new UnexpectedTypeException(123, PrimitiveType::string())),
			new NamedType(
				ClassStub::class,
				new Collection([
					new NamedType(DateTime::class),
				])
			),
			'{"primitive":1,"nested":{"Field":123},"date":"2020-01-01T00:00:00.000000Z","nullable":null,"carbonImmutable":"2020-01-01T00:00:00.000000Z"}',
		];

		yield 'ClassStub with wrong nested array field type' => [
			new PropertyMappingException('date.0.Field', new UnexpectedTypeException(123, PrimitiveType::string())),
			NamedType::wrap(ClassStub::class, [PrimitiveType::array(NestedStub::class)]),
			'{"primitive":1,"nested":{"Field":"something"},"date":[{"Field":123}],"nullable":null,"carbonImmutable":"2020-01-01T00:00:00.000000Z"}',
		];
	}
}
