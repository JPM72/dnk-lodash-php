<?php

namespace dnk\String\Constants;

const WORDS_ASCII = '/[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/';
const REGEX_CHAR = '/([\\^$.*+?()[\]{}|])/';

/** Used to map Latin Unicode letters to basic Latin letters. */
const DIACRITIC_MAP = [
	// Latin-1 Supplement block.
	'\xc0' => 'A', '\xc1' => 'A', '\xc2' => 'A', '\xc3' => 'A', '\xc4' => 'A', '\xc5' => 'A',
	'\xe0' => 'a', '\xe1' => 'a', '\xe2' => 'a', '\xe3' => 'a', '\xe4' => 'a', '\xe5' => 'a',
	'\xc7' => 'C', '\xe7' => 'c',
	'\xd0' => 'D', '\xf0' => 'd',
	'\xc8' => 'E', '\xc9' => 'E', '\xca' => 'E', '\xcb' => 'E',
	'\xe8' => 'e', '\xe9' => 'e', '\xea' => 'e', '\xeb' => 'e',
	'\xcc' => 'I', '\xcd' => 'I', '\xce' => 'I', '\xcf' => 'I',
	'\xec' => 'i', '\xed' => 'i', '\xee' => 'i', '\xef' => 'i',
	'\xd1' => 'N', '\xf1' => 'n',
	'\xd2' => 'O', '\xd3' => 'O', '\xd4' => 'O', '\xd5' => 'O', '\xd6' => 'O', '\xd8' => 'O',
	'\xf2' => 'o', '\xf3' => 'o', '\xf4' => 'o', '\xf5' => 'o', '\xf6' => 'o', '\xf8' => 'o',
	'\xd9' => 'U', '\xda' => 'U', '\xdb' => 'U', '\xdc' => 'U',
	'\xf9' => 'u', '\xfa' => 'u', '\xfb' => 'u', '\xfc' => 'u',
	'\xdd' => 'Y', '\xfd' => 'y', '\xff' => 'y',
	'\xc6' => 'Ae', '\xe6' => 'ae',
	'\xde' => 'Th', '\xfe' => 'th',
	'\xdf' => 'ss',
	// Latin Extended-A block.
	'\x{0100}' => 'A', '\x{0102}' => 'A', '\x{0104}' => 'A',
	'\x{0101}' => 'a', '\x{0103}' => 'a', '\x{0105}' => 'a',
	'\x{0106}' => 'C', '\x{0108}' => 'C', '\x{010a}' => 'C', '\x{010c}' => 'C',
	'\x{0107}' => 'c', '\x{0109}' => 'c', '\x{010b}' => 'c', '\x{010d}' => 'c',
	'\x{010e}' => 'D', '\x{0110}' => 'D', '\x{010f}' => 'd', '\x{0111}' => 'd',
	'\x{0112}' => 'E', '\x{0114}' => 'E', '\x{0116}' => 'E', '\x{0118}' => 'E', '\x{011a}' => 'E',
	'\x{0113}' => 'e', '\x{0115}' => 'e', '\x{0117}' => 'e', '\x{0119}' => 'e', '\x{011b}' => 'e',
	'\x{011c}' => 'G', '\x{011e}' => 'G', '\x{0120}' => 'G', '\x{0122}' => 'G',
	'\x{011d}' => 'g', '\x{011f}' => 'g', '\x{0121}' => 'g', '\x{0123}' => 'g',
	'\x{0124}' => 'H', '\x{0126}' => 'H', '\x{0125}' => 'h', '\x{0127}' => 'h',
	'\x{0128}' => 'I', '\x{012a}' => 'I', '\x{012c}' => 'I', '\x{012e}' => 'I', '\x{0130}' => 'I',
	'\x{0129}' => 'i', '\x{012b}' => 'i', '\x{012d}' => 'i', '\x{012f}' => 'i', '\x{0131}' => 'i',
	'\x{0134}' => 'J', '\x{0135}' => 'j',
	'\x{0136}' => 'K', '\x{0137}' => 'k', '\x{0138}' => 'k',
	'\x{0139}' => 'L', '\x{013b}' => 'L', '\x{013d}' => 'L', '\x{013f}' => 'L', '\x{0141}' => 'L',
	'\x{013a}' => 'l', '\x{013c}' => 'l', '\x{013e}' => 'l', '\x{0140}' => 'l', '\x{0142}' => 'l',
	'\x{0143}' => 'N', '\x{0145}' => 'N', '\x{0147}' => 'N', '\x{014a}' => 'N',
	'\x{0144}' => 'n', '\x{0146}' => 'n', '\x{0148}' => 'n', '\x{014b}' => 'n',
	'\x{014c}' => 'O', '\x{014e}' => 'O', '\x{0150}' => 'O',
	'\x{014d}' => 'o', '\x{014f}' => 'o', '\x{0151}' => 'o',
	'\x{0154}' => 'R', '\x{0156}' => 'R', '\x{0158}' => 'R',
	'\x{0155}' => 'r', '\x{0157}' => 'r', '\x{0159}' => 'r',
	'\x{015a}' => 'S', '\x{015c}' => 'S', '\x{015e}' => 'S', '\x{0160}' => 'S',
	'\x{015b}' => 's', '\x{015d}' => 's', '\x{015f}' => 's', '\x{0161}' => 's',
	'\x{0162}' => 'T', '\x{0164}' => 'T', '\x{0166}' => 'T',
	'\x{0163}' => 't', '\x{0165}' => 't', '\x{0167}' => 't',
	'\x{0168}' => 'U', '\x{016a}' => 'U', '\x{016c}' => 'U', '\x{016e}' => 'U', '\x{0170}' => 'U', '\x{0172}' => 'U',
	'\x{0169}' => 'u', '\x{016b}' => 'u', '\x{016d}' => 'u', '\x{016f}' => 'u', '\x{0171}' => 'u', '\x{0173}' => 'u',
	'\x{0174}' => 'W', '\x{0175}' => 'w',
	'\x{0176}' => 'Y', '\x{0177}' => 'y', '\x{0178}' => 'Y',
	'\x{0179}' => 'Z', '\x{017b}' => 'Z', '\x{017d}' => 'Z',
	'\x{017a}' => 'z', '\x{017c}' => 'z', '\x{017e}' => 'z',
	'\x{0132}' => 'IJ', '\x{0133}' => 'ij',
	'\x{0152}' => 'Oe', '\x{0153}' => 'oe',
	'\x{0149}' => "'n", '\x{017f}' => 's',
];
/** Used to match Latin Unicode letters (excluding mathematical operators). */
const REGEX_LATIN = '/[\xc0-\xd6\xd8-\xf6\xf8-\xff\x{0100}-\x{017f}]/u';

