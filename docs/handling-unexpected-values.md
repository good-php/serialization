# Use default for unexpected values

There are situations where you're deserializing data from a third party that doesn't have an API documentation
or one that can't keep a backwards compatibility promise. One such case is when a third party uses an enum
and you expect that new enum values might get added in the future by them. For example, imagine this structure:

```php
enum CardType: string 
{
	case CLUBS = 'clubs';
	case DIAMONDS = 'diamonds';
	case HEARTS = 'hearts'; 
	case SPADES = 'spades';	
}

readonly class Card {
	public function __construct(
		public CardType $type,
		public string $value,
	) {}
}
```

If you get an unexpected value for `type`, you'll get an exception:

```php
// UnexpectedEnumValueException: Expected one of [clubs, diamonds, hearts, spades], but got 'joker'
$adapter->deserialize('{"type": "joker"}');
```

So if you suspect that might happen, add a default value you wish to use (anything) and 
a `#[UseDefaultForUnexpected]` attribute:

```php
readonly class Card {
	public function __construct(
		#[UseDefaultForUnexpected]
		public CardType $type = null,
		// Can be any other valid default value
		#[UseDefaultForUnexpected]
		public CardType $type2 = CardType::SPADES,
	) {}
}
```

Whenever that happens, a default value will be used instead. Optionally, you can also log such cases:

```php
$serializer = (new SerializerBuilder())
	->reportUnexpectedDefault(function (BoundClassProperty $property, UnexpectedValueException $e) {
		$log->warning("Serializer used a default for unexpected value: {$e->getMessage()}", [
			'property' => $property->serializedName(),
			'exception' => $e,
		]);
	})
	->build();
```
