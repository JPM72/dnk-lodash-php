<?php

namespace dnk;

require_once('Constants.php');

use const dnk\Constants\REGEX_LEADING_DOT;
use const dnk\Constants\REGEX_PROP_DEEP;
use const dnk\Constants\REGEX_PROP_NAME;
use const dnk\Constants\REGEX_PROP_PLAIN;

class KeyPath extends dnk
{
	public static function parent($object, $path)
	{
		return \count($path) < 2 ? $object : null;
	}

	public static function toKey($value): string
	{
		if (\is_string($value))
		{
			return $value;
		}

		$result = (string) $value;

		return ($result === '0' && (1 / $value) === -INF) ? '-0' : $result;
	}

	public static function isKey($value, $object = []): bool
	{
		if (\is_array($value))
		{
			return false;
		}

		if (\is_numeric($value))
		{
			return true;
		}

		return \preg_match(REGEX_PROP_PLAIN, $value) ||
			!\preg_match(REGEX_PROP_DEEP, $value) ||
			(!\is_null($object) && isset(((object) $object)->$value)
			);
	}

	public static function stringToPath(...$args)
	{
		$func = function ($string)
		{
			$result = [];
			if (\preg_match(REGEX_LEADING_DOT, $string))
			{
				$result[] = '';
			}

			\preg_match_all(REGEX_PROP_NAME, $string, $matches, PREG_SPLIT_DELIM_CAPTURE);

			foreach ($matches as $match)
			{
				$result[] = $match[1] ?? $match[0];
			}

			return $result;
		};

		return $func(...$args);
	}
	public static function pathToString($path): string
	{
		if (\is_string($path)) return $path;

		$string = '';

		foreach ($path as $key)
		{
			if (\dnk\Lang::isLength($key) || \preg_match('/^\d+$/', \strval($key)))
			{
				$string .= "[$key]";
			}
			else
			{
				$string .= $string ? ".$key" : \strval($key);
			}
		}
		return $string;
	}

	public static function castPath($value, $object): array
	{
		if (\is_array($value))
		{
			return $value;
		}

		return self::isKey($value, $object) ?
			[$value] :
			self::stringToPath((string) $value);
	}

	public static function toBrackets($key)
	{
		if (!\is_string($key)) return $key;
		$path = \explode('.', $key);
		$glue = '][';
		$path = \implode($glue, $path);
		$path = "[{$path}]";
		return $path;
	}

	public static function toDots($key, $nullable = false)
	{
		if (!\is_string($key)) return $key;
		$glue = ($nullable ? '?.' : '.');
		return \implode($glue, \explode('][', \trim($key, '[]')));
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
