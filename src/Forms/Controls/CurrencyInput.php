<?php

namespace ADT\Forms\Controls;

use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Nette\Utils\Json;

class CurrencyInput extends TextInput
{
	const LANGUAGE_CS = 'cs';
	const LANGUAGE_EN = 'en';
	const LANGUAGE_SK = 'sk';

	const CURRENCY_CZK = 'CZK';
	const CURRENCY_EUR = 'EUR';
	const CURRENCY_USD = 'USD';
	const CURRENCY_GBP = 'GBP';

	public static $defaultLanguage = self::LANGUAGE_CS;
	public static $defaultCurrency = self::CURRENCY_CZK;
	public static $defaultDataAttributeName = 'data-currency-input';

	protected ?int $decimalPlaces = null;

	public static $symbols = [
		self::CURRENCY_CZK => 'Kč',
		self::CURRENCY_EUR => '€',
		self::CURRENCY_USD => '$',
		self::CURRENCY_GBP => '£',
	];

	public static $codes = [
		self::CURRENCY_CZK => 'CZK',
		self::CURRENCY_EUR => 'EUR',
		self::CURRENCY_USD => 'USD',
		self::CURRENCY_GBP => 'GBP',
	];

	protected static $formats = [
		self::LANGUAGE_CS => [
			'digitGroupSeparator' => ' ',
			'decimalCharacter' => ',',
			'decimalCharacterAlternative' => '.',
			'currencyExpression' => 'symbol',
			'currencySymbolPlacement' => 's',
			'currencySymbolSeparator' => ' ',
			'decimalPlaces' => 0,
			'allowDecimalPadding' => 'floats'
		],
		self::LANGUAGE_EN => [
			'digitGroupSeparator' => ',',
			'decimalCharacter' => '.',
			'currencyExpression' => 'symbol',
			'currencySymbolPlacement' => 'p',
			'currencySymbolSeparator' => '',
		],
		self::LANGUAGE_SK => [
			'digitGroupSeparator' => ' ',
			'decimalCharacter' => ',',
			'decimalCharacterAlternative' => '.',
			'currencyExpression' => 'symbol',
			'currencySymbolPlacement' => 's',
			'currencySymbolSeparator' => ' ',
		],
	];

	public static function addCurrency(Container $container, $name, $label = null, $currency = null, $language = null)
	{
		$component = (new self($label));
		$component->setOption('currency', $currency);
		$component->setOption('language', $language);

		$container->addComponent($component, $name);

		return $component;
	}

	public static function register()
	{
		Form::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
		Container::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
	}

	public function getFormat()
	{
		$options =  static::$formats[$this->getOption('language')] ?? static::$formats[static::$defaultLanguage];

		if ($this->decimalPlaces) {
			$options['decimalPlaces'] = $this->decimalPlaces;
		}

		switch ($options['currencyExpression']) {
			case 'symbol':
				$options['currencySymbol'] = static::$symbols[$this->getOption('currency') ?? static::$defaultCurrency];
				break;
			case 'code':
				$options['currencySymbol'] = static::$codes[$this->getOption('currency') ?? static::$defaultCurrency];
				break;
			default:
				$options['currencySymbol'] = '';
		}

		if ($options['currencySymbol'] !== '') {
			// suffix
			if ($options['currencySymbolPlacement'] === 's') {
				$options['currencySymbol'] = $options['currencySymbolSeparator'] . $options['currencySymbol'];
			} 
			// prefix
			else {
				$options['currencySymbol'] .= $options['currencySymbolSeparator'];
			}
		}

		return $options;
	}

	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl(): Html
	{
		return parent::getControl()
			->addAttributes([static::$defaultDataAttributeName => $this->getFormat()])
			->setAttribute('type', 'text');
	}

	/**
	 * Sets control's value.
	 * @param $value
	 * @return $this
	 */
	public function setValue($value)
	{
		parent::setValue($value);

		$this->value = $value ? $this->parseAmount($value) : $value;
		$this->rawValue = (string) $value;

		return $this;
	}


	public function setDecimalPlaces(int $decimalPlaces): self
	{
		$this->decimalPlaces = $decimalPlaces;
		return $this;
	}


	public function parseAmount($amount)
	{
		$options = $this->getFormat();

		$cleanString = preg_replace('/([^0-9\.,])/i', '', $amount);
		$onlyNumbersString = preg_replace('/([^0-9])/i', '', $amount);

		$separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

		$stringWithDecimalChar = preg_replace('/([\\' . $options['decimalCharacter'] . '\\' . $options['decimalCharacterAlternative'] . '])/', '', $cleanString, $separatorsCountToBeErased);
		$removedThousandSeparator = preg_replace('/(\\' . $options['digitGroupSeparator'] . ')(?=[0-9]{3,}$)/', '',  $stringWithDecimalChar);

		return (float) str_replace(',', '.', $removedThousandSeparator);
	}

	public static function setFormat(string $language, array $options)
	{
		$options = array_merge(static::$formats[$language], $options);

		foreach ($options as $key => $value) {
			if ($value === null) {
				throw new \InvalidArgumentException("Option '$key' must not be null.");
			}
		}

		if ($options['currencyExpression'] !== false && $options['currencyExpression'] !== 'symbol' && $options['currencyExpression'] !== 'code') {
			throw new \InvalidArgumentException("Option 'currencyExpression' must be either 'symbol', 'code' or false.");
		}

		if ($options['currencySymbolPlacement'] !== 's' && $options['currencySymbolPlacement'] !== 'p') {
			throw new \InvalidArgumentException("Option 'currencySymbolPlacement' must be either 's' or 'p'.");
		}

		static::$formats[$language] = $options;
	}
}
