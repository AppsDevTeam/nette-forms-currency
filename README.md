# Currency input for Nette Forms
Nette component for creating currency input.

## Installation

Install currency input library via composer:

```sh
composer require adt/nette-forms-currency
```

and register method extension in `bootstrap.php`:

```php
\ADT\Forms\Controls\CurrencyInput::register();
```

This allows you to call the method `addCurrency` on class `Nette\Forms\Form` or `Nette\Forms\Container`.

For live formatting functionality its necessary to create custom js. <br> You can use our example bellow.
Install and link following libraries: <br>
[nette.ajax.js](https://github.com/vojtech-dobes/nette.ajax.js) <br>
[autoNumeric](https://github.com/autoNumeric/autoNumeric)

Initialize autoNumeric on currency input
```js
$(function () {
	$.nette.ext('live').after(function ($element) {
		$element.find('.js-nette-forms-currency').each(function () {
			new AutoNumeric(this, JSON.parse(this.dataset.options));
		});
	});
})
```

## Usage

It's very simple:

```php
$form->addCurrency('currency', 'label', \ADT\Forms\Controls\CurrencyInput::CURRENCY_CZK);
  
$form->onSuccess[] = function ($form) {
	$form['currency']->getValue(); // returns eg. from "100 000 000,56 kč" => "100000000.56" 
};
```

And in latte:

```latte
{input currency}
```


To turn on autocomplete in IDE, add `@method CurrencyInput addCurrency($name, $label = null, $currency = null, $language = null)` to your base form.
