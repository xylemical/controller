<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests \Xylemical\Controller\Context.
 */
class ContextTest extends TestCase {

  /**
   * Test the context.
   */
  public function testContext(): void {
    $context = new Context(['test' => 1]);
    $this->assertEquals(['test' => 1], $context->all());
    $this->assertTrue($context->has('test'));
    $this->assertEquals(1, $context->get('test'));
    $this->assertFalse($context->has('foo'));

    $context->set('test', 2);
    $this->assertEquals(['test' => 2], $context->all());
    $this->assertEquals(2, $context->get('test'));

    $context->remove('test');
    $this->assertEquals([], $context->all());
    $this->assertFalse($context->has('test'));
    $this->assertNull($context->get('test'));
  }

}