/** Used to compose unicode character classes. */
const REGEX_COMB_MARKS = '\\x{0300}-\\x{036f}';
const REGEX_COMB_MARKS_HALF = '\\x{fe20}-\\x{fe2f}';
const REGEX_COMB_SYMBOL = '\\x{20d0}-\\x{20ff}';

/**
 * Used to compose unicode capture groups to match
 * combining diacritical marks (https://en.wikipedia.org/wiki/Combining_Diacritical_Marks) and
 * combining diacritical marks for symbols (https://en.wikipedia.org/wiki/Combining_Diacritical_Marks_for_Symbols).
 */
const REGEX_COMB = '#[' . REGEX_COMB_MARKS . REGEX_COMB_MARKS_HALF . REGEX_COMB_SYMBOL . ']#u';

const REGEX_QUOTES = "/['\x{2019}]/u";

const HAS_UNICODE = '#[\\x{200d}\\x{0300}-\\x{036f}\\x{fe20}-\\x{fe2f}\\x{20d0}-\\x{20ff}\\x{fe0e}\\x{fe0f}]#u';

const HAS_UNICODE_WORD = '/[a-z][A-Z]|[A-Z]{2}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/';

const rsAstralRange = '\\x{e800}-\\x{efff}';
const rsComboMarksRange = '\\x{0300}-\\x{036f}';
const reComboHalfMarksRange = '\\x{fe20}-\\x{fe2f}';
const rsComboSymbolsRange = '\\x{20d0}-\\x{20ff}';
const rsComboRange = rsComboMarksRange . reComboHalfMarksRange . rsComboSymbolsRange;
const rsDingbatRange = '\\x{2700}-\\x{27bf}';
const rsLowerRange = 'a-z\\xdf-\\xf6\\xf8-\\xff';
const rsMathOpRange = '\\xac\\xb1\\xd7\\xf7';
const rsNonCharRange = '\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf';
const rsPunctuationRange = '\\x{2000}-\\x{206f}';
const rsSpaceRange = ' \\t\\x0b\\f\\xa0\\x{feff}\\n\\r\\x{2028}\\x{2029}\\x{1680}\\x{180e}\\x{2000}\\x{2001}\\x{2002}\\x{2003}\\x{2004}\\x{2005}\\x{2006}\\x{2007}\\x{2008}\\x{2009}\\x{200a}\\x{202f}\\x{205f}\\x{3000}';
const rsUpperRange = 'A-Z\\xc0-\\xd6\\xd8-\\xde';
const rsVarRange = '\\x{fe0e}\\x{fe0f}';
const rsBreakRange = rsMathOpRange . rsNonCharRange . rsPunctuationRange . rsSpaceRange;

