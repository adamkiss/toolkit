<?php

namespace Kirby\Cms;

class Helpers {
	public static function handleErrors(\Closure $act, \Closure $rethrow, mixed $return = false): mixed {
		try {
			return $act();
		} catch (\Throwable $th) {
			if ($rethrow($th->getCode(), $th->getMessage()) !== true) {
				return $return;
			}

			throw $th;
		}
	}
}
