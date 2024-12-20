<?php

namespace dnk;

require_once 'Constants.php';

use dnk\String\Constants as Constants;

class Str extends dnk
{
	public static function cat($string, ...$rest): string
	{
		$string = \strval($string);
		$rest = Coll::map($rest, fn ($s) => (string) $s);
		return implode("", [
			$string,
			...$rest
		]);
	}

	private static function baseDeburr(string $string)
	{
		$patterns = \array_map(
			function ($pattern)
			{
				return "#$pattern#u";
			},
			\array_keys(Constants\DIACRITIC_MAP)
		);
		return \preg_replace(
			Constants\REGEX_COMB,
			'',
			\preg_replace($patterns, \array_values(Constants\DIACRITIC_MAP), $string)
		);
	}

	public static function camelCase($string): string
	{
		$string = \strval($string);
		return \lcfirst(
			\array_reduce(
				self::words(\preg_replace("/['\\x{2019}]/u", '', $string)),
				function ($result, $word)
				{
					return $result . self::capitalize(\strtolower($word));
				},
				''
			)
		);
	}

	public static function capitalize($string): string
	{
		$string = \strval($string);
		return \ucfirst(\mb_strtolower($string));
	}

	public static function crunchWhitespace($string): string
	{
		$string = \strval($string);
		return self::trim(
			self::replace($string, '/\s+/', ' ')
		);
	}

	public static function deburr($string): string
	{
		$string = \strval($string);
		$normalized = \Normalizer::normalize($string, \Normalizer::FORM_KD);
		if ($normalized && function_exists('\transliterator_transliterate'))
		{
			return \transliterator_transliterate('Any-Latin; Latin-ASCII;', $normalized) || '';
		}
		return self::baseDeburr($string);
	}

	public static function escape($string): string
	{
		$string = \strval($string);
		return $string ? \htmlentities($string) : '';
	}

	public static function escapeRegExp($string): string
	{
		$string = \strval($string);
		return $string ? \preg_replace('/([\\^$.*+?()[\]{}|])/', '\\\$0', $string) : '';
	}

