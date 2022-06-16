<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authorization;

use PHPUnit\Framework\TestCase;
use Xylemical\Controller\RouteInterface;

/**
 * Tests \Xylemical\Controller\Authorization\Authorization.
 */
class AuthorizationTest extends TestCase {

  /**
   * Test sanity.
   */
  public function testSanity(): void {
    $route = $this->getMockBuilder(RouteInterface::class)->getMock();

    $authorization = new Authorization();
    $this->assertFalse($authorization->authorize($route));
  }

}
