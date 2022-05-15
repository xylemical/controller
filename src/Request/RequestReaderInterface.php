<?php

declare(strict_types=1);

namespace Xylemical\Controller\Request;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a reader for creating requests.
 */
interface RequestReaderInterface {

  /**
   * Check the request factory applies.
   *
   * @return bool
   *   The result.
   */
  public function applies(): bool;

  /**
   * Read a request.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The request.
   */
  public function getRequest(): RequestInterface;

}
