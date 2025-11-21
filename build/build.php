<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Adamkiss\Toolkit\A;
use Adamkiss\Toolkit\Str;
use Adamkiss\Toolkit\Filesystem\F;
use Adamkiss\Toolkit\Filesystem\Dir;

$parts = [
	'<?php'
];

class Build {
	public const ROOT = __DIR__ . '/../';

	/**
	 * Removes '<?php' declaration from the beginning of a string
	 */
	public static function remove_php(string $from): string {
		return preg_replace('/^\s*<\?php[\n\s]*/', '', $from);
	}

	/**
	 * Reads a file in ROOT . $path and removes the PHP declaration
	 */
	public static function read_and_remove_php(string $path): string {
		$path = ltrim($path, '/');
		return self::remove_php(F::read(self::ROOT . '/' . $path));
	}

	/**
	 * Expands a per-file namespace into closed namespace
	 * Works only on single files withing namespace
	 */
	public static function expand_namespace(string $source): string {
		return preg_replace('/^(namespace .*?);/m', '$1 {', $source) . '}';
	}

	/**
	 * Removes namespace declaration from string
	 */
	public static function remove_namespace(string $source): string {
		return preg_replace('/^namespace .*?;[\n\s]*/m', '', $source);
	}

	/**
	 * Captures namespace
	 */
	public static function capture_namespace(string $source): ?string {
		preg_match('/^namespace (.*?);/m', $source, $matches);
		return $matches[1] ?? null;
	}

	/**
	 * Capture and remove "use XYZ;" statements
	 */
	public static function capture_and_remove_uses(string $source): array {
		$uses = [];
		$modified = preg_replace_callback('/^use (.*?);\s*$/m', function ($match) use (&$uses) {
			$uses [] = $match[1];
			return '';
		}, $source);
		return [$modified, $uses];
	}

	public static function get_dir_contents(string $path): array {
		$path = ltrim($path, '/');

		$files = Dir::files(Build::ROOT . '/' . $path);
		$files = A::keyBy($files, fn($f) => Str::lower("{$path}/{$f}"));

		$all = A::map($files, function ($f) use ($path, &$uses) {
			$__ = Build::read_and_remove_php("{$path}/{$f}");
			$source = Build::expand_namespace($__);

			return $source;
		});

		return $all;
	}

	public static function get_full_tree_contents(string $path): array {
		$path = ltrim($path, '/');
		$all = self::get_dir_contents($path);

		$dirs = Dir::dirs(Build::ROOT . '/' . $path);
		foreach ($dirs as $d) {
			$all = array_merge($all, self::get_full_tree_contents("{$path}/{$d}"));
		}

		return $all;
	}
}

/**
 * Get the dependencies
 */
$parts [] = Build::read_and_remove_php('/lib/escaper.php');

// $parts [] = "namespace Adamkiss\Toolkit\Dependencies {
//     use Exception;
//     use DOMDocument;
//     use DOMElement;
//     use stdClass;
// ";
// $parts [] = Build::read_and_remove_php();
// // fix
// $parts [] = Build::remove_php(F::read($ROOT . '/kirby/dependencies/parsedown-extra/ParsedownExtra.php'));
// $parts [] = Build::remove_php(F::read($ROOT . '/kirby/dependencies/spyc/Spyc.php'));
// $parts [] = '}';

$__ = Build::read_and_remove_php('/vendor/claviska/simpleimage/src/claviska/SimpleImage.php');
$parts [] = Build::expand_namespace($__);

$mocks = Build::get_dir_contents('/lib/CmsMocks');
$parts = array_merge($parts, array_values($mocks));

/**
 * Get the Kirby Toolkit files
 */
$files = Build::get_full_tree_contents('/src');
$sort_values = fn($file) => match($file) {
	'src/iterator.php' => 100,
	'src/sane/domhandler.php' => 501,
	'src/sane/xml.php' => 505,
	default => 1000,
};
uksort($files, fn($a, $b) => $sort_values($a) <=> $sort_values($b));

$parts = array_merge($parts, array_values($files));

/**
 * Build the single file
 */
F::write(Build::ROOT . '/dist/toolkit.php', A::join($parts, "\n"));
