# Good PHP serialization

The concept is similar to Moshi, a Java/Kotlin serialization library - the least effort
without sacrificing customizability, support for different formats or ease of use.

This is what it can serialize and deserialize out-of-the-box:

```php
/**
 * @template T1
 */
class Item
{
	/**
	 * @param BackedEnumStub[] $array
	 * @param Collection<int, T1>
	 * @param T1 $generic
	 * @param NestedGeneric<int, T1> $nested
	 */
	public function __construct(
		// Scalars
		public readonly int $int,
		public readonly float $float,
		public readonly string $string,
		public readonly bool $bool,
		// Nullable and optional values
		public readonly ?string $nullableString,
		public readonly int|null|MissingValue $optional,
		// Custom property names
		#[SerializedName('two')] public readonly string $one,
		// Backed enums
		public readonly BackedEnumStub $backedEnum,
		// Generics and nested objects
		public readonly mixed $generic,
		public readonly NestedGenerics $nestedGeneric,
		// Arrays and Illuminate Collection of any type (with generics!)
		public readonly array $array,
		public readonly Collection $collection,
		// Dates
		public readonly DateTime $dateTime,
		public readonly Carbon $carbon,
	) {}
}
```

You can then convert it into a "primitive" (scalars and arrays of scalars) or JSON:

```php
$primitiveAdapter = $serializer->adapter(
	PrimitiveTypeAdapter::class, 
	NamedType::wrap(Item::class, [Carbon::class])
);
$primitiveAdapter->serialize(new Item(...)) // -> ['int' => 123, ...]

$jsonAdapter = $serializer->adapter(
	JsonTypeAdapter::class, 
	NamedType::wrap(Item::class, [PrimitiveType::int()])
);
$jsonAdapter->deserialize('{"int": 123, ...}') // -> new Item(123, ...)
```

### Custom mappers

Mappers are the simplest form customizing serialization of types. All you have
to do is to mark a method with either `#[MapTo()]` or `#[MapFrom]` attribute,
specify the type in question as first parameter or return type and the serializer
will handle the rest automatically. A single mapper may have as many map methods as you wish.

```php
final class DateTimeMapper
{
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function serialize(DateTime $value): string
	{
		return $value->format(DateTimeInterface::RFC3339_EXTENDED);
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function deserialize(string $value): DateTime
	{
		return new DateTime($value);
	}
}

$serializer = (new SerializerBuilder())
	->addMapperLast(new DateTimeMapper())
	->build();
```

With mappers, you can even handle complex types - such as generics or inheritance:

```php
final class ArrayMapper
{
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function to(array $value, Type $type, Serializer $serializer): array
	{
		$itemAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, $type->arguments[1]);
		
		return array_map(fn ($item) => $itemAdapter->serialize($item), $value);
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function from(array $value, Type $type, Serializer $serializer): array
	{
		$itemAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, $type->arguments[1]);

		return array_map(fn ($item) => $itemAdapter->deserialize($item), $value);
	}
}

final class BackedEnumMapper
{
	#[MapTo(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(BackedEnum::class))]
	public function to(BackedEnum $value): string|int
	{
		return $value->value;
	}
	
	#[MapFrom(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(BackedEnum::class))]
	public function from(string|int $value, Type $type): BackedEnum
	{
		$enumClass = $type->name;
		
		return $enumClass::tryFrom($value);
	}
}
```

## Type adapter factories

Besides type mappers which satisfy most of the needs, you can use type adapter factories
to precisely control how each type is serialized.

The idea is the following: when building a serializer, you add all of the factories you want
to use in order of priority:

```php
(new SerializerBuilder())
	->addMapperLast(new TestMapper()) // then this one
	->addFactoryLast(new TestFactory()) // and this one last
	->addFactory(new TestFactory()) // attempted first
```

A factory has the following signature:

```php
public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter
```

If you return `null`, the next factory is called. Otherwise, the returned type adapter is used.

The serialized is entirely built using type adapter factories. Every type that is
supported out-of-the-box also has it's factory and can be overwritten just by doing
`->addFactoryLast()`. Type mappers are also just fancy adapter factories under the hood.

This is how you can use them:

```php
class NullableTypeAdapterFactory implements TypeAdapterFactory
{
	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NullableType) {
			return null;
		}

		return new NullableTypeAdapter(
			$serializer->adapter($typeAdapterType, $type->innerType, $attributes),
		);
	}
}

class NullableTypeAdapter implements PrimitiveTypeAdapter
{
	public function __construct(
		private readonly PrimitiveTypeAdapter $delegate,
	) {
	}

	public function serialize(mixed $value): mixed
	{
		if ($value === null) {
			return null;
		}

		return $this->delegate->serialize($value);
	}

	public function deserialize(mixed $value): mixed
	{
		if ($value === null) {
			return null;
		}

		return $this->delegate->deserialize($value);
	}
}
```

In this example, `NullableTypeAdapterFactory` handles all nullable types. When a non-nullable
type is given, it returns `null`. That means that the next in "queue" type adapter will be
called. When a nullable is given, it returns a new type adapter instance which has two
methods: `serialize` and `deserialize`. They do exactly what they're called.

