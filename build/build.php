<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
		return preg_replace('/(namespace .*?);/', '$1 {', $source) . '}';
	}

	/**
	 * Removes namespace declaration from string
	 */
	public static function remove_namespace(string $source): string {
		return preg_replace('/^namespace .*?;[\n\s]*/m', '', $source);
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
}

/**
 * Get the dependencies
 */
$parts [] = Build::read_and_remove_php('/lib/escaper.php');

$parts [] = "namespace Adamkiss\Toolkit\Dependencies {
    use Exception;
    use DOMDocument;
    use DOMElement;
    use stdClass;
";
$parts [] = Build::read_and_remove_php();
// fix
$parts [] = Build::remove_php(F::read($ROOT . '/kirby/dependencies/parsedown-extra/ParsedownExtra.php'));
$parts [] = Build::remove_php(F::read($ROOT . '/kirby/dependencies/spyc/Spyc.php'));
$parts [] = '}';

$__ = Build::remove_php(F::read($ROOT . '/vendor/claviska/simpleimage/src/claviska/SimpleImage.php'));
$parts [] = Build::expand_namespace($__);

/**
 * Get the Kirby Toolkit files
 */
$files = Dir::index(Build::ROOT . '/src', true);
rd($files);

// Remove some
$files = A::filter($files, fn ($f) => !in_array($f, [
	'/Filesystem/Asset.php',
	'/Toolkit/Query.php'
]));

/**
 * - Remove PHP and namespace
 * - filter and fix "use clauses"
 * - wrap in namespace Toolkit {}
 */
$all_uses = [];
$files = A::map($files, function ($file) use ($ROOT, &$all_uses) {
	if (F::extension($file) !== 'php') {
		return '';
	}

	$content = remove_php(F::read($ROOT . "/kirby/src/{$file}"));
	$content = remove_namespace($content);
	[$content, $uses] = capture_and_remove_uses($content);

	if (!empty($uses)) {
		ray()->table($uses)->label($file);
	}

	$all_uses = A::merge($all_uses, $uses);
	return trim($content);
});

$uses = A::filter(A::map(array_unique($all_uses), function (string $use) {
	if (preg_match('/^Kirby\\\\(?:Exception|Filesystem|Http|Image|Text|Toolkit)/', $use)) {
		return '';
	}
	$use = str_replace('Kirby', 'Mocks', $use);
	return match(true) {
		Str::startsWith($use, 'Parsedown') => "Toolkit\\Dependencies\\{$use}",
		!Str::contains($use, '\\') => "\\{$use}",
		default => $use
	};
}), fn ($mapped) => !empty($mapped ?? []));
sort($uses);

$parts = A::merge(
	$parts,
	['namespace Toolkit {'],
	A::map($uses, fn ($use) => "use {$use};"),
	[''],
	$files,
	['}']
);

/**
 * Build the single file
 */
F::write($ROOT . '/dist/KirbyToolkit.php', A::join($parts, "\n"));
