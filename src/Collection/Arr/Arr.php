<?php

namespace dnk;

class Arr extends dnk
{
	public static function assign(array &$array, ...$sources): array
	{
		$sources = \array_filter($sources, '\is_iterable');

		foreach ($sources as $source)
		{
			$source = static::castArray($source);
			if (!\count($source)) continue;

			Coll::forEach($source, function ($value, $key) use (&$array)
			{
				$assigned = &$array;
				$assigned = static::drillKey(
					$assigned,
					$key,
					$value
				);
				$array = &$array;
			});
		}

		return $array;
	}

	protected static function baseIntersection(
		$arrays,
		?callable $iteratee,
		$comparator = null
	)
	{
		$includes = $comparator ? 'self::arrayIncludesWith' : 'self::arrayIncludes';
		$length = \count($arrays[0]);
		$othLength = \count($arrays);
		$othIndex = $othLength;
		$caches = [];
		$maxLength = INF;
		$result = [];

		while ($othIndex--)
		{
			$array = &$arrays[$othIndex];
			if ($othIndex && $iteratee)
			{
				$array = \array_map($iteratee, $array);
			}

			$maxLength = \min(\count($array), $maxLength);
			$caches[$othIndex] = !$comparator && $iteratee ? [] : null;
		}

		$array = $arrays[0];

		$index = -1;
		$seen = $caches[0];

		while (++$index < $length && \count($result) < $maxLength)
		{
			$value = $array[$index];
			$computed = $iteratee ? $iteratee($value) : $value;

			$value = ($comparator ?: $value !== 0) ? $value : 0;
			if (!($seen ? \is_scalar($computed) && isset($seen[$computed]) : $includes($result, $computed, $comparator)))
			{
				$othIndex = $othLength;
				while (--$othIndex)
				{
					$cache = $caches[$othIndex];
					if (!(!empty($cache) ? isset($cache[$computed]) : $includes($arrays[$othIndex], $computed, $comparator)))
					{
						continue 2;
					}
				}
				if (empty($seen))
				{
					$seen[] = $computed;
				}

				$result[] = $value;
			}
		}

		return $result;
	}

	private static function baseUnpack($array, $curKey = null)
	{
		$arr = [];

		foreach ($array as $key => $value)
		{
			$k = static::joinKey($curKey, $key);

			if (!static::isUnpackable($value))
			{
				$arr[$k] = $value;
				continue;
			}

			$nested = self::baseUnpack($value, $k);

			foreach ($nested as $nsKey => $nsValue)
			{
				$arr[$nsKey] = $nsValue;
			}
		}

		return $arr;
	}

	protected static function isFlattenable($value): bool
	{
		if (!\is_array($value)) return false;

		if ($value === []) return true;

		return \range(0, \count($value) - 1) === \array_keys($value);
	}

	public static function chunk(?array $array, ?int $size = 1): array
	{
		if (!$size) return [];

		return \array_chunk($array ?? [], $size, false);
	}

	public static function compact(?array $array): array
	{
		return \array_values(
			\array_filter($array ?? [], function ($v)
			{
				switch (\gettype($v))
				{
					case 'boolean':
					case 'integer':
					case 'string':
						return \boolval($v);
					case 'NULL':
						return false;
					case 'double':
						return !\is_nan($v);
						// case 'array':
						// case 'object':
						// case 'resource':
						// case 'resource (closed)':
						// case 'unknown type':
					default:
						return true;
				}
			})
		);
	}