## Naming of keys

By default serializer preserves the naming of keys, but this is easily customizable (in order of priority):

- specify a custom property name using the `#[SerializedName]` attribute
- specify a custom naming strategy per class using the `#[SerializedName]` attribute
- specify a custom global naming strategy (use one of the built in or write your own)

Here's an example:

```php
(new SerializerBuilder())->namingStrategy(BuiltInNamingStrategy::SNAKE_CASE);

// Uses snake_case by default
class Item1 {
	public function __construct(
		public int $keyName, // appears as "key_name" in serialized data
		#[SerializedName('second_key')] public int $firstKey, // second_key
		#[SerializedName(BuiltInNamingStrategy::PASCAL_CASE)] public int $thirdKey, // THIRD_KEY
	) {}
}

// Uses PASCAL_CASE by default
#[SerializedName(BuiltInNamingStrategy::PASCAL_CASE)]
class Item2 {
	public function __construct(
		public int $keyName, // KEY_NAME
	) {}
}
```

Out of the box, strategies for `snake_case`, `camelCase` and `PascalCase` are provided,
but you it's trivial to implement your own:

```php
class PrefixedNaming implements NamingStrategy {
	public function __construct(
		private readonly string $prefix,
	) {}
	
	public function translate(PropertyReflection $property): string
	{
		return $this->prefix . $property->name();
	}
}

#[SerializedName(new PrefixedNaming('$'))]
class SiftTrackData {}
```

## Required, nullable, optional and default values

By default if a property is missing in serialized payload:

- nullable properties are just set to null
- properties with a default value - use the default value
- optional properties are set to `MissingValue::INSTANCE`
- any other throw an exception

Here's an example:

```php
class Item {
	public function __construct(
		public ?int $first, // set to null
		public bool $second = true, // set to true
		public Item $third = new Item(...), // set to Item instance
		public int|MissingValue $fourth, // set to MissingValue::INSTANCE
		public int $fifth, // required, throws if missing
	) {}
}

// all keys missing -> throws for 'fifth' property
$adapter->deserialize([])

// only required property -> uses null, default values and optional
$adapter->deserialize(['fifth' => 123]);

// all properties -> fills all values
$adapter->deserialize(['first' => 123, 'second' => false, ...]);
```

## Flattening

Sometimes the same set of keys/types is shared between multiple other models. You could
use inheritance for this, but we believe in composition over inheritance and hence provide
a simple way to achieve the same behaviour without using inheritance:

Here's an example:

```php
class Pagination {
	public function __construct(
		public readonly int $perPage,
		public readonly int $total,
	) {}
}

class UsersPaginatedList {
	public function __construct(
		#[Flatten]
		public readonly Pagination $pagination,
		/** @var User[] */
		public readonly array $users,
	) {}
}

// {"perPage": 25, "total": 100, "users": []}
$adapter->serialize(
	new UsersPaginatedList(
		pagination: new Pagination(25, 100),
		users: [],
	)
);
```

## Error handling

This is expected to be used with client-provided data, so good error descriptions is a must.
These are some of the errors you'll get:

- Expected value of type 'int', but got 'string'
- Expected value of type 'string', but got 'NULL'
- Failed to parse time string (2020 dasd) at position 5 (d): The timezone could not be found in the database
- Expected value of type 'string|int', but got 'boolean'
- Expected one of [one, two], but got 'five'
- Could not map item at key '1': Expected value of type 'string', but got 'NULL'
- Could not map item at key '0': Expected value of type 'string', but got 'NULL' (and 1 more errors)."
- Could not map property at path 'nested.field': Expected value of type 'string', but got 'integer'

All of these are just a chain of PHP exceptions with `previous` exceptions. Besides
those messages, you have all of the thrown exceptions with necessary information.

## More formats

You can add support for more formats as you wish with your own type adapters.
All of the existing adapters are at your disposal:

```php
interface XmlTypeAdapter extends TypeAdapter {}

final class FromPrimitiveXmlTypeAdapter implements XmlTypeAdapter
{
	public function __construct(
		private readonly PrimitiveTypeAdapter $primitiveAdapter,
	) {
	}

	public function serialize(mixed $value): mixed
	{
		return xml_encode($this->primitiveAdapter->serialize($value));
	}

	public function deserialize(mixed $value): mixed
	{
		return $this->primitiveAdapter->deserialize(xml_decode($value));
	}
}
```

## Why this over everything else?

There are some alternatives to this, but all of them will lack at least one of these:

- doesn't rely on inheritance, hence allows serializing third-party classes
- parses existing PHPDoc information instead of duplicating it through attributes
- supports generic types which are extremely useful for wrapper types
- allows simple extension through mappers and complex stuff through type adapters
- produces developer-friendly error messages for invalid data
- correctly handles optional (missing keys) and `null` values as separate concepts
- simple to extend with additional formats
- simple internal structure: no node tree, no value wrappers, no PHP parsing, no inherent limitations
