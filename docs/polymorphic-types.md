# Polymorphic types

When you have a class or an interface that has subclasses, and you want to reflect
that in the serialized format, you can use the built-in polymorphic type adapter.
For example, let's say you have this class structure:

```php
interface Change {}

final class FromToChange implements Change
{
	public function __construct(
		public string $from,
		public string $to,
	) {}
}

final class RemovedChange implements Change
{
	public function __construct(
		public string $field,
	) {}
}
```

To handle that, you'll need to do is register the parent class, along with 
all of its subclasses and their serialized type names. Then, you'll be able
to serialize and deserialize the data, as long as it contains the type name:

```php
$serializer = (new SerializerBuilder())
	->addFactoryLast(
		ClassPolymorphicTypeAdapterFactory::for(Change::class)
			->subClass(FromToChange::class, 'from_to')
			->subClass(RemovedChange::class, 'removed')
			->build()
	)
	->build();

$adapter = $serializer->adapter(JsonTypeAdapter::class, Change::class);

// {"__typename": "from_to", "from": "fr", "to": "t"}
$adapter->serialize(new FromToChange(from: 'fr', to: 't'));

// new FromToChange(from: 'fr', to: 't')
$adapter->deserialize('{"__typename": "from_to", "from": "fr", "to": "t"}');
```

The serializer will use a special field (customizable in #2 argument of `ClassPolymorphicTypeAdapterFactory::for`)
to differentiate between types. Moreover, if you want to handle unexpected polymorphic types,
similarly to enums, you can use `#[UseDefaultForUnexpected]`:

```php
class OtherClass {
	public function __construct(
		#[UseDefaultForUnexpected]
		public readonly ?Change $change = null,
	) {}
}

$adapter = $serializer->adapter(JsonTypeAdapter::class, OtherClass::class);

// new OtherClass(change: null)
$adapter->deserialize('{"__typename": "some_other_unknown_type"}');
```

