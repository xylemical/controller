<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xylemical\Controller\Exception\UnavailableException;
use Xylemical\Controller\Exception\UnhandledResponseException;

/**
 * Test the \Xylemical\Controller\Responder class.
 */
class ResponderTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the responder.
   */
  public function testResponder() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $result = $this->getMockBuilder(ResultInterface::class)->getMock();
    $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

    $child = $this->prophesize(ResponderInterface::class);
    $child->applies($request, $result)->willReturn(TRUE);
    $child->getResponse($request, $result)->willReturn($response);

    $child = $child->reveal();

    $responder = new Responder([$child]);

    $this->assertTrue($responder->applies($request, $result));
    $this->assertEquals($response, $responder->getResponse($request, $result));

    $responder = new Responder();
    $responder->addResponder($child);

    $this->assertTrue($responder->applies($request, $result));
    $this->assertEquals($response, $responder->getResponse($request, $result));

    $responder = new Responder();

    $this->assertFalse($responder->applies($request, $result));
    $this->expectException(UnhandledResponseException::class);
    $responder->getResponse($request, $result);
  }

}
