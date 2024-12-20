<?php

if (!function_exists('array_is_list'))
{
	/**
	 * Determines if the given `array` is a list. An array is considered a list if its keys consist of consecutive numbers from `0` to `count($array)-1`.
	 * @param array $array
	 * @return bool
	 * @link https://www.php.net/manual/en/function.array-is-list.php
	 */
	function array_is_list(array $array): bool
	{
		$i = -1;
		foreach ($array as $k => $v)
		{
			++$i;
			if ($k !== $i)
			{
				return false;
			}
		}
		return true;
	}
}

if (!function_exists('array_is_assoc'))
{
	/**
	 * Checks if array is not a list; inverse of `array_is_list`
	 *
	 * @param array $array
	 * @return bool
	 */
	function array_is_assoc(array $array): bool
	{
		if (empty($array))
		{
			return false; // Empty arrays aren't strictly associative
		}

		foreach ($array as $key => $value)
		{
			if (\is_int($key))
			{
				return false;
			}
		}
		return true;
	}
}

if (!function_exists('str_starts_with'))
{
	/**
	 * Checks if a string starts with a given substring.
	 * Performs a case-sensitive check indicating if `haystack` starts with `needle`.
	 *
	 * @param  string $haystack
	 * @param  string $needle
	 * @return bool
	 */
	function str_starts_with(string $haystack, string $needle)
	{
		return $needle !== '' && \strncmp($haystack, $needle, \strlen($needle)) === 0;
	}
}
if (!function_exists('str_ends_with'))
{
	/**
	 * Checks if a string ends with a given substring.
	 * Performs a case-sensitive check indicating if `haystack` ends with `needle`.
	 *
	 * @param  string $haystack
	 * @param  string $needle
	 * @return bool
	 */
	function str_ends_with(string $haystack, string $needle)
	{
		return $needle !== '' && \substr($haystack, -\strlen($needle)) === $needle;
	}
}

if (!function_exists('str_contains'))
{
	/**
	 * Determine if a string contains a given substring.
	 * Performs a case-sensitive check indicating if `needle` is contained in `haystack`.
	 *
	 * @param  string $haystack
	 * @param  string $needle
	 * @return bool
	 *
	 * @link https://www.php.net/manual/en/function.str-contains.php
	 */
	function str_contains(string $haystack, string $needle)
	{
		return $needle !== '' && \mb_strpos($haystack, $needle) !== false;
	}
}
