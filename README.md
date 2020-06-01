# Currency input for Nette Forms

## Installation


Install **[autoNumeric](https://github.com/autoNumeric/autoNumeric)** library.
Please note that this library is required!

Copy `CurrencyInput.js` into your public directory and then link it into your `@layout.latte` file. 

Install currency input library via composer:

```sh
composer require adt/nette-forms-currency
```

and register method extension in `bootstrap.php`:

```php
\ADT\Forms\Controls\CurrencyInput::register();
```

This allows you to call the method `addCurrency` on class `Nette\Forms\Form` or `Nette\Forms\Container`.

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


To turn on autocomplete in IDE, add `@method CurrencyInput addCurrency($name, $label = null, $language = 'cs')` to your base form.
