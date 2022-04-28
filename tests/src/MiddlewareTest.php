<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests \Xylemical\Controller\Middleware.
 */
class MiddlewareTest extends TestCase {

  /**
   * Test sanity.
   */
  public function testSanity(): void {
    $middleware = new Middleware(10);
    $this->assertEquals(10, $middleware->priority());

    $route = $this->getMockBuilder(RouteInterface::class)->getMock();
    $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
    $this->assertSame($response, $middleware->response($route, $response));

  }

}
