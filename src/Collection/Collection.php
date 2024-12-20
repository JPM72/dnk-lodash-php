<?php

namespace dnk;

class Coll extends dnk
{
	private static function castCollectionToArray($collection)
	{
		if (\is_array($collection) || $collection instanceof \Countable)
		{
			return $collection;
		}

		if ($collection instanceof \Traversable)
		{
			return \iterator_to_array($collection);
		}

		if (\is_object($collection))
		{
			return \get_object_vars($collection);
		}

		return Arr::castArray($collection);
	}

	protected static function createAggregator($setter, $initializer = null)
	{
		return function ($collection, $iteratee) use ($setter, $initializer)
		{
			$accumulator = null !== $initializer ? $initializer() : [];

			$func = function (
				$collection,
				$setter,
				&$accumulator,
				$iteratee
			)
			{
				Coll::each(
					$collection,
					function ($value, $key, $collection) use ($setter, &$accumulator, $iteratee)
					{
						$accumulator = $setter(
							$accumulator,
							$value,
							$iteratee($value),
							$collection
						);
					}
				);

				return $accumulator;
			};

			return $func(
				$collection,
				$setter,
				$accumulator,
				dnk::baseIteratee($iteratee)
			);
		};
	}

	protected static function baseReduce(
		iterable $array,
		callable $iteratee,
		$accumulator,
		$initAccum = null
	)
	{
		$length = \is_array($array) || $array instanceof \Countable ? \count($array) : 0;

		if ($initAccum && $length)
		{
			$accumulator = \current($array);
		}

		foreach ($array as $key => $value)
		{
			$accumulator = $iteratee($accumulator, $value, $key, $array);
		}

		return $accumulator;
	}

	public static function alias($collection, array $keyMap, $include = true): array
	{
		$keyMap = Arr::unpack($keyMap);
		$arr = [];
		foreach ($keyMap as $key => $alias)
		{
			$arr = self::set(
				$arr,
				$alias,
				self::get($collection, $key)
			);
		}
		if ($include)
		{
			$arr = Arr::defaultsDeep($arr, $collection);
		}
		return $arr;
	}

	public static function at($collection, ...$paths): array
	{
		return self::map(
			Arr::flatten($paths),
			fn ($path) => self::get($collection, $path)
		);
	}

	public static function cascadeKey($collection, ...$paths)
	{
		return self::find(
			Arr::flatten($paths),
			fn ($p) => self::get($collection, $p)
		);
	}

	/** return first value that has an existing path */
	public static function cascadeProp($collection, ...$paths)
	{
		$path = self::cascadePropKey($collection, ...$paths);
		if (\is_null($path)) return null;

		return self::get($collection, $path);
	}

	/** return first path that exists */
	public static function cascadePropKey($collection, ...$paths)
	{
		$paths = Arr::flatten($paths);

		foreach ($paths as $p)
		{
			if (self::has($collection, $p)) return $p;
		}
		return null;
	}

	public static function countBy(iterable $collection, callable $iteratee): array
	{
		return self::createAggregator(function ($result, $key, $value)
		{
			$result[$value] ??= 0;

			$result[$value]++;

			return $result;
		})($collection, $iteratee);
	}

	/**
	 * Creates a function that invokes the predicate
	 * properties of `source` with the corresponding
	 * property values of a given object, returning
	 * true if all predicates return truthy, else false.
	 *
	 * @param  iterable|array   $source
	 * @return callable
	 */
	public static function conforms($source)
	{
		$unpacked = Arr::unpack(
			self::castCollectionToArray($source)
		);

		$options = [];

		foreach ($unpacked as $key => $func)
		{
			$options[$key] = fn (...$args) => $func(...$args);
		}

		return function ($object) use ($options)
		{
			$result = true;

			foreach ($options as $key => $func)
			{
				$srcProperty = static::get(
					$object,
					$key,
					null
				);

				$result = $result && $func($srcProperty);
				if (!$result) return false;
			}

			return true;
		};
	}

	/**
	 * Checks if `collection` conforms to `source` by
	 * invoking the predicate properties of `source`
	 * with the corresponding property
	 * values of `collection`.
	 *
	 * @param  object|iterable|array   $collection
	 * @return bool
	 */
	public static function conformsTo($collection, $source)
	{
		return self::conforms($source)($collection);
	}

	public static function each($collection, callable $func)
	{
		return self::forEach($collection, $func);
	}

	public static function every($collection, $predicate = null)
	{
		if (!\is_iterable($collection) || !self::size($collection)) return false;

		$predicate ??= static::getIdentityFunction();
		$iterator = dnk::baseIteratee($predicate);

		foreach ($collection as $key => $value)
		{
			if (!$iterator($value, $key, $collection))
			{
				return false;
			}
		}
		return true;
	}

