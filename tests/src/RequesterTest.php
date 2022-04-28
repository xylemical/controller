<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
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
    $route = $this->getMockBuilder(RouteInterface::class)->getMock();
    $body = ['body'];

    $child = $this->prophesize(RequesterInterface::class);
    $child->applies($route, Argument::any())->willReturn(TRUE);
    $child->getBody($route, Argument::any())->willReturn($body);

    $child = $child->reveal();

    $requester = new Requester([$child]);

    $this->assertTrue($requester->applies($route));
    $this->assertEquals($body, $requester->getBody($route));

    $requester = new Requester();
    $requester->addRequester($child);

    $this->assertTrue($requester->applies($route));
    $this->assertEquals($body, $requester->getBody($route));

    $requester = new Requester();

    $this->assertFalse($requester->applies($route));
    $this->expectException(InvalidBodyException::class);
    $requester->getBody($route);
  }

}
