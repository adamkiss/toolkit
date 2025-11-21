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

	public static function get_dir_contents_and_uses(string $path): array {
		$path = ltrim($path, '/');
		$files = Dir::files(Build::ROOT . '/' . $path);

		$namespace = Build::capture_namespace(F::read(Build::ROOT . "/{$path}/{$files[0]}"));
		$uses = [];

		$all = A::map($files, function ($f) use ($path, &$uses) {
			$__ = Build::read_and_remove_php("{$path}/{$f}");
			$__ = Build::remove_namespace($__);
			[$__, $file_uses] = Build::capture_and_remove_uses($__);

			$source = trim($__);
			$uses = array_merge($uses, $file_uses);

			return $source;
		});

		$contents = sprintf(<<<'PHP'
			namespace %s {
				%s
			}
			PHP,
			$namespace,
			A::join($all, "\n\n")
		);
		return [$contents, $uses];
	}

	public static function get_full_tree_contents(string $path): array {
		$path = ltrim($path, '/');

		[$contents, $uses] = self::get_dir_contents_and_uses($path);

		$all = [$contents];

		$dirs = Dir::dirs(Build::ROOT . '/' . $path);
		foreach ($dirs as $d) {
			[$dir_contents, $dir_uses] = self::get_full_tree_contents("{$path}/{$d}");
			$all = array_merge($all, $dir_contents);
			$uses = array_merge($uses, $dir_uses);
		}

		return [$all, $uses];
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

/**
 * Get the Kirby Toolkit contents and uses
 */
[$toolkit_contents, $toolkit_uses] = Build::get_full_tree_contents('/src');

/*
	Compile `use` statements
	- unique
	- replace PHP Exceptions with Toolkit Exceptions
	- sort by namespace depth and alphabetically
*/
$uses = array_unique($toolkit_uses);
$uses = A::map($uses, fn ($u) => match($u) {
	'Exception' => 'Adamkiss\Toolkit\Exception\Exception',
	'InvalidArgumentException' => 'Adamkiss\Toolkit\Exception\InvalidArgumentException',
	default => $u,
});
usort($uses, fn ($a, $b) => match(substr_count($a, '\\') <=> substr_count($b, '\\')) {
	0 => strcmp($a, $b),
	default => substr_count($a, '\\') <=> substr_count($b, '\\'),
});
$parts [] = A::join(A::map($uses, fn ($u) => "use {$u};"), "\n");

/* Now add the contents */
$parts = array_merge($parts, $toolkit_contents);

/**
 * Build the single file
 */
F::write(Build::ROOT . '/dist/toolkit.php', A::join($parts, "\n"));
