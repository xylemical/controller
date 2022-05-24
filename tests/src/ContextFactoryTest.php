<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Tests \Xylemical\Controller\ContextFactory.
 */
class ContextFactoryTest extends TestCase {

  /**
   * Test the factory.
   */
  public function testFactory(): void {
    $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();

    $factory = new ContextFactory();

    $context = $factory->getContext($request);
    $this->assertEquals(Context::class, get_class($context));
  }

}
