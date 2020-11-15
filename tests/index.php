<?php

use MaiVu\Php\Filter;

require_once dirname(__DIR__) . '/src/Filter.php';

Filter::setRule('custom', function ($value) {
	return 'CUSTOM';
});

$closure = function ($value) {
	return $value . ' is filtered by a Closure';
};

echo '<pre>' . print_r(Filter::clean(1, '1|0:array'), true) . '</pre>';

echo '<pre>' . print_r(Filter::clean('a\\//b/', 'path'), true) . '</pre>';

echo '<pre>' . print_r(Filter::clean('a\\//b/', 'custom'), true) . '</pre>';

echo '<pre>' . print_r(Filter::clean('a\\//b/', $closure), true) . '</pre>';