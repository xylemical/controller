<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides a base for middleware processors.
 */
class Middleware implements MiddlewareInterface {

  /**
   * The priority value.
   *
   * @var int
   */
  protected int $priority;

  /**
   * Middleware constructor.
   *
   * @param int $priority
   *   The priority.
   */
  public function __construct(int $priority = 0) {
    $this->priority = $priority;
  }

  /**
   * {@inheritdoc}
   */
  public function priority(): int {
    return $this->priority;
  }

  /**
   * {@inheritdoc}
   */
  public function request(RouteInterface $route): void {
  }

  /**
   * {@inheritdoc}
   */
  public function response(RouteInterface $route, ResponseInterface $response): ResponseInterface {
    return $response;
  }

}
