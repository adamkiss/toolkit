<?php

namespace Adamkiss\Toolkit\Form;

use Adamkiss\Toolkit\Exception\InvalidArgumentException;
use Adamkiss\Toolkit\V;

/**
 * Often used validation rules for fields
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Validations
{
	/**
	 * Validates if the field value is boolean
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function boolean($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			if (is_bool($value) === false) {
				throw new InvalidArgumentException([
					'key' => 'validation.boolean'
				]);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid date
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function date($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			if (V::date($value) !== true) {
				throw new InvalidArgumentException(
					V::message('date', $value)
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid email
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function email($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			if (V::email($value) === false) {
				throw new InvalidArgumentException(
					V::message('email', $value)
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is maximum
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function max($field, $value): bool
	{
		if ($field->isEmpty($value) === false && $field->max() !== null) {
			if (V::max($value, $field->max()) === false) {
				throw new InvalidArgumentException(
					V::message('max', $value, $field->max())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is max length
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function maxlength($field, $value): bool
	{
		if ($field->isEmpty($value) === false && $field->maxlength() !== null) {
			if (V::maxLength($value, $field->maxlength()) === false) {
				throw new InvalidArgumentException(
					V::message('maxlength', $value, $field->maxlength())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is minimum
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function min($field, $value): bool
	{
		if ($field->isEmpty($value) === false && $field->min() !== null) {
			if (V::min($value, $field->min()) === false) {
				throw new InvalidArgumentException(
					V::message('min', $value, $field->min())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is min length
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function minlength($field, $value): bool
	{
		if ($field->isEmpty($value) === false && $field->minlength() !== null) {
			if (V::minLength($value, $field->minlength()) === false) {
				throw new InvalidArgumentException(
					V::message('minlength', $value, $field->minlength())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value matches defined pattern
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function pattern($field, $value): bool
	{
		if ($field->isEmpty($value) === false && $field->pattern() !== null) {
			if (V::match($value, '/' . $field->pattern() . '/i') === false) {
				throw new InvalidArgumentException(
					V::message('match')
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is required
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function required($field, $value): bool
	{
		if ($field->isRequired() === true && $field->save() === true && $field->isEmpty($value) === true) {
			throw new InvalidArgumentException([
				'key' => 'validation.required'
			]);
		}

		return true;
	}

	/**
	 * Validates if the field value is in defined options
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function option($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			$values = array_column($field->options(), 'value');

			if (in_array($value, $values, true) !== true) {
				throw new InvalidArgumentException([
					'key' => 'validation.option'
				]);
			}
		}

		return true;
	}

	/**
	 * Validates if the field values is in defined options
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function options($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			$values = array_column($field->options(), 'value');
			foreach ($value as $val) {
				if (in_array($val, $values, true) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.option'
					]);
				}
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid time
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function time($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			if (V::time($value) !== true) {
				throw new InvalidArgumentException(
					V::message('time', $value)
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid url
	 *
	 * @param \Adamkiss\Toolkit\Form\Field|\Adamkiss\Toolkit\Form\FieldClass $field
	 * @param $value
	 * @return bool
	 * @throws \Adamkiss\Toolkit\Exception\InvalidArgumentException
	 */
	public static function url($field, $value): bool
	{
		if ($field->isEmpty($value) === false) {
			if (V::url($value) === false) {
				throw new InvalidArgumentException(
					V::message('url', $value)
				);
			}
		}

		return true;
	}
}
