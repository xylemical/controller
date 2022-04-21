<?php

namespace Xylemical\Controller\Cors\Exception;

/**
 * Triggers on a CORS error.
 */
class CorsException extends \Exception {

  /**
   * {@inheritdoc}
   */
  public function __construct(string $message = "", int $code = 0, ?Throwable $previous = NULL) {
    parent::__construct($message, $code ?: 401, $previous);
  }

}
