<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Provide a generic context factory.
 */
class ContextFactory implements ContextFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function getContext(ServerRequestInterface $request): ContextInterface {
    return new Context();
  }

}
