<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use PHPUnit\Framework\TestCase;
use Xylemical\Controller\RouteInterface;

/**
 * Tests \Xylemical\Controller\Authentication\Authentication.
 */
class AuthenticationTest extends TestCase {

  /**
   * Test sanity.
   */
  public function testSanity(): void {
    $route = $this->getMockBuilder(RouteInterface::class)->getMock();
    $authentication = new Authentication();
    $this->assertNull($authentication->authenticate($route));
  }

}
