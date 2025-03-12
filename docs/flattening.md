# Flattening

Sometimes the same set of keys/types is shared between multiple other models. You could
use inheritance for this, but we believe in composition over inheritance and hence provide
a simple way to achieve the same behaviour without using inheritance:

To "flatten" a nested variable, use `#[Flatten]` attribute:

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
