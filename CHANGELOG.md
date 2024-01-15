# [1.0.0-alpha.2](https://github.com/good-php/serialization/compare/v1.0.0-alpha.1...v1.0.0-alpha.2) (2024-01-15)


### Bug Fixes

* Update reflection ([#3](https://github.com/good-php/serialization/issues/3)) ([6964587](https://github.com/good-php/serialization/commit/6964587875149ba289211d248603b1ae917986c5))

# 1.0.0-alpha.1 (2023-11-03)


### Bug Fixes

* Allow default values for typed promoted properties & ignore missing values for nullable properties (set as null by default) ([b145fbf](https://github.com/good-php/serialization/commit/b145fbf644510e98ae18988e8d4aa5355ec12a18))
* Broken caching of type adapters ([9c61d57](https://github.com/good-php/serialization/commit/9c61d57b06f2c8172205abceb208e50e8dd12bdd))
* Broken serializer interface and adapter not found exception ([7da8da8](https://github.com/good-php/serialization/commit/7da8da89f619b96add106661df8feb2088ca5404))
* Default value for promoted property ([10e4598](https://github.com/good-php/serialization/commit/10e45984dbede2d5236366534c0e2523fa7cf4a8))
* Exception message when an error happens for one of the ArrayMapper elements ([d78ae82](https://github.com/good-php/serialization/commit/d78ae82ae96cad706da40e62a0a937ebd7ce89cf))
* Generic type on serializer adapter ([e32d56e](https://github.com/good-php/serialization/commit/e32d56e449780cc45b89ef3e74b27ecba5896781))
* Improper error when serializing a flattened property ([86cd9af](https://github.com/good-php/serialization/commit/86cd9af1a116ad793838998bb6fe90463ee405dd))
* Invalid return type from serializer adapter ([281a8f2](https://github.com/good-php/serialization/commit/281a8f2fb53bdbd1b07e006186bded91ecffecfa))
* Mapper with nullable output type, flattening serializing twice ([d6c65c4](https://github.com/good-php/serialization/commit/d6c65c4f538e66b99872ca3e89b9e32f742fc1f6))
* Missing .releaserc.yml ([ed01bb5](https://github.com/good-php/serialization/commit/ed01bb56637119c858c7274dc6d3699f934647b8))
* PHPStan ([9c8834e](https://github.com/good-php/serialization/commit/9c8834e41fdb9ffed575625af70d1d447bbd41fc))
* PHPStan ([e7776c9](https://github.com/good-php/serialization/commit/e7776c99f7570878ab6b052c2a10882e5a8f341b))
* Serialize empty "map" arrays as objects in JSON ([0de9eca](https://github.com/good-php/serialization/commit/0de9eca17e4e7bf896f0bbb36f28bf0dc34e3b61))
* Use new changed structure of good-php/reflection ([d823db2](https://github.com/good-php/serialization/commit/d823db2dfc36cf72e22cf9cf1c8cbbcc76b82a6b))
* Use serializedName in property mapping exception ([c62274a](https://github.com/good-php/serialization/commit/c62274ad7bf6623e58abc0654a4a510d6ae2c2fa))


### Features

* Allow custom object construction and custom property binds for ClassPropertiesPrimitiveTypeAdapter ([7f96a25](https://github.com/good-php/serialization/commit/7f96a25e69ae88a712a3fa5fce2c6a797fd95e4f))
* Combine all exceptions into one when mapping objects or arrays ([79b95b8](https://github.com/good-php/serialization/commit/79b95b86d2dbc805d05cd6f9d3db6406a4d21302))
* Flattening fields ([0899e7f](https://github.com/good-php/serialization/commit/0899e7f1c4ddc2ac9c54e456542d267f87d71552))
* Improve docs further and refactor internals ([9a624c1](https://github.com/good-php/serialization/commit/9a624c1ba01d2882f38547032c0200c8b262c3c5))
* Initial release ([1e56add](https://github.com/good-php/serialization/commit/1e56add2325035cf3c63c512e52e5850b3f8e7fb))
* Mapping carbon types and allowing partial failure of deserialization ([c8c231c](https://github.com/good-php/serialization/commit/c8c231c92cfb3e0cf3cbcc0d52f018a89b905aaa))