	public static function get($object, $path, $defaultValue = null)
	{
		if (\is_array($object))
		{
			if ($object && $path && (\is_string($path) || \is_int($path)) && ($object[$path] ?? null))
			{
				return $object[$path];
			}

			return Arr::get($object, $path, $defaultValue);
		}
		return Propper::get($object, $path, $defaultValue);
	}

	public static function groupBy(iterable $collection, $iteratee): array
	{
		return self::createAggregator(function ($result, $value, $key)
		{
			$result[$key] ??= [];

			$result[$key][] = $value;

			return $result;
		})($collection, $iteratee);
	}

	public static function invokeMap(iterable $collection, $path, array $args = []): array
	{
		return dnk::baseRest(function ($collection, $path, $args)
		{
			$isFunc = \is_callable($path);
			$result = [];

			self::forEach($collection, function ($value, $key) use (&$result, $isFunc, $path, $args)
			{
				$result[$key] = $isFunc ? $path($value, ...$args) : Propper::invoke($value, $path, ...$args);
			});

			return $result;
		})($collection, $path, ...$args);
	}

	protected static function baseHas($collection, $key)
	{
		return \is_object($collection)
			? Propper::hasProperty($collection, $key)
			: (
				\is_array($collection)
				? Arr::hasKey(
					self::castCollectionToArray($collection),
					$key
				)
				: false
			);
	}

	public static function has($collection, $key)
	{
		$path = static::toPath($key);

		$c = $collection;

		while (\count($path))
		{
			$k = \array_shift($path);
			if (!$k || !self::baseHas($c, $k)) return false;
			$c = self::get($c, $k);
		}
		return true;
	}

	public static function isEmpty($collection = null): bool
	{
		return is_null($collection) || self::size($collection) === 0;
	}

	public static function keyBy(iterable $collection, $iteratee): array
	{
		return self::createAggregator(function ($result, $value, $key)
		{
			$result[$key] = $value;

			return $result;
		})($collection, $iteratee);
	}

	public static function keys($collection)
	{
		return \array_keys(
			self::castCollectionToArray($collection)
		);
	}

	public static function filter(
		iterable $array,
		$predicate = null,
		$preserveKeys = null
	)
	{
		$callable = dnk::baseIteratee($predicate);

		$collection = \is_array($array) ? $array : \iterator_to_array($array);

		$result = \array_filter(
			$collection,
			function ($value, $key) use ($array, $callable)
			{
				return $callable($value, $key, $array);
			},
			\ARRAY_FILTER_USE_BOTH
		);

		$preserveKeys ??= \is_array($array) ? false : true;

		return $preserveKeys ? $result : \array_values($result);
	}

	public static function find(
		iterable $collection,
		$predicate = null,
		int $startIndex = 0
	)
	{
		$callable = dnk::baseIteratee($predicate);

		$collection = \is_array($collection) ? $collection : \iterator_to_array($collection);
		$collection = \array_slice($collection, $startIndex);

		foreach ($collection as $key => $value)
		{
			if ($callable($value, $key, $collection)) return $value;
		}

		return null;
	}

	public static function findKey(iterable $collection, $predicate = null)
	{
		if (\is_array($collection) && Arr::isList($collection)) return Arr::findIndex($collection, $predicate);

		$length = \count($collection);
		if (!$length)
		{
			return -1;
		}

		$iteratee = dnk::baseIteratee($predicate);

		foreach ($collection as $key => $value)
		{
			if ($iteratee($value, $key, $collection))
			{
				return $key;
			}
		}

		return null;
	}

	public static function flatMap(iterable $collection, callable $iteratee): array
	{
		return Arr::flatten(self::map($collection, $iteratee), 1);
	}
	public static function flatMapDeep(iterable $collection, callable $iteratee): array
	{
		return Arr::flatten(self::map($collection, $iteratee), \PHP_INT_MAX);
	}
	public static function flatMapDepth(iterable $collection, callable $iteratee, int $depth = 1): array
	{
		return Arr::flatten(self::map($collection, $iteratee), $depth);
	}

	public static function forEach($collection, callable $func)
	{
		$values = \is_object($collection) ? \get_object_vars($collection) : $collection;

		/** @var array $values */
		foreach ($values as $index => $value)
		{
			if ($func($value, $index, $collection) === false)
			{
				break;
			}
		}

		return $collection;
	}

	public static function map($collection, $func)
	{
		$values = [];

		if (\is_array($collection))
		{
			$values = $collection;
		}
		elseif ($collection instanceof \Traversable)
		{
			$values = \iterator_to_array($collection);
		}
		elseif (\is_object($collection))
		{
			$values = \get_object_vars($collection);
		}

		$callable = dnk::baseIteratee($func);

		return \array_map(function ($value, $index) use ($callable, $collection)
		{
			return $callable($value, $index, $collection);
		}, $values, \array_keys($values));
	}

