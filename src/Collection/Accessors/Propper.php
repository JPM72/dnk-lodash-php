<?php

namespace dnk;

use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Propper extends dnk
{
	private static PropertyAccessor $propertyAccess;
	private static $initialized = false;

	private static function initialize()
	{
		if (self::$initialized) return self::$propertyAccess;

		$builder = PropertyAccess::createPropertyAccessorBuilder()
			->disableExceptionOnInvalidIndex();

		self::$propertyAccess = $builder->getPropertyAccessor();
		self::$initialized = true;

		return self::$propertyAccess;
	}

	protected static function createAccessorFunction(callable $function)
	{
		return Functions::flatRest(
			function ($object, $paths) use ($function)
			{
				return $function($object, $paths);
			}
		);
	}

	public static function isReadable($object, $path)
	{
		return static::$propertyAccess->isReadable($object, $path);
	}

	public static function getPropAccessor()
	{
		return self::initialize();
	}

	/**
	 * @param object|iterable $collection
	 * @param string $key
	 */
	public static function hasProperty($collection, $key)
	{
		if (\is_object($collection)) return \property_exists($collection, $key);

		if (!\is_iterable($collection)) return false;

		foreach ($collection as $k => $value)
		{
			if (dnk::isEqual($key, $k)) return true;
		}

		return false;
	}

	public static function matches($source): callable
	{
		return function ($value, $index, $collection) use ($source): bool
		{
			if ($value === $source || dnk::isEqual($value, $source))
			{
				return true;
			}

			if (\is_array($source) || $source instanceof \Traversable)
			{
				foreach ($source as $k => $v)
				{
					$propValue = static::property($k)($value, $index, $collection);

					if (!dnk::isEqual($propValue, $v))
					{
						return false;
					}
				}

				return true;
			}

			return false;
		};
	}

	public static function matchesProperty($property, $source): callable
	{
		return function ($value, $index, $collection) use ($property, $source)
		{
			return dnk::isEqual(
				static::property($property)($value, $index, $collection),
				$source
			);
		};
	}

	public static function property($path): callable
	{
		$pA = Propper::getPropAccessor();

		return function ($value, $index = 0, $collection = []) use ($path, $pA)
		{
			$path = \implode('.', (array) $path);

			if (\is_array($value))
			{
				if (\strpos($path, '.') !== false)
				{
					$paths = \explode('.', $path);

					foreach ($paths as $path)
					{
						$value = self::property($path)($value, $index, $collection);
					}

					return $value;
				}

				if (\is_string($path) && $path[0] !== '[' && $path[-1] !== ']')
				{
					$path = "[$path]";
				}
			}

			try
			{
				return $pA->getValue($value, $path);
			}
			catch (NoSuchPropertyException | NoSuchIndexException $e)
			{
				return null;
			}
		};
	}

	protected static function basePick($object, $paths): \stdClass
	{
		return static::basePickBy($object, $paths, function ($value, $path) use ($object)
		{
			return \property_exists($object, $path)
				|| \method_exists($object, 'get' . (\ucfirst($path)));
		});
	}

	public static function pick($object, $paths): \stdClass
	{
		return Functions::flatRest(function ($object, $paths)
		{
			return static::basePick($object, $paths);
		})($object, $paths);
	}

	protected static function basePickBy($object, $paths, callable $predicate): \stdClass
	{
		$index = -1;
		$length = \is_array($paths) ? \count($paths) : \strlen($paths);
		$result = new \stdClass();

		while (++$index < $length)
		{
			$path = $paths[$index];
			$value = static::baseGet($object, $path);

			if ($predicate($value, $path))
			{
				static::baseSet($result, KeyPath::castPath($path, $object), $value);
			}
		}

		return $result;
	}

	public static function pickBy($object, ?callable $predicate = null): \stdClass
	{
		if ($object === null)
		{
			return new \stdClass;
		}

		$props = Coll::map(\array_keys(\get_object_vars($object)), function ($prop)
		{
			return [$prop];
		});
		$predicate = dnk::baseIteratee($predicate ?? '\dnk::identity');

		return static::basePick($object, $props, function ($value, $path) use ($predicate)
		{
			return $predicate($value, $path[0]);
		});
	}

	protected static function baseGet($object, $path)
	{
		$path = KeyPath::castPath($path, $object);

		$index = 0;
		$length = \count($path);

		while ($object !== null && !\is_scalar($object) && $index < $length)
		{
			$object = static::property(KeyPath::toKey($path[$index++]))($object);
		}

		return ($index > 0  && $index === $length) ? $object : null;
	}
	public static function get($object, $path, $defaultValue = null)
	{
		return ($object !== null ? static::baseGet($object, $path) : null) ?? $defaultValue;
	}

	protected static function baseSet(&$object, $path, $value, callable $customizer = null)
	{
		if (!\is_object($object))
		{
			return $object;
		}

		$path = KeyPath::castPath($path, $object);

		$index = -1;
		$length = \count($path);
		$lastIndex = $length - 1;
		$nested = $object;

		while ($nested !== null && ++$index < $length)
		{
			$key = KeyPath::toKey($path[$index]);

			if ($index !== $lastIndex)
			{
				$objValue = \is_array($nested) ?
					($nested[$key] ?? null) : ($nested->$key ?? null);

				$newValue = $customizer ?
					$customizer($objValue, $key, $nested) :
					$objValue;

				if ($newValue === null)
				{
					$newValue = \is_object($objValue) ?
						$objValue : (\is_numeric($path[$index + 1])
							? [] :
							new \stdClass());
				}

				if (\is_array($nested))
				{
					$nested[$key] = $newValue;
				}
				else
				{
					$nested->{$key} = $newValue;
				}

				if (\is_array($nested))
				{
					$nested = &$nested[$key];
				}
				else
				{
					$nested = &$nested->$key;
				}

				continue;
			}

			$nested->{$key} = $value;
		}

		return $object;
	}
	public static function set(&$object, $path, $value)
	{
		return static::baseSet($object, $path, $value);
	}

	public static function setWith($object, $path, $value, ?callable $customizer = null)
	{
		$customizer ??= static::getIdentityFunction();
		return self::baseSet($object, $path, $value, $customizer);
	}

	public static function invoke($object, $path, ...$args)
	{
		$path = KeyPath::castPath($path, $object);
		$object = KeyPath::parent($object, $path);
		$last = KeyPath::toKey(Arr::last($path));

		if (\is_callable([$object, $last], true))
		{
			return $object->$last(...$args);
		}

		/** @var callable|null $func */
		$func = \is_null($object) ? $object : [$object, $last];

		return \is_callable($func) ? $func($object, ...$args) : null;
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
