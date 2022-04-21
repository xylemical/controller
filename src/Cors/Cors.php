<?php

namespace Xylemical\Controller\Cors;

use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\ContextInterface;
use Xylemical\Controller\Controller;
use Xylemical\Controller\Headers;

/**
 * Provides the CORS support.
 */
class Cors implements CorsInterface {

  /**
   * The CORS supported objects.
   *
   * @var \Xylemical\Controller\Cors\CorsInterface[]
   */
  protected array $cors = [];

  /**
   * The cache supported objects.
   *
   * @var array
   */
  protected array $cache = [];

  /**
   * The response headers.
   *
   * @var string[]
   */
  protected array $headers = [];

  /**
   * Cors constructor.
   *
   * @param \Xylemical\Controller\Controller $controller
   *   The controller.
   */
  public function __construct(Controller $controller) {
    $objects = [
      $controller->getRequester(),
      $controller->getResponder(),
      $controller->getProcessor(),
    ];
    $objects = array_merge($objects, $controller->getMiddleware());

    $this->cors = array_filter($objects, function ($item) {
      return $item instanceof CorsInterface;
    });
  }

  /**
   * The allowed origins.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed origins.
   */
  public function getAllowedOrigins(RequestInterface $request, ContextInterface $context): array {
    $origin = $request->getHeaderLine('Origin');
    foreach ($this->cors as $object) {
      if (in_array('*', $object->getAllowedOrigins($request, $context))) {
        return ['*'];
      }
    }
    return [$origin];
  }

  /**
   * The allowed origins.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed origins.
   */
  public function getAllowedHeaders(RequestInterface $request, ContextInterface $context): array {
    $headers = [];
    foreach ($this->cors as $object) {
      $result = Headers::normalize($object->getAllowedHeaders($request, $context));
      if (in_array('*', $result)) {
        return [$request->getHeader('Access')];
      }
      $headers = array_unique(array_merge($headers, $result));
    }
    return $headers;
  }

  /**
   * The allowed methods.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed methods.
   */
  public function getAllowedMethods(RequestInterface $request, ContextInterface $context): array {
    $methods = [];
    foreach ($this->cors as $object) {
      $result = $object->getAllowedMethods($request, $context);
      if (in_array('*', $result)) {
        return [$request->getHeaderLine('Access-Control-Request-Method')];
      }
      $methods = array_unique(array_merge($methods, $result));
    }
    return $methods;
  }

  /**
   * The exposed headers.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed methods.
   */
  public function getExposedHeaders(RequestInterface $request, ContextInterface $context): array {
    $headers = [];
    foreach ($this->cors as $object) {
      $result = Headers::normalize($object->getAllowedMethods($request, $context));
      if (in_array('*', $result)) {
        return Headers::normalize(array_keys($request->getHeaders()));
      }
      $headers = array_unique(array_merge($headers, $result));
    }
    return $headers;
  }

  /**
   * Check the request uses credentials.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return bool
   *   The result.
   */
  public function usesCredentials(RequestInterface $request, ContextInterface $context): bool {
    foreach ($this->cors as $item) {
      if ($item->usesCredentials($request, $context)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Check the request has credentials.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return bool
   *   The result.
   */
  public function hasCredentials(RequestInterface $request, ContextInterface $context): bool {
    foreach ($this->cors as $item) {
      if ($item->hasCredentials($request, $context)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Get the response headers.
   *
   * @return string[][]
   *   The headers.
   */
  public function getResponseHeaders(): array {
    $headers = $this->headers;
    ksort($headers);
    return array_combine(
      Headers::normalize(array_keys($headers)),
      array_values($headers)
    );
  }

  /**
   * Set the response headers.
   *
   * @param string[][] $headers
   *   The headers.
   *
   * @return $this
   */
  public function setResponseHeaders(array $headers): static {
    $this->headers = [];
    foreach ($headers as $header => $values) {
      if (is_array($values)) {
        foreach ($values as $value) {
          $this->addResponseHeader($header, $value);
        }
      }
      elseif (is_string($values) || is_numeric($values)) {
        $this->addResponseHeader($header, (string)$values);
      }
    }
    return $this;
  }

  /**
   * Set the response header.
   *
   * @param string $header
   *   The header.
   * @param string $value
   *   The value.
   *
   * @return $this
   */
  public function setResponseHeader(string $header, string $value): static {
    $this->headers[$header] = [];
    return $this->addResponseHeader($header, $value);
  }

  /**
   * Add a response header.
   *
   * @param string $header
   *   The header.
   * @param string $value
   *
   * @return $this
   */
  public function addResponseHeader(string $header, string $value): static {
    $this->headers[$header][] = $value;
    return $this;
  }

}
