<?php

namespace Xylemical\Controller\Cors;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xylemical\Controller\ContextInterface;
use Xylemical\Controller\Controller;
use Xylemical\Controller\Cors\Exception\CorsException;
use Xylemical\Controller\MiddlewareInterface;

/**
 * Provides CORS support for controllers.
 *
 * @see http://www.w3.org/TR/cors/
 * @see http://www.w3.org/TR/2014/REC-cors-20140116/
 */
class CorsMiddleware implements MiddlewareInterface {

  /**
   * The CORS simple headers.
   */
  protected const SIMPLE_HEADERS = [
    'GET',
    'POST',
    'HEAD',
  ];

  /**
   * The priority of the middleware.
   *
   * @var int
   */
  protected int $priority = 1000;

  /**
   * {@inheritdoc}
   */
  public function priority(): int {
    return $this->priority;
  }

  /**
   * {@inheritdoc}
   */
  public function request(Controller $controller, RequestInterface $request, ContextInterface $context): RequestInterface {
    $cors = $this->getSupported($controller);

    if (!$this->isCors($request, $cors)) {
      return $request;
    }

    if (!$this->isAllowedOrigin($request, $cors)) {
      throw new CorsException('Request not being made from an allowed origin.');
    }

    if ($this->isPreflight($request)) {
      return $this->doPreflight($request, $context, $cors);
    }

    return $this->doRequest($request, $context, $cors);
  }

  /**
   * {@inheritdoc}
   */
  public function response(Controller $controller, ResponseInterface $response, ContextInterface $context): ResponseInterface {
    // TODO: Implement response() method.
  }


  /**
   * Get the CORS supported objects.
   *
   * @param \Xylemical\Controller\Controller $controller
   *   The controller.
   *
   * @return \Xylemical\Controller\Cors\CorsInterface[]
   *   The CORS supported objects.
   */
  protected function getSupported(Controller $controller): array {
    $objects = [
      $controller->getRequester(),
      $controller->getResponder(),
      $controller->getProcessor(),
    ];
    $objects = array_merge($objects, $controller->getMiddleware());
    return array_filter($objects, function ($item) {
      return $item instanceof CorsInterface;
    });
  }

  /**
   * Check this is a CORS request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return bool
   *   The result.
   */
  protected function isCors(RequestInterface $request, array $cors): bool {
    if (!$request->hasHeader('Origin')) {
      return FALSE;
    }

    // Check Origin matches the current server information.

    return TRUE;
  }

  protected function isAllowedOrigin(RequestInterface $request, array $cors): bool {
    // Check allowed origins (throw cors exception if not in the allowed origins.
    return TRUE;
  }

  /**
   * Check the request is a pre-flight request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return bool
   *   The result.
   */
  protected function isPreflight(RequestInterface $request): bool {
    return $request->getMethod() === 'OPTIONS';
  }

  /**
   * Check the headers are supported.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return bool
   *   The result.
   */
  protected function areHeadersSupported(RequestInterface $request, ContextInterface $context, array $cors): bool {
    if (!$request->hasHeader('')) {
      return TRUE;
    }

    $allowed = [];
    foreach ($cors as $object) {
      $allowed = array_merge($allowed, $object->getAllowedHeaders($request, $context));
    }

    if (in_array('*', $allowed)) {
      return TRUE;
    }

    $allowed = (new Header($allowed))
      ->normalize();
    $headers = (new Header($request->getHeader('Access-Control-Request-Headers')))
      ->normalize();

    return count(array_intersect($allowed, $headers)) === count($headers);
  }

  /**
   * Check the credentials are expected for this request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return bool
   *   The result.
   */
  protected function usesCredentials(RequestInterface $request, ContextInterface $context, array $cors): bool {
    $uses = FALSE;
    foreach ($cors as $object) {
      $uses = $uses || $object->usesCredentials($request, $context);
    }
    return $uses;
  }