	public static function difference(array $array, array ...$values): array
	{
		return \array_values(\array_diff($array, ...$values));
	}
	public static function differenceBy(array $array, ...$values): array
	{
		if (!$array)
		{
			return [];
		}

		if (!\is_callable(\end($values)))
		{
			return self::difference($array, ...$values);
		}

		$iteratee = \array_pop($values);

		$values = \array_map($iteratee, self::baseFlatten($values, 1, '\is_array', true, null));

		$valuesLength = \count($values);
		$result = [];

		foreach ($array as $value)
		{
			$computed = $iteratee($value);
			$valuesIndex = $valuesLength;
			while ($valuesIndex--)
			{
				if ($computed === $values[$valuesIndex])
				{
					continue 2;
				}
			}

			$result[] = $value;
		}

		return $result;
	}
	public static function differenceWith(array $array, ...$values): array
	{
		if (!$array)
		{
			return [];
		}

		if (!\is_callable(\end($values)))
		{
			return self::difference($array, ...$values);
		}

		/** @var callable $comparator */
		$comparator = \array_pop($values);

		$values = self::baseFlatten($values, 1, '\is_array', true, null);

		$valuesLength = \count($values);
		$result = [];

		foreach ($array as $value)
		{
			$valuesIndex = $valuesLength;
			while ($valuesIndex--)
			{
				if ($comparator($value, $values[$valuesIndex]))
				{
					continue 2;
				}
			}

			$result[] = $value;
		}

		return $result;
	}

	public static function fromEntries(array $pairs): array
	{
		if (!\count($pairs))
		{
			return [];
		}

		$result = [];

		foreach ($pairs as $pair)
		{
			$result[$pair[0]] = $pair[1];
		}

		return $result;
	}
	public static function fromPairs(array $pairs): \stdClass
	{
		if (!\count($pairs))
		{
			return new \stdClass();
		}

		$result = new \stdClass();

		foreach ($pairs as $pair)
		{
			$result->{$pair[0]} = $pair[1];
		}

		return $result;
	}

	public static function constantArray($value, $dimensions)
	{
		if (!\is_array($dimensions) && !static::isLength($dimensions))
		{
			return [];
		}

		if (static::isLength($dimensions))
		{
			if (!$dimensions) return [];
			$dimensions = [$dimensions];
		}
		else if (!\is_array($dimensions) || !\count($dimensions))
		{
			return [];
		}

		$len = \array_pop($dimensions);
		$c = static::constant($value);

		$array = static::times($len, $c);
		while ($len = \array_pop($dimensions))
		{
			$array = static::times($len, static::constant($array));
		}
		return $array;
	}

	public static function intersection(array ...$arrays): array
	{
		return \array_intersect(...$arrays);
	}
	public static function intersectionBy(...$arrays/*, callable $iteratee*/): array
	{
		$iteratee = \array_pop($arrays);

		return self::baseIntersection($arrays, static::baseIteratee($iteratee));
	}
	public static function intersectionWith(...$arrays /*, callable $comparator = null*/): array
	{
		$copy = $arrays;
		$comparator = \array_pop($arrays);

		if (!\is_callable($comparator))
		{
			$arrays = $copy;
			$comparator = null;
		}

		return self::baseIntersection($arrays, null, $comparator);
	}

	/**
	 * @param iterable|array $collection
	 * @param int|string $key
	 */
	public static function hasKey($collection, $key): bool
	{
		if (\is_array($collection)) return \key_exists($key, $collection);

		if (\is_iterable($collection))
		{
			foreach ($collection as $k => $value)
			{
				if (dnk::isEqual($key, $k)) return true;
			}
		}

		return false;
	}

	public static function slice($array, ?int $start = 0, $end = null, ?bool $preserveKeys = false)
	{
		if (\is_string($array))
		{
			return \substr($array, $start, $end);
		}
		$end ??= \count($array);
		return \array_slice($array, $start, $end, $preserveKeys);
	}

	public static function concat($array, ...$values): array
	{
		$check = function ($value): array
		{
			return \is_array($value) ? $value : [$value];
		};

		return \array_merge($check($array), ...\array_map($check, $values));
	}

	public static function last(array $array)
	{
		return \end($array) ?: null;
	}

	public static function head(array $array)
	{
		reset($array);

		return current($array) ?: null;
	}