	public static function split($string, string $separator, int $limit = 0): array
	{
		$string = \strval($string);

		if ($separator !== "." && \preg_match(Constants\REGEX_CHAR, $separator))
		{
			return \preg_split($separator, $string, $limit ?? -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
		}

		/** @var array $result */
		$result = \explode($separator, $string);

		if ($limit > 0)
		{
			return \array_splice($result, 0, $limit);
		}

		return $result;
	}

	protected static function asciiWords(string $string)
	{
		\preg_match_all(Constants\WORDS_ASCII, $string, $matches);
		return $matches[0] ?? [];
	}
	protected static function hasUnicode(string $string)
	{
		return \preg_match('#' . Constants\reHasUnicode . '#u', $string) > 0;
	}
	protected static function hasUnicodeWord(string $string)
	{
		return \preg_match(Constants\HAS_UNICODE_WORD, $string) === 1;
	}
	protected static function unicodeWords(string $string): array
	{
		$regex = '#' . \implode('|', [
			Constants\rsUpper . '?' . Constants\rsLower . '+' . Constants\rsOptContrLower . '(?=' . \implode('|', [Constants\rsBreak, Constants\rsUpper, '$']) . ')',
			Constants\rsMiscUpper . '+' . Constants\rsOptContrUpper . '(?=' . \implode('|', [Constants\rsBreak, Constants\rsUpper . Constants\rsMiscLower, '$']) . ')',
			Constants\rsUpper . '?' . Constants\rsMiscLower . '+' . Constants\rsOptContrLower,
			Constants\rsUpper . '+' . Constants\rsOptContrUpper,
			Constants\rsOrdUpper,
			Constants\rsOrdLower,
			Constants\rsDigits,
			rsEmoji,
		]) . '#u';

		if (\preg_match_all($regex, $string, $matches) > 0)
		{
			return $matches[0];
		}

		return [];
	}

	public static function endsWith(
		$string,
		$target,
		int $position = null
	): bool
	{
		$string = \strval($string);
		$target = \strval($target);
		$length = \strlen($string);
		$position = \is_null($position) ? $length : +$position;

		if ($position < 0)
		{
			$position = 0;
		}
		elseif ($position > $length)
		{
			$position = $length;
		}

		$position -= \strlen($target);

		return $position >= 0 && \substr($string, $position, \strlen($target)) === $target;
	}
	public static function endWith(
		$string,
		$target
	): string
	{
		$string = \strval($string);
		if (!$target) return $string;
		$target = \strval($target);
		return self::endsWith($string, $target) ? $string : ($string . $target);
	}

	public static function isJson($string, $returnIfTrue = false, $options = [
		'flags' => [],
		'associative' => true,
		'depth' => 512
	])
	{

		if (!\is_string($string)) return false;

		$options = Arr::defaults($options, [
			'flags' => [],
			'associative' => true,
			'depth' => 512
		]);

		$decoded = \json_decode(
			$string,
			$options['associative'],
			$options['depth'],
			...$options['flags']
		);
		if (\is_null($decoded)) return false;

		if ($returnIfTrue) return $decoded;

		return true;
	}

	public static function kebabCase($string): string
	{
		$string = \strval($string);
		return Coll::reduce(
			self::words(self::replace(self::deburr($string), Constants\REGEX_QUOTES, '')),
			function ($result, $word, $index)
			{
				return $result . ($index ? '-' : '') . \strtolower($word);
			},
			''
		);
	}

	public static function lowerCase($string)
	{
		$string = \strval($string);
		return \implode(' ', \array_map('\strtolower', self::words(
			\preg_replace(Constants\REGEX_QUOTES, '', $string)
		)));
	}

	public static function snakeCase($string): string
	{
		$string = \strval($string);
		return Coll::reduce(
			self::words(self::replace(self::deburr($string), Constants\REGEX_QUOTES, '')),
			function ($result, $word, $index)
			{
				return $result . ($index ? '_' : '') . \strtolower($word);
			},
			''
		);
	}
	public static function startCase($string): string
	{
		$string = \strval($string);
		return Coll::reduce(
			self::words(self::replace(self::deburr($string), Constants\REGEX_QUOTES, '')),
			function ($result, $word, $index)
			{
				return $result . ($index ? ' ' : '') . \ucfirst($word);
			},
			''
		);
	}

	public static function upperCase($string)
	{
		$string = \strval($string);
		return \implode(' ', \array_map('\strtoupper', self::words(
			\preg_replace(Constants\REGEX_QUOTES, '', $string)

		)));
	}

	public static function toLower($string): string
	{
		return \strtolower(\strval($string));
	}
	public static function toUpper($string): string
	{
		return \strtoupper(\strval($string));
	}
	public static function lowerFirst($string): string
	{
		return \lcfirst(\strval($string));
	}

	public static function match($string, string $pattern)
	{
		$string = \strval($string);
		$matched = \preg_match($pattern, $string, $matches);
		if ($matched !== 1) return [];
		return \array_slice($matches, 1);
	}

	public static function upperFirst($string): string
	{
		return \ucfirst(\strval($string));
	}

	public static function pad($string, int $length, string $chars = ' '): string
	{
		return \str_pad(\strval($string), $length, $chars, \STR_PAD_BOTH);
	}
	public static function padStart($string, int $length, string $chars = ' '): string
	{
		return \str_pad(\strval($string), $length, $chars, \STR_PAD_LEFT);
	}
	public static function padEnd($string, int $length, string $chars = ' '): string
	{
		return \str_pad(\strval($string), $length, $chars, \STR_PAD_RIGHT);
	}

	public static function repeat($string, int $n = 1): string
	{
		return \str_repeat(\strval($string), $n);
	}

	public static function removeWhitespace($string): string
	{
		return self::replace(\strval($string), '/\s+/', '');
	}

	public static function replace($string, $pattern, $replacement = null): string
	{
		$string = \strval($string);

		if (\is_array($pattern))
		{
			return static::reduce(
				$pattern,
				function ($result, $value, $key)
				{
					if (\is_array($value) && \count($value) == 2)
					{
						[$key, $value] = $value;
					}
					return self::replace($result, $key, $value);
				},
				$string
			);
		}

		if (\preg_match(Constants\REGEX_CHAR, $pattern))
		{
			if (!\is_callable($replacement))
			{
				return \preg_replace(
					$pattern,
					\is_string($replacement) || \is_array($replacement) ? $replacement : '',
					$string
				);
			}

			$callback = function (array $matches) use ($replacement): ?string
			{
				if (!\array_filter($matches))
				{
					return null;
				}

				return \is_callable($replacement) ? $replacement(...$matches) : null;
			};

			return \preg_replace_callback($pattern, $callback, $string);
		}

		return \str_replace(
			$pattern,
			\is_string($replacement) || \is_array($replacement) ? $replacement : '',
			\strval($string)
		);
	}

	public static function startsWith(
		$string,
		$target,
		int $position = null
	): bool
	{
		$string = \strval($string);
		$target = \strval($target);
		$length = \strlen($string);
		$position = \is_null($position) ? 0 : +$position;

		if ($position < 0)
		{
			$position = 0;
		}
		elseif ($position > $length)
		{
			$position = $length;
		}

		return $position >= 0 && \substr($string, $position, \strlen($target)) === $target;
	}
	public static function startWith(
		$string,
		$target
	): string
	{
		$string = \strval($string);
		if (!$target) return $string;
		$target = \strval($target);
		return self::startsWith($string, $target, 0) ? $string : ($target . $string);
	}

	public static function test($string, string $pattern): bool
	{
		$string = \strval($string);
		return \preg_match($pattern, $string) === 1;
	}

	public static function trim($string, string $chars = ' '): string
	{
		return \trim(\strval($string), $chars);
	}
	public static function trimEnd($string, string $chars = ' '): string
	{
		return \rtrim(\strval($string), $chars);
	}
	public static function trimStart($string, string $chars = ' '): string
	{
		return \ltrim(\strval($string), $chars);
	}

	public static function toNumeric($value, $float = true)
	{
		if (!$value) return 0;
		if (\is_int($value) || \is_float($value)) return $value;
		$re = $float ? '/[^\d\.]/' : '/[^\d]/';
		$value = self::replace(\strval($value), $re, '');
		return $float ? \floatval($value) : \intval($value);
	}

	public static function words($string, string $pattern = null): array
	{
		$string = \strval($string);
		if ($pattern === null)
		{
			if (self::hasUnicodeWord($string)) return self::unicodeWords($string);
			return self::asciiWords($string);
		}

		if (@\preg_match_all($pattern, $string, $matches) > 0)
		{
			return $matches[0];
		}

		return [];
	}

	public static function toAlpha(int $n, ?int $offset = 0): string
	{
		$n += $offset;
		$r = '';
		for ($i = 1; $n >= 0 && $i < 10; $i++)
		{
			$r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
			$n -= pow(26, $i);
		}
		return $r;
	}

	public static function unescape($string): string
	{
		$string = \strval($string);
		return $string ? \html_entity_decode($string) : '';
	}

	public static function fromAlpha(string $a): int
	{
		$r = 0;
		$l = strlen($a);
		for ($i = 0; $i < $l; $i++)
		{
			$r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
		}
		return $r - 1;
	}

	public static function closestWord(
		array $words,
		string $input,
		?array $options = []
	)
	{
		$options = dnk::defaults($options, [
			'trim' => true,
			'deburr' => false,
			'lower' => true,
			'sensitivity' => 0.75
		]);

		$trim = ($options['trim'] ?? null) !== false;
		$deburr = ($options['deburr'] ?? null) !== false;
		$lower = ($options['lower'] ?? null) !== false;
		$s = $options['sensitivity'] ?? 0.75;

		if ($trim) $input = self::trim($input);
		if ($deburr) $input = self::deburr($input);
		if ($lower) $input = self::toLower($input);

		$shortest = -1;
		foreach ($words as $word)
		{
			$w = $lower ? self::toLower($word) : $word;
			$lev = \levenshtein($input, $w);

			if ($lev == 0)
			{
				$closest = $word;
				$shortest = 0;
				break;
			}

			if ($lev <= $shortest || $shortest < 0)
			{
				$closest  = $word;
				$shortest = $lev;
			}
		}

		if ($s && \is_numeric($s))
		{
			$percent = 1 - \levenshtein($input, $closest) / \max(\strlen($input), \strlen($closest));
			if ($percent < $s) return false;
		}

		return $closest;
	}

	public static function closestWordIndex(array $words, string $input, ?array $options = [])
	{
		$closest = self::closestWord($words, $input, $options);
		return dnk::indexOf($words, $closest);
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