	public static function mapKeys($collection, $func)
	{
		$callable = dnk::baseIteratee($func);

		$values = [];

		if (\is_array($collection))
		{
			$values = $collection;
		}
		elseif ($collection instanceof \Traversable)
		{
			$values = \iterator_to_array($collection);
		}
		elseif (\is_object($collection))
		{
			$values = \get_object_vars($collection);
		}

		$mapped = [];

		foreach ($values as $key => $value)
		{
			$k = $callable($value, $key, $values);
			$mapped[$k] = $value;
		}
		return $mapped;
	}

	public static function mapValues($collection, $func)
	{
		$callable = dnk::baseIteratee($func);

		$values = [];

		if (\is_array($collection))
		{
			$values = $collection;
		}
		elseif ($collection instanceof \Traversable)
		{
			$values = \iterator_to_array($collection);
		}
		elseif (\is_object($collection))
		{
			$values = \get_object_vars($collection);
		}

		$mapped = [];

		foreach ($values as $key => $value)
		{
			$mapped[$key] = $callable($value, $key, $values);
		}
		return $mapped;
	}


	public static function merge(&$destination, ...$sources)
	{
		if (\is_array($destination))
		{
			$sources = \array_map(
				fn ($a) => (array) $a,
				\array_filter($sources, '\is_iterable'),
			);
			return \array_replace_recursive($destination, ...$sources);
		}
		elseif (\is_object($destination))
		{
			$sources = \array_filter($sources);
			foreach ($sources as $source)
			{
				$source = Arr::unpack(self::castCollectionToArray($source));
				foreach ($source as $key => $value)
				{
					$destination = self::set($destination, $key, $value);
				}
			}
		}
		return $destination;
	}

	protected static function baseOmit(&$array, $path)
	{
		$keys = Arr::splitKey($path);

		if (!\count($keys))
		{
			return $array;
		}

		$currentKey = \array_shift($keys);

		// if (!\array_key_exists($currentKey, $array))
		// {
		// 	return $array;
		// }
		if (!Arr::hasKey($array, $currentKey)) return $array;

		if (!\count($keys))
		{
			unset($array[$currentKey]);
			return $array;
		}

		return $array;
	}

	public static function omit(&$array, ...$paths)
	{
		return Functions::flatRest(function ($array, $path)
		{
			return self::baseOmit($array, $path);
		})($array, $paths);
	}

	public static function orderBy(?iterable $collection, ?array $iteratees = null, ?array $orders = null): array
	{
		if ($collection === null) return [];

		$iteratees ??= ['\dnk\dnk::identity'];
		$orders ??= Arr::constantArray('asc', \count($iteratees));

		$index = -1;
		$iteratees = static::arrayMap($iteratees, Functions::unary('\dnk\dnk::baseIteratee'));

		$result = self::map($collection, function ($value) use ($iteratees, &$index)
		{
			$criteria = static::arrayMap($iteratees, function ($iteratee) use ($value)
			{
				return $iteratee($value);
			});

			return ['criteria' => $criteria, 'index' => ++$index, 'value' => $value];
		});

		return self::map(self::sortBy($result, function ($object, $other) use ($orders)
		{
			$index = -1;
			$objCriteria = $object['criteria'];
			$othCriteria = $other['criteria'];
			$length = \count($objCriteria);
			$ordersLength = \count($orders);

			while (++$index < $length)
			{
				$result = $objCriteria[$index] <=> $othCriteria[$index];
				if ($result)
				{
					if ($index >= $ordersLength)
					{
						return $result;
					}
					$order = $orders[$index];

					return $result * ('desc' === $order ? -1 : 1);
				}
			}

			return $object['index'] - $other['index'];
		}), 'value');
	}

	/**
	 * @param array|iterable $predicates
	 * @return callable
	 */
	public static function overEvery($predicates)
	{
		return function (...$args) use ($predicates)
		{
			foreach ($predicates as $predicate)
			{
				if (!dnk::baseIteratee($predicate)(...$args))
				{
					return false;
				}
			}
			return true;
		};
	}

	/**
	 * @param array|iterable $predicates
	 * @return callable
	 */
	public static function overSome($predicates)
	{
		return function (...$args) use ($predicates)
		{
			foreach ($predicates as $predicate)
			{
				if (dnk::baseIteratee($predicate)(...$args))
				{
					return true;
				}
			}
			return false;
		};
	}

