<?php namespace Inkwell\Routing
{
	use Dotink\Flourish;

	/**
	 * Collection class responsible for aggregating and mapping routes to actions
	 *
	 * @copyright Copyright (c) 2012, Matthew J. Sahagian
	 * @author Matthew J. Sahagian [mjs] <gent@dotink.org>
	 *
	 * @license Please reference the LICENSE.txt file at the root of this distribution
	 *
	 * @package Inkwell\Routing
	 */
	class Compiler implements CompilerInterface
	{
		const REGEX_TOKEN = '/\[[^\]]*\]/';
		const WS_HOLDER   = '#WS#';


		/**
		 * A list of transformation method aliases
		 *
		 * @static
		 * @var array
		 */
		static protected $filters = [
			'uc' => 'makeUpperCamelCase',
			'lc' => 'makeLowerCamelCase',
			'us' => 'makeUnderScored',
			'ws' => 'makeWebSafe'
		];


		/**
		 *
		 */
		private $wordSeparator = NULL;


		/**
		 *
		 */
		public function __construct($word_separator = '-')
		{
			$this->wordSeparator = $word_separator;
		}


		/**
		 *
		 */
		public function make($target, $params, &$remainder = array())
		{
			$remainder = $params;

			if (preg_match_all(self::REGEX_TOKEN, $target, $matches)) {
				foreach ($matches[0] as $token) {
					$split_pos = strrpos($token, ':');

					if ($split_pos !== FALSE) {
						$param  = trim(substr($token, $split_pos + 1, -1));
						$filter = trim(substr($token, 1, $split_pos - 1));
					} else {
						$param  = trim($token, '[ ]');
						$filter = NULL;
					}

					if (!isset($params[$param])) {
						throw new Flourish\ProgrammerException(
							'Missing parameter %s in supplied parameters',
							$param
						);
					}

					$target = $this->transformToken($filter, $token, $params[$param], $target);

					if (isset($remainder[$param])) {
						unset($remainder[$param]);
					}
				}
			}

			return $target;
		}


		/**
		 *
		 */
		public function makeLowerCamelCase($value)
		{
			$value = str_replace($this->wordSeparator, ' ', $value);
			$value = ucwords($value);
			$value = str_replace(' ', '', $value);
			$value = strtolower($value[0]) . substr($value, 1);

			return $value;
		}


		/**
		 *
		 */
		public function makeUpperCamelCase($value)
		{
			$value = str_replace($this->wordSeparator, ' ', $value);
			$value = ucwords($value);
			$value = str_replace(' ', '', $value);

			return $value;
		}


		/**
		 *
		 */
		public function makeUnderScored($value)
		{
			if ($this->wordSeparator != '_') {
				$value = str_replace($this->wordSeparator, '_', $value);
			}

			return $value;
		}


		/**
		 *
		 */
		public function makeWebSafe($value)
		{
			$value = preg_replace('/[-_\[\]\(\)]/', ' ', $value);
			$value = preg_replace('/([a-z])([A-Z]|[0-9])/', '$1 $2', $value);
			$value = preg_replace('/[^a-zA-Z0-9\s]/', '', $value);
			$value = preg_replace('/\s+/', ' ', $value);
			$value = str_replace(' ', $this->wordSeparator, $value);
			$value = strtolower(trim($value, $this->wordSeparator));

			return $value;
		}


		/**
		 *
		 */
		protected function transformToken($filter, $token, $value, $target)
		{
			if ($filter !== NULL) {
				if (isset(static::$filters[$filter])) {
					$transformer = [$this, static::$filters[$filter]];
					$value       = $transformer($value);

				} else {
					throw new Flourish\ProgrammerException(
						'Cannot compile invalid transformation filter %s.',
						$filter
					);
				}
			}

			return str_replace($token, $value, $target);
		}
	}
}
