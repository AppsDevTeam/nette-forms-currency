<?php


namespace ADT\Forms\Controls;

use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Json;


class CurrencyInput extends TextInput
{
	const LANGUAGE_CS = 'cs';
	const LANGUAGE_EN = 'en';
	const LANGUAGE_SK = 'sk';

	const CURRENCY_CZK = 'CZK';
	const CURRENCY_EUR = 'EUR';
	const CURRENCY_EUR_sk = 'EUR_sk';
	const CURRENCY_USD = 'USD';
	const CURRENCY_GBP = 'GBP';

	const CURRENCY_FORMATS = [
		self::CURRENCY_CZK => 'CZK',
		self::CURRENCY_EUR => 'EUR',
		self::CURRENCY_EUR_sk => 'EUR_sk',
		self::CURRENCY_USD => 'USD',
		self::CURRENCY_GBP => 'GBP',
	];

	public static $defaultLanguage = self::LANGUAGE_CS;
	public static $defaultCurrency = self::CURRENCY_CZK;

	public static $symbols = [
		self::CURRENCY_CZK => ' Kč',
		self::CURRENCY_EUR => '€',
		self::CURRENCY_EUR_sk => ' €',
		self::CURRENCY_USD => '$',
		self::CURRENCY_GBP => '£',
	];

	public static $formats = [
		self::LANGUAGE_CS => [
			'digitGroupSeparator' => ' ',
			'decimalCharacter' => ',',
			'decimalCharacterAlternative' => '.',
			'currencySymbolPlacement' => 's',
			'roundingMethod' => 'S',
		],
		self::LANGUAGE_EN => [
			'digitGroupSeparator' => ',',
			'decimalCharacter' => '.',
			'decimalCharacterAlternative' => '.',
			'currencySymbolPlacement' => 'p',
			'roundingMethod' => 'S',
		],
		self::LANGUAGE_SK => [
			'digitGroupSeparator' => ' ',
			'decimalCharacter' => ',',
			'decimalCharacterAlternative' => '.',
			'currencySymbolPlacement' => 's',
			'roundingMethod' => 'S',
		],
	];

	const DATA_OPTIONS_NAME = 'data-options';


	public static function addCurrency(Container $container, $name, $label = null, $currency = null)
	{
		$component = (new self($label));

		$component->getControlPrototype()->class[] = 'js-input-currency';
		$component->setAttribute(static::DATA_OPTIONS_NAME, Json::encode(static::getCurrencyOptions($currency ?? static::$defaultCurrency)));

		$container->addComponent($component, $name);

		return $component;
	}


	public static function register()
	{
		Form::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
		Container::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
	}


	protected static function getCurrencyOptions($currency)
	{
//		https://github.com/autoNumeric/autoNumeric#options
		$options = static::$formats[static::$defaultLanguage];
		$options['currencySymbol'] = static::$symbols[$currency];

		return $options;
	}


	/**
	 * Sets control's value.
	 * @param $value
	 * @return $this
	 */
	public function setValue($value)
	{
		if ($value === null) {
			$value = '';
		} elseif (!is_scalar($value) && !method_exists($value, '__toString')) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", gettype($value), $this->name));
		}
		$this->value = $value ? $this->parseAmount($value) : '';
		$this->rawValue = (string) $value;

		return $this;
	}


	public function parseAmount($amount)
	{
		$optionsName = static::DATA_OPTIONS_NAME;
		$options = $this->control->$optionsName ? Json::decode($this->control->$optionsName) : [];

		$cleanString = preg_replace('/([^0-9\.,])/i', '', $amount);
		$onlyNumbersString = preg_replace('/([^0-9])/i', '', $amount);

		$separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

		$stringWithDecimalChar = preg_replace('/([\\' . $options->decimalCharacter . '\\' . $options->decimalCharacterAlternative . '])/', '', $cleanString, $separatorsCountToBeErased);
		$removedThousandSeparator = preg_replace('/(\\' . $options->digitGroupSeparator . ')(?=[0-9]{3,}$)/', '',  $stringWithDecimalChar);

		return (float) str_replace(',', '.', $removedThousandSeparator);
	}


}