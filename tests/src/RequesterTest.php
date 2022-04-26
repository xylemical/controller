<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\Exception\InvalidBodyException;

/**
 * Test the \Xylemical\Controller\Requester class.
 */
class RequesterTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the requester.
   */
  public function testRequester(): void {
    $context = $this->getMockBuilder(ContextInterface::class)->getMock();
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $child = $this->prophesize(RequesterInterface::class);
    $child->applies($request, Argument::any())->willReturn(TRUE);
    $child->getBody($request, Argument::any())->willReturn($body);

    $child = $child->reveal();

    $requester = new Requester([$child]);

    $this->assertTrue($requester->applies($request, $context));
    $this->assertEquals($body, $requester->getBody($request, $context));

    $requester = new Requester();
    $requester->addRequester($child);

    $this->assertTrue($requester->applies($request, $context));
    $this->assertEquals($body, $requester->getBody($request, $context));

    $requester = new Requester();

    $this->assertFalse($requester->applies($request, $context));
    $this->expectException(InvalidBodyException::class);
    $requester->getBody($request, $context);
  }

}