	public static function first(array $array)
	{
		return static::head($array);
	}

	public static function tail(array $array): array
	{
		array_shift($array);

		return $array;
	}

	public static function initial($array): array
	{
		$array = self::castArray($array);
		if (count($array) <= 1) return $array;
		return array_slice($array, 0, -1);
	}

	public static function most($array): array
	{
		return self::initial($array);
	}

	public static function isList($array): bool
	{
		return \is_array($array) && \array_is_list($array);
	}

	public static function isAssoc($array): bool
	{
		return \is_array($array) && \array_is_assoc($array);
	}

	public static function indexOf(array $array, $value, int $fromIndex = null): int
	{
		$inc = true;
		$index = 0;

		if (null !== $fromIndex)
		{
			$index = $fromIndex >= 0 ? $fromIndex : \count($array) - 1;
			if ($fromIndex < 0)
			{
				$array = \array_reverse($array, false);
				$inc = false;
			}
		};

		foreach ($array as $v)
		{
			if (dnk::isEqual($value, $v))
			{
				return $index;
			}

			$inc ? $index++ : $index--;
		}

		return -1;
	}

	public static function includes($array, $value): bool
	{
		if (!$array)
		{
			return false;
		}
		elseif (\is_array($array))
		{
			return self::indexOf(\array_values($array), $value, 0) > -1;
		}
		elseif (\is_string($array))
		{
			return \str_contains($array, $value);
		}
	}

	public static function isUnpackable($arg = null): bool
	{
		return \is_array($arg) && !self::isList($arg);
	}

	public static function isNested(array $array): bool
	{
		foreach ($array as $element)
		{
			if (is_array($element))
			{
				return true;
			}
		}

		return false;
	}

	public static function joinKey(...$keys)
	{
		$keys = \array_filter($keys);

		$path = \array_shift($keys);
		foreach ($keys as $key)
		{
			$path .= \preg_match('/^\d+$/', $key) ? "[$key]" : ".$key";
		}

		return $path;
	}

	/**
	 * @param string|array $key
	 * @return array
	 */
	public static function splitKey($key)
	{
		if (\is_array($key)) return $key;
		return \explode('.', \preg_replace('/\[(\d+)\]/', '.${1}', $key));
	}

	/**
	 * @param array $arr
	 * @param array|string $key_s
	 * @param mixed $value
	 * @param int $lim length at which to stop
	 * consuming keys
	 */
	public static function drillKey(&$array, $keyPath, $value, $lim = 1)
	{
		$path = static::toPath($keyPath);

		$target = &$array;

		while (\count($path) > 1)
		{
			$key = \array_shift($path);
			if (!isset($target[$key])) $target[$key] = [];
			$target = &$target[$key];
		}

		$target[$path[0]] = $value;
		return $array;
	}

	/**
	 * @param array $arr
	 * @param array|string $key_s
	 * @param mixed $value
	 */
	public static function extractKey($array, $path)
	{
		// $path = static::splitKey($path);
		$path = static::toPath($path);
		$value = $array[\array_shift($path)] ?? null;

		while (!\is_null($value) && \count($path))
		{
			$value = $value[\array_shift($path)] ?? null;
		}
		return $value;
	}

	public static function unpack($array)
	{
		if (self::isUnpackable($array) === false) return $array;
		return self::baseUnpack($array);
	}

	public static function pack(iterable $array = [])
	{
		$arr = [];

		foreach ($array as $k => $v)
		{
			foreach ($array as $k => $v)
			{
				static::drillKey($arr, static::splitKey($k), $v);
			}
		}

		return $arr;
	}

	public static function join(array $array, ?string $separator = ","): string
	{
		return \implode($separator, $array);
	}

	public static function implodeWith(
		array $array,
		string $separator = '',
		string $before = '',
		string $after = ''
	)
	{
		return $before . implode($separator, $array) . $after;
	}

