<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides for generating controllers.
 */
interface ControllerFactoryInterface {

  /**
   * Create the controller.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\Controller
   *   The controller.
   */
  public function getController(RequestInterface $request): Controller;

}
