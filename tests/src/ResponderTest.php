<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xylemical\Controller\Exception\UnhandledResponseException;

/**
 * Test the \Xylemical\Controller\Responder class.
 */
class ResponderTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the responder.
   */
  public function testResponder(): void {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $result = $this->getMockBuilder(ResultInterface::class)->getMock();
    $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
    $context = $this->getMockBuilder(ContextInterface::class)->getMock();

    $child = $this->prophesize(ResponderInterface::class);
    $child->applies($request, $result, Argument::any())->willReturn(TRUE);
    $child->getResponse($request, $result, Argument::any())->willReturn($response);

    $child = $child->reveal();

    $responder = new Responder([$child]);

    $this->assertTrue($responder->applies($request, $result, $context));
    $this->assertEquals($response, $responder->getResponse($request, $result, $context));

    $responder = new Responder();
    $responder->addResponder($child);

    $this->assertTrue($responder->applies($request, $result, $context));
    $this->assertEquals($response, $responder->getResponse($request, $result, $context));

    $responder = new Responder();

    $this->assertFalse($responder->applies($request, $result, $context));
    $this->expectException(UnhandledResponseException::class);
    $responder->getResponse($request, $result, $context);
  }

}