	public static function implodeWrap(
		array $array,
		string $separator = '',
		string $before = '',
		string $after = ''
	)
	{
		return $before . implode("{$after}{$separator}{$before}", $array) . $after;
	}

	/**
	 * join implode
	 */
	public static function jimplode(array $array)
	{
		return implode("", $array);
	}

	public static function nimplode(array $array)
	{
		return static::implodeWith($array, "\n");
	}

	public static function cnimplode(array $array)
	{
		return static::implodeWith($array, ",\n");
	}

	public static function castArray($x = null): array
	{
		if (!isset($x))
		{
			return [];
		}
		elseif (is_array($x))
		{
			return $x;
		}
		return [$x];
	}

	protected static function baseGet($array, $path)
	{
		$path = static::toPath($path);
		$l = \count($path);

		$value = $array[$path[0]] ?? null;

		for ($i = 1; $i < $l; $i++)
		{
			if (\is_null($value)) break;
			$value = $value[$path[$i]] ?? null;
		}
		return $value;
	}
	public static function get($array, $path, $defaultValue = null)
	{
		if (!static::isNested($array))
		{
			return $array[static::joinKey(...static::castArray($path))] ?? $defaultValue;
		}

		return static::baseGet($array, $path) ?? $defaultValue;
	}
	public static function set(array &$array, $key, $value): array
	{
		return static::drillKey(
			$array,
			$key,
			$value
		);
	}

	public static function at(array $array, ...$keys): array
	{
		$keys = self::flatten($keys);
		$return = [];
		$valuesOnly = self::last($keys) === true;
		if ($valuesOnly) \array_pop($keys);

		foreach ($keys as $key)
		{
			if (\is_array($key))
			{
				return static::at($array, ...$key);
			}

			if (!\array_key_exists($key, $array)) continue;
			$value = $array[$key] ?? null;
			$return[$key] = $value;
		}

		if ($valuesOnly) $return = \array_values($return);

		return $return;
	}

	public static function push(&$array, $values)
	{
		$index = -1;
		$length = \is_array($values) ? \count($values) : \strlen($values);
		$offset = \count($array);

		while (++$index < $length)
		{
			$array[$offset + $index] = $values[$index];
		}

		return $array;
	}

	protected static function baseFlatten(
		?array $array,
		int $depth,
		callable $predicate = null,
		bool $isStrict = null,
		array $result = null
	): array
	{
		$result ??= [];

		if ($array === null)
		{
			return $result;
		}

		$predicate ??= function ()
		{
			return self::isFlattenable(...func_get_args());
		};

		foreach ($array as $value)
		{
			if ($depth > 0 && $predicate($value))
			{
				if ($depth > 1)
				{
					/* Recursively flatten arrays (susceptible to call stack limits). */
					$result = self::baseFlatten($value, $depth - 1, $predicate, $isStrict, $result);
				}
				else
				{
					self::push($result, $value);
				}
			}
			elseif (!$isStrict)
			{
				$result[\count($result)] = $value;
			}
		}

		return $result;
	}

	public static function flatten(array $array = null): array
	{
		return self::baseFlatten($array, 1);
	}

	public static function flattenDeep(array $array = null): array
	{
		return self::baseFlatten($array, PHP_INT_MAX);
	}

	public static function findIndex(array $array, $predicate = null, int $fromIndex = null): int
	{
		$length = \count($array);
		if (!$length)
		{
			return -1;
		}

		$index = $fromIndex ?? 0;
		if ($index < 0)
		{
			$index = \min($length + $index, 0);
		}

		$predicate ??= dnk::getIdentityFunction();
		$iteratee = dnk::baseIteratee($predicate);

		foreach ($array as $key => $value)
		{
			if ($iteratee($value, $key, $array))
			{
				return $index;
			}

			$index++;
		}

		return -1;
	}

