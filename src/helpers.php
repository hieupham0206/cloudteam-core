<?php
//STRING HELPER

if ( ! function_exists('camel2words')) {
	/**
	 * For example, 'PostTag' will be converted to 'Post Tag'.
	 *
	 * @param $name
	 * @param bool $toLower
	 *
	 * @return string
	 */
	function camel2words($name, $toLower = true)
	{
		$label = trim(str_replace([
			'-',
			'_',
			'.',
		], ' ', preg_replace('/(?<![A-Z])[A-Z]/', '\0', $name)));

		return $toLower ? strtolower($label) : $label;
	}
}

if ( ! function_exists('humanize')) {
	/**
	 * Returns a human-readable string from $word.
	 *
	 * @param string $word the string to humanize
	 * @param bool $ucAll  whether to set all words to uppercase or not
	 *
	 * @return string
	 */
	function humanize($word, $ucAll = false)
	{
		$word = str_replace('_', ' ', preg_replace('/_id$/', '', $word));

		return $ucAll ? ucwords($word) : ucfirst($word);
	}
}

if ( ! function_exists('variablize')) {
	/**
	 * Same as camelize but first char is in lowercase.
	 * Converts a word like "send_email" to "sendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "whoSOnline"
	 *
	 * @param string $word to lowerCamelCase
	 *
	 * @return string
	 */
	function variablize($word)
	{
		$word = Illuminate\Support\Str::studly($word);

		return strtolower($word[0]) . substr($word, 1);
	}
}

if ( ! function_exists('underscore')) {
	/**
	 * Converts any "CamelCased" into an "underscored_word".
	 *
	 * @param string $words the word(s) to underscore
	 *
	 * @return string
	 */
	function underscore($words)
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $words));
	}
}

if ( ! function_exists('camelize')) {
	/**
	 * Returns given word as CamelCased.
	 *
	 * Converts a word like "send_email" to "SendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "WhoSOnline".
	 *
	 * @param string $word the word to CamelCase
	 *
	 * @return string
	 * @see variablize()
	 *
	 */
	function camelize($word)
	{
		return str_replace(' ', '', ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', $word)));
	}
}

if ( ! function_exists('formatBytes')) {
	/**
	 * Format and convert "bytes" to its optimal higher metric unit
	 *
	 * @param double $bytes      number of bytes
	 * @param integer $precision the number of decimal places to round off
	 *
	 * @return string
	 */
	function formatBytes($bytes, $precision = 2)
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];

		$bytes = max($bytes, 0);
		$pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow   = min($pow, count($units) - 1);

		$bytes /= $pow ** 1024;

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
}

