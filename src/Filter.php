<?php

namespace MaiVu\Php;

class Filter
{
	public static function clean($value, $type = 'string')
	{
		if (is_array($type))
		{
			$result = $value;

			foreach ($type as $filterType)
			{
				$result = static::clean($result, $filterType);
			}

			return $result;
		}

		switch ($type)
		{
			case 'int':
			case 'float':

				$callBack = 'int' === $type ? 'intval' : 'floatval';

				if (is_array($value))
				{
					$result = array_map($callBack, $value);
				}
				else
				{
					$result = $callBack($value);
				}

				break;

			case 'uint':

				if (is_array($value))
				{
					$result = [];

					foreach ($value as $eachString)
					{
						$result[] = abs(intval($eachString));
					}
				}
				else
				{
					$result = abs(intval($value));
				}

				break;

			case 'ufloat':

				if (is_array($value))
				{
					$result = [];

					foreach ($value as $eachString)
					{
						$result[] = abs(floatval($eachString));
					}
				}
				else
				{
					$result = abs(floatval($value));
				}

				break;

			case 'boolean':

				if (is_array($value))
				{
					$result = [];

					foreach ($value as $eachString)
					{
						$result[] = (bool) $eachString;
					}
				}
				else
				{
					$result = (bool) $value;
				}

				break;

			case 'alphaNum':
			case 'base64':
				$pattern = 'alphaNum' === $type ? '/[^A-Z0-9]/i' : '/[^A-Z0-9\/+=]/i';

				if (is_array($value))
				{
					$result = [];

					foreach ($value as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $value);
				}
				break;

			case 'string':
			case 'email':
			case 'url':
			case 'encode':

				$filterMaps = [
					'string' => FILTER_SANITIZE_STRING,
					'email'  => FILTER_SANITIZE_EMAIL,
					'url'    => FILTER_SANITIZE_URL,
					'encode' => FILTER_SANITIZE_ENCODED,
				];

				if (is_array($value))
				{
					$result = [];

					foreach ($value as $eachString)
					{
						$result[] = filter_var($eachString, $filterMaps[$type]);
					}
				}
				else
				{
					$result = filter_var($value, $filterMaps[$type]);
				}

				break;

			case 'slug':
				$result = static::toSlug($value);
				break;

			case 'path':
				return static::toPath($value);
				break;

			case 'unset':
				$result = null;
				break;

			case 'jsonEncode':
				$result = json_encode($value);
				break;

			case 'jsonDecode':

				$result = is_array($value) ? $value : (json_decode($value, true) ?: []);
				break;

			case 'yesNo':
				$result = in_array($value, ['Y', 'N'], true) ? $value : 'N';
				break;

			case 'inputName':
				$result = preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
				break;

			case 'unique':
				settype($value, 'array');
				$result = array_map('serialize', $value);
				$result = array_unique($result);
				$result = array_map('unserialize', $result);
				break;

			default:

				if (function_exists($type))
				{
					if (is_array($value))
					{
						$result = array_map($type, $value);
					}
					else
					{
						$result = $type($value);
					}
				}
				else
				{
					$result = $value;
				}

				break;
		}

		return $result;
	}

	public static function stripMarks($str)
	{
		// Lower
		$str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
		$str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
		$str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
		$str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
		$str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
		$str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
		$str = preg_replace('/(đ)/', 'd', $str);

		// Upper
		$str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $str);
		$str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $str);
		$str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $str);
		$str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $str);
		$str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $str);
		$str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $str);
		$str = preg_replace('/(Đ)/', 'D', $str);

		// Clean up
		$str = preg_replace('/[^a-zA-Z0-9-_]/', '', $str);

		return $str;
	}

	public static function toSlug($string)
	{
		$string = trim(preg_replace('/\s+/', '-', strtolower($string)), '-');
		$string = array_map(function ($str) {
			return static::stripMarks($str);
		}, explode('-', $string));

		$string = implode('-', $string);
		$string = preg_replace('/-+/', '-', $string);

		return $string;
	}

	public static function toPath($string)
	{
		$path = trim(preg_replace('/\/+/', '/', strtolower($string)), '/');
		$path = array_map(function ($str) {
			return static::toSlug($str);
		}, explode('/', $path));

		return implode('/', $path);
	}
}