<?php

namespace Xylemical\Controller\Cors;

use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\ContextInterface;

/**
 * Provides CORS support.
 */
interface CorsInterface {

  /**
   * CORS simple methods.
   */
  public const SIMPLE_METHODS = [
    'GET',
    'POST',
    'HEAD',
  ];

  /**
   * CORS simple headers, not include Content-Type.
   */
  public const SIMPLE_HEADERS = [
    'Cache-Control',
    'Content-Language',
    'Expires',
    'Last-Modified',
    'Pragma',
  ];

  /**
   * Get the allowed domains for the CORS request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed domains.
   */
  public function getAllowedOrigins(RequestInterface $request, ContextInterface $context): array;

  /**
   * Get the allowed headers for the request.
   *
   * Return an asterisk to allow all headers.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed headers.
   */
  public function getAllowedHeaders(RequestInterface $request, ContextInterface $context): array;

  /**
   * Get the allowed headers for the CORS request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed methods.
   */
  public function getAllowedMethods(RequestInterface $request, ContextInterface $context): array;

  /**
   * Get the allowed exposed headers for the response.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return string[]
   *   The allowed headers.
   */
  public function getExposedHeaders(RequestInterface $request, ContextInterface $context): array;

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
  public function usesCredentials(RequestInterface $request, ContextInterface $context): bool;

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
  public function hasCredentials(RequestInterface $request, ContextInterface $context): bool;

}