	public static function defaults(array &$destination, ...$sources): array
	{
		$sources = \array_filter($sources, '\is_iterable');
		foreach ($sources as $source)
		{
			if (!\count($source)) continue;
			foreach ($source as $key => $default) $destination[$key] ??= $default;
			unset($key, $default);
		}

		return $destination;
	}

	protected static function baseDefaultsDeep(&$destination, $source, $pick = false): array
	{
		$destKeys = [];
		if ($pick)
		{
			$destKeys = dnk::chain($destination)
				->unpack()
				->keys()
				->value();
		}

		$unpacked = static::unpack($source);
		foreach ($unpacked as $srcKey => $srcValue)
		{
			if ($pick && !self::includes($destKeys, $srcKey)) continue;

			$dV = static::get($destination, $srcKey, null);
			if ($dV === null)
			{
				$destination = static::set(
					$destination,
					$srcKey,
					$srcValue
				);
			}
		}

		return $destination;
	}

	public static function defaultsDeep(array &$destination, ...$sources): array
	{
		$sources = \array_filter($sources, '\is_iterable');
		if (!\count($sources)) return $destination;
		foreach ($sources as $source)
		{
			$destination = self::baseDefaultsDeep($destination, $source, false);
		}
		unset($source);

		return $destination;
	}

	public static function pickDefaults(array &$destination, ...$sources): array
	{
		foreach ($sources as $source)
		{
			$destination = self::baseDefaultsDeep($destination, $source, true);
		}
		return $destination;
	}

	public static function replaceDeep(array &$destination, ...$sources)
	{
		return $destination = static::reduce(
			[$destination, ...$sources],
			fn ($r, $v) => $r = \array_replace($r, $v),
			$destination
		);
	}
	public static function merge(array &$destination, ...$sources)
	{
		foreach ($sources as $source)
		{
			if (!\is_array($source)) continue;
			$merged = &$destination;
			$merged = \array_replace_recursive($destination, $source);
			$destination = &$destination;
		}
		return $destination;
	}

	public static function diffAssocRecursive($array1, $array2)
	{
		foreach ($array1 as $key => $value)
		{
			$value2 = $array2[$key] ?? null;
			if ($value === $value2) continue;
			if (\is_array($value))
			{
				if (!isset($array2[$key]))
				{
					$difference[$key] = $value;
				}
				elseif (!\is_array($array2[$key]))
				{
					$difference[$key] = $value;
				}
				else
				{
					$new_diff = self::diffAssocRecursive($value, $array2[$key]);
					if ($new_diff != FALSE)
					{
						$difference[$key] = $new_diff;
					}
				}
			}
			elseif (!isset($array2[$key]) || $array2[$key] !== $value)
			{
				$difference[$key] = $value;
			}
		}
		return !isset($difference) ? 0 : $difference;
	}

	private static function isEmptyArray($array)
	{
		return \is_array($array) && \count(\array_keys($array)) === 0;
	}

	public static function addedDiff($lhs, $rhs)
	{
		$diff = [];
		if ($lhs === $rhs || !\is_array($lhs) || !\is_array($rhs)) return $diff;

		return self::reduce(
			dnk::keys($rhs),
			function ($acc, $key) use ($lhs, $rhs)
			{
				if (self::hasKey($lhs, $key))
				{
					$difference = self::addedDiff($lhs[$key], $rhs[$key]);
					if (\is_array($difference) && self::isEmptyArray($difference)) return $acc;

					$acc[$key] = $difference;
					return $acc;
				}
				$acc[$key] = $rhs[$key];
				return $acc;
			},
			$diff
		);
	}

	public static function deletedDiff($lhs, $rhs)
	{
		$diff = [];
		if ($lhs === $rhs || !\is_array($lhs) || !\is_array($rhs)) return $diff;

		return self::reduce(
			dnk::keys($lhs),
			function ($acc, $key) use ($lhs, $rhs)
			{
				if (self::hasKey($rhs, $key))
				{
					$difference = self::deletedDiff($lhs[$key], $rhs[$key]);
					if (\is_array($difference) && self::isEmptyArray($difference)) return $acc;

					$acc[$key] = $difference;
					return $acc;
				}
				$acc[$key] = null;
				return $acc;
			},
			$diff
		);
	}

