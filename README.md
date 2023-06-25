# Ramzinex

# <a href="https://documenter.getpostman.com/view/15475713/UyxnD4dH">Ramzinex API Document</a>

## Installation

<p>
First of all, You need to make an account on Ramzinex exchange from <a href="https://ramzinex.com/exchange/pt/authentication">Ramzinex</a>
</p>
<p>
After that you just need to pick your token
</p>
<hr>

Use in these ways :

```php
composer require "ramzinex/sdk:dev-main"

```

or add

```php
"ramzinex/sdk": "dev-main",
```

and

```php
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kosar501/ramzinex-sdk"
        }
    ],
```

to your composer.json file and then run

```php
$ composer update
```

Usage
-----

There is an example

```php
require __DIR__ . '/vendor/autoload.php';
	$ramzinex_api = new \ramzinex\new RamzinexApi('ramzinex_secretkey','ramzinex_apikey');
	
	
	$price =$ramzinex_api->getPrice(272);
	
	if($price['http_code'] == 200){
		return $price;	
	}
```

<div dir='rtl'>

## راهنما

### ساخت حساب کاربری

اگر در رمزینکس عضو نیستید میتوانید از [لینک عضویت](https://ramzinex.com/exchange/pt/authentication) ثبت نام کنید.

### مستندات

برای مطالعه کامل مستندات رمزینکس به صفحه [مستندات رمزینکس](https://documenter.getpostman.com/view/15475713/UyxnD4dH)
مراجعه کنید.

##

![ramzinex](https://ramzinex.com/exchange/pt/static/media/logo-dark.254200c0c6e2e4874067db61e5b45cf6.svg)

[https://ramzinex.com/](https://ramzinex.com)

</div>