<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provide a generic context factory.
 */
class ContextFactory implements ContextFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function getContext(RequestInterface $request): ContextInterface {
    return new Context();
  }

}