	public static function updatedDiff($lhs, $rhs)
	{
		$diff = [];

		if ($lhs === $rhs) return $diff;
		if (!\is_array($lhs) || !\is_array($rhs)) return $rhs;

		return self::reduce(
			dnk::keys($rhs),
			function ($acc, $key) use ($lhs, $rhs)
			{
				if (self::hasKey($lhs, $key))
				{
					$difference = self::updatedDiff($lhs[$key], $rhs[$key]);

					if (
						self::isEmptyArray($difference)
						&&
						(self::isEmptyArray($lhs[$key] ?? null) || !self::isEmptyArray($rhs[$key] ?? null))
					) return $acc;

					$acc[$key] = $difference;
					return $acc;
				}
				return $acc;
			},
			$diff
		);
	}

	public static function detailedDiff($lhs, $rhs)
	{
		return [
			'added' => self::addedDiff($lhs, $rhs),
			'deleted' => self::deletedDiff($lhs, $rhs),
			'updated' => self::updatedDiff($lhs, $rhs),
		];
	}

	public static function showDiff($lhs, $rhs)
	{
		$diff = self::updatedDiff($lhs, $rhs);

		if (!\count($diff)) return [];

		$keys = dnk::chain($diff)
			->unpack()
			->keys()
			->value();

		$a = [];
		foreach ($keys as $path)
		{
			$a = dnk::set($a, $path, [
				'from' => dnk::get($lhs, $path, null),
				'to' => dnk::get($rhs, $path, null),
			]);
		}

		return $a;
	}

	public static function union(array ...$arrays): array
	{
		return \array_unique(\array_merge(...$arrays));
	}
	public static function unionBy(...$arrays): array
	{
		return self::uniqBase(
			self::baseFlatten($arrays, 1, '\is_array', true),
			static::baseIteratee(\array_pop($arrays))
		);
	}
	public static function unionWith(...$arrays): array
	{
		$comparator = \array_pop($arrays);

		if (!\is_callable($comparator))
		{
			throw new \InvalidArgumentException(__FUNCTION__ . ' expects the last value passed to be callable');
		}

		return self::uniqBase(self::baseFlatten($arrays, 1, '\is_array', true), null, $comparator);
	}

	public static function take(array $array, int $n = 1): array
	{
		if ($n < 1) return [];

		\array_splice($array, $n);

		return $array;
	}
	public static function takeRight(array $array, int $n = 1): array
	{
		if ($n < 1) return [];

		return \array_slice($array, -$n);
	}
	public static function takeRightWhile(array $array, $predicate): array
	{
		$iteratee = static::baseIteratee($predicate);
		$result = [];

		foreach (\array_reverse($array, true) as $index => $value)
		{
			if ($iteratee($value, $index, $array))
			{
				$result[$index] = $value;
			}
		}

		return \array_reverse($result);
	}
	public static function takeWhile(array $array, $predicate): array
	{
		$result = [];

		$iteratee = static::baseIteratee($predicate);

		foreach ($array as $index => $value)
		{
			if ($iteratee($value, $index, $array))
			{
				$result[$index] = $value;
			}
		}

		return $result;
	}