  /**
   * Get the allowed origin.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return string
   *   The allowed origin.
   */
  protected function getAllowedOrigin(RequestInterface $request, ContextInterface $context, array $cors): string {
    $origin = $request->getHeaderLine('Origin');
    foreach ($cors as $object) {
      if (in_array('*', $object->getAllowedOrigins($request, $context))) {
        return '*';
      }
    }
    return $origin;
  }

  /**
   * Get the allowed methods.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return string[]
   *   The allowed methods.
   */
  protected function getAllowedMethods(RequestInterface $request, ContextInterface $context, array $cors): array {
    $methods = [];
    foreach ($cors as $object) {
      $result = $object->getAllowedMethods($request, $context);
      if (in_array('*', $result)) {
        return [$request->getHeaderLine('Access-Control-Request-Method')];
      }
      $methods = array_unique(array_merge($methods, $result));
    }

    return $methods;
  }

  /**
   * Get the allowed headers.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return string[]
   *   The allowed headers.
   */
  protected function getAllowedHeaders(RequestInterface $request, ContextInterface $context, array $cors): array {
    $headers = [];
    foreach ($cors as $object) {
      $result = $object->getAllowedHeaders($request, $context);
      $headers = array_unique(array_merge($headers, $result));
    }
    return $headers;
  }


  /**
   * Get the preflight headers.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   * @param string[] $methods
   *   The allowed methods.
   * @param string[] $headers
   *   The allowed headers.
   *
   * @return string[]
   *   The headers.
   */
  protected function getPreflightHeaders(RequestInterface $request, ContextInterface $context, array $cors, array $methods, array $headers): array {
    $headers = [
      'Vary' => 'Origin',
    ];

    if ($this->usesCredentials($request, $context, $cors)) {
      $headers['Access-Control-Allow-Origin'] = $request->getHeaderLine('Origin');
      $headers['Access-Control-Allow-Credentials'] = 'true';
    }
    else {
      $headers['Access-Control-Allow-Origin'] = $this->getAllowedOrigin(
        $request,
        $context,
        $cors
      );
    }

    //    $headers['Access-Control-Max-Age'] = 0;

    if (!in_array($request->getMethod(), self::SIMPLE_HEADERS)) {
      $headers['Access-Control-Allow-Methods'] = $this->getAllowedMethods(
        $request,
        $context,
        $cors
      );
    }


    ksort($headers);
    return $headers;
  }

  /**
   * Perform a CORS preflight request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   * @param \Xylemical\Controller\Cors\CorsInterface[] $cors
   *   The CORS supported objects.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The request.
   */
  protected function doPreflight(RequestInterface $request, ContextInterface $context, array $cors): RequestInterface {
    if (!$request->hasHeader('Access-Control-Request-Method')) {
      return $request;
    }

    $context->set('cors', 'preflight');

    $methods = $this->getAllowedMethods($request, $context, $cors);
    if (!in_array('*', $methods)) {
      if (!in_array($request->getHeaderLine('Access-Control-Request-Method'), $methods)) {
        throw new CorsException('Request method not supported.');
      }
    }

    $headers = $this->getAllowedHeaders($request, $context, $cors);
    if (!in_array('*', $headers)) {
      // Adjust the headers
      throw new CorsException('Request headers not supported.');
    }

    if (!$this->areHeadersSupported($request, $context, $cors)) {
    }

    $context->set('cors.headers', $this->getPreflightHeaders(
      $request,
      $context,
      $cors,
      $methods,
      $headers
    ));

    return $request;
  }

  // Request Headers
  // Origin
  // Access-Control-Request-Method
  // Access-Control-Request-Headers
  //

  // Response Headers
  // Access-Control-Allow-Origin
  // Access-Control-Expose-Headers
  // Access-Control-Max-Age
  // Access-Control-Allow-Credentials
  // Access-Control-Allow-Methods
  // Access-Control-Allow-Headers
  //

}