/** Used to compose unicode capture groups. */
const rsApos = "[\\x{2019}]";
const rsBreak = '[' . rsBreakRange . ']';
const rsCombo = '[' . rsComboRange . ']';
const rsDigits = '\\d+';
const rsDingbat = '[' . rsDingbatRange . ']';
const rsLower = '[' . rsLowerRange . ']';
const rsMisc = '[^' . rsAstralRange . rsBreakRange . rsDigits . rsDingbatRange . rsLowerRange . rsUpperRange . ']';
const rsFitz = '\\x{e83c}[\\x{effb}-\\x{efff}]';
const rsModifier = '(?:' . rsCombo . '|' . rsFitz . ')';
const rsNonAstral = '[^' . rsAstralRange . ']';
const rsRegional = '(?:\\x{e83c}[\\x{ede6}-\\x{edff}]){2}';
const rsSurrPair = '[\\x{e800}-\\x{ebff}][\\x{ec00}-\\x{efff}]';
const rsUpper = '[' . rsUpperRange . ']';
const rsZWJ = '\\x{200d}';

/** Used to compose unicode regexes. */
const rsMiscLower = '(?:' . rsLower . '|' . rsMisc . ')';
const rsMiscUpper = '(?:' . rsUpper . '|' . rsMisc . ')';
const rsOptContrLower = '(?:' . rsApos . '(?:d|ll|m|re|s|t|ve))?';
const rsOptContrUpper = '(?:' . rsApos . '(?:D|LL|M|RE|S|T|VE))?';
const reOptMod = rsModifier . '?';
const rsOptVar = '[' . rsVarRange . ']?';
define('rsOptJoin', '(?:' . rsZWJ . '(?:' . implode('|', [rsNonAstral, rsRegional, rsSurrPair]) . ')' . rsOptVar . reOptMod . ')*');
const rsOrdLower = '\\d*(?:(?:1st|2nd|3rd|(?![123])\\dth)\\b)';
const rsOrdUpper = '\\d*(?:(?:1ST|2ND|3RD|(?![123])\\dTH)\\b)';
const rsSeq = rsOptVar . reOptMod . rsOptJoin;
define('rsEmoji', '(?:' . implode('|', [rsDingbat, rsRegional, rsSurrPair]) . ')' . rsSeq);

const rsAstral = '[' . rsAstralRange . ']';
const rsNonAstralCombo = rsNonAstral . rsCombo . '?';
define('rsSymbol', '(?:' . implode('|', [rsNonAstralCombo, rsCombo, rsRegional, rsSurrPair, rsAstral]) . ')');

/** Used to match [string symbols](https://mathiasbynens.be/notes/javascript-unicode). */
const reUnicode = rsFitz . '(?=' . rsFitz . ')|' . rsSymbol . rsSeq;
const reHasUnicode = '[' . rsZWJ . rsAstralRange . rsComboRange . rsVarRange . ']';
