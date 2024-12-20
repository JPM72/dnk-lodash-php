<?php

function identity($value = null)
{
	return $value;
}

function date_now()
{
	return (int) \floor(microtime(true) * 1e3);
}