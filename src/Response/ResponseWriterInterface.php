<?php

declare(strict_types=1);

namespace Xylemical\Controller\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides a means of writing a response.
 */
interface ResponseWriterInterface {

  /**
   * Write a response out.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   *
   * @return int|null
   *   The exit code or NULL if not the appropriate writer.
   */
  public function putResponse(ResponseInterface $response): ?int;

}
