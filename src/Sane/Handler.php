<?php

namespace Adamkiss\Toolkit\Sane;

use Adamkiss\Toolkit\Exception\Exception;
use Adamkiss\Toolkit\Filesystem\F;

/**
 * Base handler abstract,
 * which needs to be extended to
 * create valid sane handlers
 * @since 3.5.4
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Handler
{
	/**
	 * Sanitizes the given string
	 *
	 * @param bool $isExternal Whether the string is from an external file
	 *                         that may be accessed directly
	 */
	abstract public static function sanitize(
		string $string,
		bool $isExternal = false
	): string;

	/**
	 * Sanitizes the contents of a file by overwriting
	 * the file with the sanitized version
	 *
	 * @throws \Adamkiss\Toolkit\Exception\Exception If the file does not exist
	 * @throws \Adamkiss\Toolkit\Exception\Exception On other errors
	 */
	public static function sanitizeFile(string $file): void
	{
		$content   = static::readFile($file);
		$sanitized = static::sanitize($content, isExternal: true);
		F::write($file, $sanitized);
	}

	/**
	 * Validates file contents
	 *
	 * @param bool $isExternal Whether the string is from an external file
	 *                         that may be accessed directly
	 *
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Adamkiss\Toolkit\Exception\Exception On other errors
	 */
	abstract public static function validate(
		string $string,
		bool $isExternal = false
	): void;

	/**
	 * Validates the contents of a file
	 *
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Adamkiss\Toolkit\Exception\Exception If the file does not exist
	 * @throws \Adamkiss\Toolkit\Exception\Exception On other errors
	 */
	public static function validateFile(string $file): void
	{
		$content = static::readFile($file);
		static::validate($content, isExternal: true);
	}

	/**
	 * Reads the contents of a file
	 * for sanitization or validation
	 *
	 * @throws \Adamkiss\Toolkit\Exception\Exception If the file does not exist
	 */
	protected static function readFile(string $file): string
	{
		$contents = F::read($file);

		if ($contents === false) {
			throw new Exception(
				message: 'The file "' . $file . '" does not exist'
			);
		}

		return $contents;
	}
}
