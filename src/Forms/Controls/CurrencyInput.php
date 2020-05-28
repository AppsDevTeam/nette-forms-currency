<?php


namespace ADT\Forms\Controls;

use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Json;


class CurrencyInput extends TextInput
{
	const LANGUAGE_CZ = 'CZ';

	const LANGUAGE_OPTIONS = [
		self::LANGUAGE_CZ
	];

	const DATA_OPTIONS_NAME = 'data-options';


	public static function addCurrency(Container $container, $name, $label = null, $language = 'CZ')
	{
		$component = (new self($label));

		$component->getControlPrototype()->class[] = 'js-input--currency';
		$component->setAttribute(static::DATA_OPTIONS_NAME, Json::encode(static::getCurrencyOptions($language)));

		$container->addComponent($component, $name);

		return $component;
	}


	public static function register()
	{
		Form::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
		Container::extensionMethod('addCurrency', [__CLASS__, 'addCurrency']);
	}


	protected static function getCurrencyOptions($language)
	{
		if (! in_array($language, static::LANGUAGE_OPTIONS)) {
			throw (new \Exception('Wrong currency language option.'));
		}

//		https://github.com/autoNumeric/autoNumeric#options
		$options = [
			'digitGroupSeparator' => ' ',
			'decimalCharacter' => ',',
			'decimalCharacterAlternative' => '.',
			'currencySymbol' => '\u202f€',
			'currencySymbolPlacement' => 's',
			'roundingMethod' => 'S',
		];

		if ($language === self::LANGUAGE_CZ) {
			$options['currencySymbol'] = ' kč';
		}

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