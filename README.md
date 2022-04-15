# Data types

Provides a framework for http server controller responses.

## Install

The recommended way to install this library is [through composer](http://getcomposer.org).

```sh
composer require xylemical/controller
```

## Usage

```php
<?php

use Xylemical\Controller\Controller;

$requester = ...; // class based on Xylemical\Controller\RequesterInterface
$responder = ...; // class based on Xylemical\Controller\ResponderInterface
$processor = ...; // class based on Xylemical\Controller\ProcessorInterface

$controller = new Controller($requester, $responder, $processor);

// Both $request and $response are Psr-4 compatible interfaces.
$response = $controller->handle($response);

```

## License

MIT, see LICENSE.