	protected static function uniqBase(
		array $array,
		callable $iteratee = null,
		callable $comparator = null
	)
	{
		$index = -1;
		$includes = 'self::arrayIncludes';
		$length = \count($array);
		$isCommon = true;
		$result = [];
		$seen = $result;

		if ($comparator)
		{
			$isCommon = false;
			$includes = 'self::arrayIncludesWith';
		}
		else
		{
			$seen = $iteratee ? [] : $result;
		}

		while (++$index < $length)
		{
			$value = $array[$index];
			$computed = $iteratee ? $iteratee($value) : $value;

			$value = ($comparator || $value !== 0) ? $value : 0;

			if ($isCommon && $computed)
			{
				$seenIndex = \count($seen);
				while ($seenIndex--)
				{
					if ($seen[$seenIndex] === $computed)
					{
						continue 2;
					}
				}
				if ($iteratee)
				{
					$seen[] = $computed;
				}

				$result[] = $value;
			}
			elseif (!$includes($result, $computed, $comparator))
			{
				if ($seen !== $result)
				{
					$seen[] = $computed;
				}
				$result[] = $value;
			}
		}

		return $result;
	}
	public static function uniq(array $array = []): array
	{
		return \array_unique($array);
	}
	public static function uniqBy(array $array, $iteratee): array
	{
		return self::uniqBase($array, static::baseIteratee($iteratee));
	}
	public static function uniqWith(array $array, callable $comparator): array
	{
		return self::uniqBase($array, null, $comparator);
	}

	public static function unzip(array $array): array
	{
		if (!\count($array))
		{
			return [];
		}

		$length = 0;
		$array = \array_filter($array, function ($group) use (&$length)
		{
			if (\is_array($group))
			{
				$length = \max(\count($group), $length);

				return true;
			}
		});

		return parent::baseTimes($length, function ($index) use ($array)
		{
			return self::map($array, parent::baseProperty($index));
		});
	}

	public static function unzipWith(array $array, ?callable $iteratee = null): array
	{
		if (!\count($array))
		{
			return [];
		}

		$result = self::unzip($array);
		if (!\is_callable($iteratee))
		{
			return $result;
		}

		return self::arrayMap($result, function ($group) use ($iteratee)
		{
			return $iteratee(...$group);
		});
	}

	public static function without(array $array, ...$values): array
	{
		return dnk::baseRest(fn ($a, ...$v) => self::difference($a, ...$v))($array, ...$values);
	}

	public static function zip(array ...$arrays): array
	{
		return parent::baseRest(
			fn ($v) => self::unzip($v)
		)(...$arrays);
	}
	public static function zipWith(...$arrays): array
	{
		/** @var callable|null $iteratee */
		$iteratee = \is_callable(\end($arrays)) ? \array_pop($arrays) : null;

		return self::unzipWith($arrays, $iteratee);
	}

	public static function zipArray(array $props = [], array $values = [])
	{
		$props = self::castArray($props);
		$values = self::castArray($values);
		$values = self::take($values, \count($props));
		return \array_combine($props, $values);
	}
	public static function zipArrayDeep(array $props = [], array $values = [])
	{
		$props = \array_values(self::castArray($props));
		$values = \array_values(self::castArray($values));
		$values = self::take($values, \count($props));

		$array = [];
		foreach ($props as $index => $prop)
		{
			$array = self::set($array, $prop, $values[$index]);
		}
		return $array;
	}

	public static function zipObject(array $props = [], array $values = [])
	{
		$result = new \stdClass;
		$index = -1;
		$length = \count($props);
		$props = \array_values($props);
		$values = \array_values($values);

		while (++$index < $length)
		{
			$value = $values[$index] ?? null;
			$result->{$props[$index]} = $value;
		}

		return $result;
	}

	public static function zipObjectDeep(array $props = [], array $values = []): \stdClass
	{
		$result = new \stdClass;
		$index = -1;
		$length = \count($props);
		$props = \array_values($props);
		$values = \array_values($values);

		while (++$index < $length)
		{
			$value = $values[$index] ?? null;
			Propper::set($result, $props[$index], $value);
		}

		return $result;
	}

	public static function __callStatic($name, $arguments)
	{
		if (\method_exists(self::class, $name))
		{
			return self::$name(...$arguments);
		}

		return parent::__callStatic($name, $arguments);
	}
}