if ( ! function_exists('numberToWord')) {
	/**
	 * Chuyển số sang chữ Tiếng Việt
	 *
	 * Ex: numberToWord(123456)()
	 *
	 * @param $number
	 *
	 * @return string
	 */
	function numberToWord($number)
	{
		return new class ($number) {
			private const DICTIONARY = [
				0                   => 'không',
				1                   => 'một',
				2                   => 'hai',
				3                   => 'ba',
				4                   => 'bốn',
				5                   => 'năm',
				6                   => 'sáu',
				7                   => 'bảy',
				8                   => 'tám',
				9                   => 'chín',
				10                  => 'mười',
				11                  => 'mười một',
				12                  => 'mười hai',
				13                  => 'mười ba',
				14                  => 'mười bốn',
				15                  => 'mười năm',
				16                  => 'mười sáu',
				17                  => 'mười bảy',
				18                  => 'mười tám',
				19                  => 'mười chín',
				20                  => 'hai mươi',
				30                  => 'ba mươi',
				40                  => 'bốn mươi',
				50                  => 'năm mươi',
				60                  => 'sáu mươi',
				70                  => 'bảy mươi',
				80                  => 'tám mươi',
				90                  => 'chín mươi',
				100                 => 'trăm',
				1000                => 'nghìn',
				1000000             => 'triệu',
				1000000000          => 'tỷ',
				1000000000000       => 'nghìn tỷ', //ngìn tỷ
				1000000000000000    => 'triệu tỷ',
				1000000000000000000 => 'tỷ tỷ',
			];
			private $seperator = ' ', $number;

			public function __construct($number)
			{
				$this->number = $number;
			}

			public function __invoke()
			{
				$number = str_replace(',', '', $this->number);

				if ( ! is_numeric($number)) {
					return false;
				}

				$number = (int) $number;

				if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
					trigger_error(
						'only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
						E_USER_WARNING
					);

					return false;
				}

				if ($number < 0) {
					return 'âm ' . $this->numberToWord(abs($number));
				}

				return $this->processNumber($number);
			}

			public function numberToWord($number)
			{
				$number = str_replace(',', '', $number);

				if ( ! is_numeric($number)) {
					return false;
				}

				if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
					trigger_error(
						'Chỉ chấp nhận trong khoảng từ -' . PHP_INT_MAX . ' đến ' . PHP_INT_MAX,
						E_USER_WARNING
					);

					return false;
				}

				if ($number < 0) {
					return 'Âm ' . $this->numberToWord(abs($number));
				}

				return $this->processNumber($number);
			}

			private function processNumber($number)
			{
				$fraction = null;

				if (strpos($number, '.') !== false) {
					[$number, $fraction] = explode('.', $number);
				}

				switch (true) {
					case $number < 21:
						$string = self::DICTIONARY[$number];
						break;
					case $number < 100:
						$string = $this->generateOnes($number);
						break;
					case $number < 1000:
						$string = $this->generateHundred($number);
						break;
					default:
						$string = $this->generateBeyondThoundsand($number);
						break;
				}

				if (null !== $fraction && is_numeric($fraction)) {
					$string .= ' phẩy ';
					$words  = [];
					foreach (str_split((string) $fraction) as $num) {
						$words[] = self::DICTIONARY[$num];
					}
					$string .= implode(' ', $words);
				}

				return $string;
			}

			private function generateOnes($number): string
			{
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = self::DICTIONARY[$tens];
				if ($units) {
					$tmpText = $this->seperator . self::DICTIONARY[$units];
					if ($units === 1) {
						$tmpText = $this->seperator . 'mốt';
					} elseif ($units === 5) {
						$tmpText = $this->seperator . 'lăm';
					}
					$string .= $tmpText;
				}

				return $string;
			}

			private function generateHundred($number): string
			{
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string    = self::DICTIONARY[$hundreds] . ' ' . self::DICTIONARY[100];
				if ($remainder) {
					$tmpText = $this->seperator . $this->numberToWord($remainder);
					if ($remainder < 10) {
						$tmpText = $this->seperator . 'lẻ ' . $this->numberToWord($remainder);
					} elseif ($remainder % 10 === 5) {
						$tmpText = $this->seperator . $this->numberToWord($remainder - 5) . ' lăm';
					}

					$string .= $tmpText;
				}

				return $string;
			}

			private function generateBeyondThoundsand($number): string
			{
				$baseUnit         = 1000 ** floor(log($number, 1000));
				$numBaseUnits     = (int) ($number / $baseUnit);
				$remainder        = $number % $baseUnit;
				$hundredRemainder = ($remainder / $baseUnit) * 1000;

				$string = $this->numberToWord($numBaseUnits) . ' ' . self::DICTIONARY[$baseUnit];
				if ($remainder < 100 && $remainder > 0) {
					$string = $this->numberToWord($numBaseUnits) . ' ' . self::DICTIONARY[$baseUnit] . ' không trăm';
					if ($remainder < 10) {
						$string = $this->numberToWord($numBaseUnits) . ' ' . self::DICTIONARY[$baseUnit] . ' không trăm lẻ';
					}
				} elseif ($hundredRemainder > 0 && $hundredRemainder < 100) {
					$string = $this->numberToWord($numBaseUnits) . ' ' . self::DICTIONARY[$baseUnit] . ' không trăm';
					if ($hundredRemainder < 10) {
						$string = $this->numberToWord($numBaseUnits) . ' ' . self::DICTIONARY[$baseUnit] . ' không trăm lẻ';
					}
				}

				if ($remainder) {
					$string .= $this->seperator . $this->numberToWord($remainder);
				}

				return $string;
			}
		};
	}
}
//END STRING HELPER

//NUMBER HELPER
if ( ! function_exists('normalizeNumber')) {
	/**
	 * Normalizes a user-submitted number for use in code and/or to be saved into the database.
	 *
	 * @param $number
	 * @param string $groupSymbol
	 * @param string $decimalSymbol
	 *
	 * @return mixed
	 */
	function normalizeNumber($number, $groupSymbol = ',', $decimalSymbol = '.')
	{
		if (is_string($number)) {
			// Remove any group symbols and use a period for the decimal symbol
			$number = str_replace([$groupSymbol, $decimalSymbol], ['', '.'], $number);
		}

		return $number;
	}
}

if ( ! function_exists('getPercentage')) {
	/**
	 * Returns percentage from number
	 *
	 * @param float $number
	 * @param float $percents
	 *
	 * @return float
	 */
	function getPercentage($number, $percents)
	{
		return $number / 100 * $percents;
	}
}

