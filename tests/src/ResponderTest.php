<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Test the \Xylemical\Controller\Responder class.
 */
class ResponderTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the responder.
   */
  public function testResponder(): void {
    $route = $this->getMockBuilder(RouteInterface::class)->getMock();
    $result = $this->getMockBuilder(ResultInterface::class)->getMock();
    $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

    $child = $this->prophesize(ResponderInterface::class);
    $child->getResponse($route, $result, Argument::any())
      ->willReturn($response);

    $child = $child->reveal();

    $responder = new Responder([$child]);

    $this->assertEquals($response, $responder->getResponse($route, $result));

    $responder = new Responder();
    $responder->addResponder($child);

    $this->assertEquals($response, $responder->getResponse($route, $result));

    $responder = new Responder();

    $this->assertNull($responder->getResponse($route, $result));
  }

}
