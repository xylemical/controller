<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\Exception\InvalidBodyException;
use Xylemical\Controller\Exception\UnavailableException;

/**
 * Test the \Xylemical\Controller\Requester class.
 */
class RequesterTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the requester.
   */
  public function testRequester() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $child = $this->prophesize(RequesterInterface::class);
    $child->applies($request)->willReturn(TRUE);
    $child->getBody($request)->willReturn($body);

    $child = $child->reveal();

    $requester = new Requester([$child]);

    $this->assertTrue($requester->applies($request));
    $this->assertEquals($body, $requester->getBody($request));

    $requester = new Requester();
    $requester->addRequester($child);

    $this->assertTrue($requester->applies($request));
    $this->assertEquals($body, $requester->getBody($request));

    $requester = new Requester();

    $this->assertFalse($requester->applies($request));
    $this->expectException(InvalidBodyException::class);
    $requester->getBody($request);
  }

}