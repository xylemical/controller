<?php

declare(strict_types=1);

namespace Xylemical\Controller\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides a means of writing a response.
 */
interface ResponseWriterInterface {

  /**
   * Check the writer applies to the response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   *
   * @return bool
   *   The result.
   */
  public function applies(ResponseInterface $response): bool;

  /**
   * Write a response out.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   *
   * @return int
   *   The exit code.
   */
  public function putResponse(ResponseInterface $response): int;

}
