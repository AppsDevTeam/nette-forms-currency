<?php


namespace ADT\Forms\Controls;

use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
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

	public static $symbols = [
		self::CURRENCY_CZK => 'Kč',
		self::CURRENCY_EUR => '€',
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
			'currencySymbolSeparator' => ' ',
		],
		self::LANGUAGE_EN => [
			'digitGroupSeparator' => ',',
			'decimalCharacter' => '.',
			'currencySymbolPlacement' => 'p',
			'roundingMethod' => 'S',
		],
		self::LANGUAGE_SK => [
			'digitGroupSeparator' => ' ',
			'decimalCharacter' => ',',
			'decimalCharacterAlternative' => '.',
			'currencySymbolPlacement' => 's',
			'roundingMethod' => 'S',
			'currencySymbolSeparator' => ' ',
		],
	];

	protected $currency;

	public function setCurrency($currency) {
		$this->currency = $currency;
	}

	public static function addCurrency(Container $container, $name, $label = null, $currency = null)
	{
		$component = (new self($label));
		$component->setCurrency($currency);

		$container->addComponent($component, $name);

		return $component;
	}


	public static function register()
	{
		Form::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
		Container::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
	}


	public function getOptions()
	{
//		https://github.com/autoNumeric/autoNumeric#options
		$options = static::$formats[static::$defaultLanguage];
		$options['currencySymbol'] = static::$symbols[$this->currency ?? static::$defaultCurrency];
		if (isset($options['currencySymbolPlacement'])) {
			if (isset($options['currencySymbolSeparator'])) {
				// suffix
				if ($options['currencySymbolPlacement'] === 's') {
					$options['currencySymbol'] = $options['currencySymbolSeparator'] . $options['currencySymbol'];
				}
				// prefix
				elseif ($options['currencySymbolPlacement'] === 'p') {
					$options['currencySymbol'] .= $options['currencySymbolSeparator'];
				}
				else {
					throw new \InvalidArgumentException("Parameter 'currencySymbolPlacement' must be either 's' or 'p'.");
				}
			}
		} else {
			$options['currencySymbol'] = '';
		}

		return $options;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->addAttributes([
			static::$defaultDataAttributeName => $this->getOptions(),
		]);
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
		$options = ArrayHash::from($this->getOptions());

		$cleanString = preg_replace('/([^0-9\.,])/i', '', $amount);
		$onlyNumbersString = preg_replace('/([^0-9])/i', '', $amount);

		$separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

		$stringWithDecimalChar = preg_replace('/([\\' . $options->decimalCharacter . '\\' . $options->decimalCharacterAlternative . '])/', '', $cleanString, $separatorsCountToBeErased);
		$removedThousandSeparator = preg_replace('/(\\' . $options->digitGroupSeparator . ')(?=[0-9]{3,}$)/', '',  $stringWithDecimalChar);

		return (float) str_replace(',', '.', $removedThousandSeparator);
	}

	protected static function setFormat(string $language, array $options)
	{
		$required = [
			'digitGroupSeparator',
			'decimalCharacter',
			'roundingMethod',
		];

		foreach ($required as $option) {
			if (! array_key_exists($option, $options)) {
				throw new \Exception('Missing required option ' . $option . '.');
			}
		}

		static::$formats[$language] = $options;
	}


}