	/**
	 * Creates an array of elements split into two groups, the first of which
	 * contains elements `predicate` returns truthy for, the second of which
	 * contains elements `predicate` returns falsey for. The predicate is
	 * invoked with one argument: (value).
	 *
	 * @category Collection
	 *
	 * @param iterable $collection The collection to iterate over.
	 * @param callable $predicate  The function invoked per iteration.
	 *
	 * @return array the array of grouped elements.
	 */
	public static function partition(iterable $collection, $predicate = null): array
	{
		return self::createAggregator(function ($result, $value, $key)
		{
			$result[$key ? 0 : 1][] = $value;

			return $result;
		}, function ()
		{
			return [[], []];
		})($collection, $predicate);
	}

	public static function pick($collection, $paths)
	{
		$paths = self::filter(
			$paths,
			fn ($p) => self::has($collection, $p)
		);
		return self::reduce(
			$paths,
			function (&$result, $path) use (&$collection)
			{
				$result = self::set(
					$result,
					$path,
					self::get($collection, $path, null)
				);
				return $result;
			},
			\is_array($collection) ? [] : new \stdClass()
		);
	}
	public static function pickBy($collection, ?callable $predicate = null)
	{
		$predicate ??= 'dnk\dnk::identity';
		$keys = self::keys($collection);

		return self::reduce(
			$keys,
			function (&$result, $key) use (&$collection, $predicate)
			{
				$value = self::get($collection, $key);

				if ($predicate($value, $key, $collection))
				{
					$result = self::set($result, $key, $value);
				}
				return $result;
			},
			\is_array($collection) ? [] : new \stdClass()
		);
	}

	public static function reduce(iterable $collection, $iteratee, $accumulator = null)
	{
		$func = function (iterable $array, $iteratee, $accumulator, $initAccum = null)
		{
			$length = \count(\is_array($array) ? $array : \iterator_to_array($array));

			if ($initAccum && $length)
			{
				$accumulator = \current($array);
			}
			foreach ($array as $key => $value)
			{
				$accumulator = $iteratee($accumulator, $value, $key, $array);
			}

			return $accumulator;
		};

		return $func($collection, dnk::baseIteratee($iteratee), $accumulator, null === $accumulator);
	}

	public static function reduceRight(iterable $collection, $iteratee, $accumulator = null)
	{
		return static::baseReduce(
			\array_reverse(
				self::castCollectionToArray($collection),
				true
			),
			dnk::baseIteratee($iteratee),
			$accumulator,
			null === $accumulator
		);
	}

	public static function reject(iterable $collection, $predicate = null): array
	{
		return self::filter($collection, static::negate(static::baseIteratee($predicate)));
	}

	public static function set(&$object, $path, $value)
	{
		if (\is_array($object))
		{
			return Arr::set($object, $path, $value);
		}
		return Propper::set($object, $path, $value);
	}

	public static function size($collection): int
	{
		if (\is_string($collection))
		{
			return \strlen($collection);
		}

		return \count(self::castCollectionToArray($collection));
	}

	public static function some($collection, $predicate = null)
	{
		if (!\is_iterable($collection) || !self::size($collection)) return false;

		$iterator = dnk::baseIteratee(
			$predicate ?? static::getIdentityFunction()
		);

		foreach ($collection as $key => $value)
		{
			if ($iterator($value, $key, $collection))
			{
				return true;
			}
		}
		return false;
	}

	public static function sortBy($collection, $iteratees): array
	{
		if ($collection === null)
		{
			return [];
		};

		if (\is_callable($iteratees) || !\is_iterable($iteratees))
		{
			$iteratees = [$iteratees];
		}

		$result = \is_object($collection) ? \get_object_vars($collection) : $collection;

		foreach ($iteratees as $callable)
		{
			\usort($result, function ($a, $b) use ($callable)
			{
				$iteratee = dnk::baseIteratee($callable);

				return $iteratee($a, $b) <=> $iteratee($b, $a);
			});
		}

		return $result;
	}

	public static function toPairs(iterable $collection)
	{
		if (\is_array($collection))
		{
			$keys = \array_keys($collection);
			$values = \array_values($collection);
		}
		else
		{
			$keys = self::keys($collection);
			$values = self::map($keys, fn ($k) => self::get($collection, $k));
		}

		return static::zip($keys, $values);
	}

	public static function update(&$collection, $path, ?callable $updater)
	{
		$p = static::toPath($path);
		$value = self::get($collection, $p);
		if (!$updater) return $collection;
		return self::set($collection, $p, $updater($value));
	}

	public static function updatePaths($collection, $transforms = [])
	{
		foreach (Arr::unpack($transforms) as $path => $func)
		{
			$collection = self::update($collection, $path, $func);
		}
		return $collection;
	}

	public static function values($collection)
	{
		return \array_values(
			self::castCollectionToArray($collection)
		);
	}

	public static function __callStatic($name, $arguments)
	{
		if (method_exists(self::class, $name))
		{
			return self::$name(...$arguments);
		}

		return parent::__callStatic($name, $arguments);
	}
}
