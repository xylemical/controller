<?php

declare(strict_types=1);

namespace Xylemical\Controller\Request;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides a reader for creating requests.
 */
interface RequestReaderInterface {

  /**
   * Read a request.
   *
   * @return \Psr\Http\Message\ServerRequestInterface|null
   *   The request or NULL.
   */
  public function getRequest(): ?ServerRequestInterface;

}
