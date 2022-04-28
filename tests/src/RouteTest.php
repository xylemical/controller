<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Tests \Xylemical\Controller\Route.
 */
class RouteTest extends TestCase {

  /**
   * Test sanity.
   */
  public function testSanity(): void {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $context = $this->getMockBuilder(ContextInterface::class)->getMock();

    $args = ['arg' => TRUE];
    $route = new Route('test', $args, $request, $context);
    $this->assertEquals('test', $route->getName());
    $this->assertEquals($args, $route->getArguments());
    $this->assertSame($request, $route->getRequest());
    $this->assertSame($context, $route->getContext());

    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $this->assertNotSame($request, $route->getRequest());
    $route->setRequest($request);
    $this->assertSame($request, $route->getRequest());

  }

}
