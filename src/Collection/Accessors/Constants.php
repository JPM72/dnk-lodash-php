<?php

namespace dnk\Constants;

const REGEX_LEADING_DOT = '/^\./';
const REGEX_PROP_DEEP = '#\.|\[(?:[^[\]]*|(["\'])(?:(?!\1)[^\\\\]|\\.)*?\1)\]#';
const REGEX_PROP_PLAIN = '/^\w*$/';
const REGEX_PROP_NAME = '#[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["\'])((?:(?!\2)[^\\\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))#';
const REGEX_BACKSLASH = '/\\(\\)?/g';

