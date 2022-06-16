<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

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
    $child->getBody($route, Argument::any())->willReturn($body);

    $child = $child->reveal();

    $requester = new Requester([$child]);

    $this->assertEquals($body, $requester->getBody($route));

    $requester = new Requester();
    $requester->addRequester($child);

    $this->assertEquals($body, $requester->getBody($route));

    $requester = new Requester();

    $this->assertNull($requester->getBody($route));
  }

}