if ( ! function_exists('calculatePercentage')) {
	/**
	 * Calculates percentage from two numbers
	 *
	 * @param float $original
	 * @param float $new
	 * @param bool $factor If enabled, `75%` will result in `0.75`.
	 *
	 * @return float
	 */
	function calculatePercentage($original, $new, $factor = true)
	{
		$result = ($original - $new) / $original;
		if ( ! $factor) {
			$result *= 100;
		}

		return $result;
	}
}

if ( ! function_exists('increaseByPercentage')) {
	/**
	 * Increase number by percents
	 *
	 * @param float $number
	 * @param float $percents
	 *
	 * @return float
	 */
	function increaseByPercentage($number, $percents)
	{
		return $number + getPercentage($number, $percents);
	}
}

if ( ! function_exists('decreaseByPercentage')) {
	/**
	 * Increase number by percents
	 *
	 * @param float $number
	 * @param float $percents
	 *
	 * @return float
	 */
	function decreaseByPercentage($number, $percents)
	{
		return $number - getPercentage($number, $percents);
	}
}
//END NUMBER HELPER

if ( ! function_exists('user')) {
	/**
	 * Get user đang đăng nhập
	 *
	 * @return string
	 */
	function user()
	{
		return auth()->user();
	}
}

if ( ! function_exists('normalizeSerializeArray')) {
	/**
	 * @param $queryDatas
	 *
	 * @throws JsonException
	 * @return array
	 */
	function normalizeSerializeArray($queryDatas)
	{
		$filters      = json_decode($queryDatas, JSON_FORCE_OBJECT, 512, JSON_THROW_ON_ERROR);
		$finalFilters = [];
		foreach ($filters as $filter) {
			if (isset($finalFilters[$filter['name']])) {
				$currentVal = is_array($finalFilters[$filter['name']]) ? $finalFilters[$filter['name']] : [$finalFilters[$filter['name']]];
				if (is_string($currentVal)) {
					$currentVal = trim($currentVal);
				}
				$finalFilters[$filter['name']] = array_merge([
					$filter['value'],
				], $currentVal);
			} else {
				$finalFilters[$filter['name']] = trim($filter['value']);
			}
		}

		return $finalFilters;
	}
}

if ( ! function_exists('logToFile')) {
	/**
	 * Log hành động theo file tương ứng.
	 *
	 * @param string $channel
	 * @param        $api
	 * @param        $request
	 * @param        $response
	 * @param array  $times
	 * @param null   $requestUuid
	 * @param string $level
	 *
	 * @throws JsonException
	 */
	function logToFile(string $channel, $api, $request, $response, $times = [], $requestUuid = null, $level = 'info')
	{
		if ( ! $times) {
			$requestedAt = $responsedAt = date('d-m-Y H:i:s');
		} else {
			[$requestedAt, $responsedAt] = $times;
		}

		if ($request && is_array($request)) {
			$request = json_encode($request, JSON_THROW_ON_ERROR);
		}

		if ($response && is_array($response)) {
			$response = json_encode($response, JSON_THROW_ON_ERROR);
		}

		if ( ! $requestUuid) {
			if ( ! empty(request()->header('Uuid'))) {
				$uuid = '[' . request()->header('Uuid') . "]\r\n";
			} else {
				$uuid = '';
			}
		} else {
			$uuid = $requestUuid;
		}

		if ($request && $response) {
			Log::channel($channel)->log($level,"\r\n-Request: $api - $uuid - At $requestedAt\r\n$request \r\n-Response: $responsedAt\r\n$response \r\n");
		}

		if ($request && ! $response) {
			Log::channel($channel)->log($level,"\r\n-Request: $api - $uuid - At $requestedAt\r\n$request \r\n");
		}

		if (! $request && $response) {
			Log::channel($channel)->log($level, "\r\n-Response: $api - $uuid - At $responsedAt\r\n$response \r\n");
		}

		if ( ! empty(config('logging.elasticsearch_enable'))) {
			$context = [
				'app_url'     => config('app.url'),
				'app_name'    => config('logging.elasticsearch_name'),
				'log_channel' => $channel,
				'log_group'   => config('consul.name'),
			];
			Log::channel('elasticsearch')->info("\r\n-Request: $api - $uuid - At $requestedAt\r\n$request \r\n-Response: $responsedAt\r\n$response \r\n", $context);
		}
	}
}

if (! function_exists('isMultidimensionalArray')) {
	/**
	 * Log hành động theo file tương ứng.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	function isMultidimensionalArray(array $array)
	{
		return count($array) !== count($array, COUNT_RECURSIVE);
	}
}

if (! function_exists('sanitizeValue')) {
    function sanitizeValue(&$value)
    {
        $value = strip_tags($value);

        if ($value && ! is_numeric($value) && in_array($value[0], ['=', '+', '-', '@'])) {
            $value = substr($value, 1);
        }

        return $value;
    }
}