# Type adapters & factories

Besides type mappers which satisfy most of the needs, you can use type adapter factories
to precisely control how each type is serialized.

The idea is the following: when building a serializer, you add all of the factories you want
to use in order of priority:

```php
(new SerializerBuilder())
	->addMapperLast(new TestMapper()) // #2 - then this one
	->addFactoryLast(new TestFactory()) // #3 - and this one last
	->addFactory(new TestFactory()) // #1 - attempted first
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

## Required, nullable, optional and default values

By default, if a property is missing in serialized payload:

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
