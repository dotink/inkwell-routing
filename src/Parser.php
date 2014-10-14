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
	class Parser implements ParserInterface
	{
		const FORMAT_TOKEN    = '%%TOKEN%d%%';
		const REGEX_TOKEN     = '/\[[^\]]*\]/';


		/**
		 * A list of regex patterns for various pattern tokens
		 *
		 * @static
		 * @var array
		 */
		static protected $patterns = [
			'$' => '([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)',
			'+' => '([1-9]|[1-9][0-9]+)',
			'%' => '([-]?[0-9]+\.[0-9]+)',
			'#' => '([-]?(?:[0-9]+))',
			'!' => '([^/]+)',
			'*' => '(.*)',
			'/' => '(/)?',
		];


		/**
		 *
		 */
		public function regularize($route, $regex_delimiter, &$params = array())
		{
			if (preg_match_all(static::REGEX_TOKEN, $route, $matches)) {
				foreach ($matches[0] as $i => $token) {
					$holder = sprintf(static::FORMAT_TOKEN, $i);
					$route  = str_replace($token, $holder, $route);
				}

				$route = preg_quote($route, $regex_delimiter);

				foreach ($matches[0] as $i => $token) {
					$split_pos = strrpos($token, ':');

					if ($split_pos !== FALSE) {
						$params[] = trim(substr($token, $split_pos + 1, -1));
						$pattern  = trim(substr($token, 1, $split_pos - 1));
					} else {
						$params[] = $i;
						$pattern  = trim(substr($token, 1, -1));
					}

					$route = $this->replaceHolder($i, $pattern, $route);
				}
			}

			return $route;
		}


		/**
		 *
		 */
		protected function replaceHolder($i, $pattern, $route)
		{
			$holder = sprintf(static::FORMAT_TOKEN, $i);

			if (isset(static::$patterns[$pattern])) {
				return str_replace($holder, self::$patterns[$pattern], $route);

			} elseif ($patterns[0] == '(' && $pattern[strlen($pattern) - 1] == ')') {
				return str_replace($holder, $pattern, $route);
			}

			throw new Flourish\ProgrammerException(
				'Cannot parse invalid token pattern %s.',
				$pattern
			);
		}
	}
}