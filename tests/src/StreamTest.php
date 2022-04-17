<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests \Xylemical\Controller\Stream.
 *
 * Tests against the requirements of the Psr-7 StreamInterface requirements.
 */
class StreamTest extends TestCase {

  /**
   * Test basic stream functionality.
   */
  public function testStream() {
    $message = 'Test Message';
    $size = strlen($message);

    $stream = new Stream($message);
    $this->assertEquals($size, $stream->getSize());
    $this->assertNull($stream->getMetadata('context'));
    $this->assertTrue($stream->isReadable());
    $this->assertTrue($stream->isSeekable());
    $this->assertFalse($stream->isWritable());
    $this->assertNull($stream->detach());

    $this->assertEquals(0, $stream->tell());
    $this->assertEquals(substr($message, 0, 4), $stream->read(4));
    $this->assertFalse($stream->eof());
    $this->assertEquals(4, $stream->tell());
    $this->assertEquals(substr($message, 4), $stream->read($size));
    $this->assertEquals($size, $stream->tell());
    $this->assertEquals('', $stream->getContents());
    $this->assertTrue($stream->eof());
    $this->assertEquals($message, (string)$stream);

    $stream->rewind();
    $this->assertEquals($message, $stream->getContents());

    $stream->seek(-1, SEEK_END);
    $this->assertEquals(substr($message, -1), $stream->getContents());

    $stream->seek(1);
    $this->assertEquals(substr($message, 1), $stream->getContents());

    $stream->seek(5);
    $stream->seek(-2, SEEK_CUR);
    $this->assertEquals(substr($message, 3), $stream->getContents());

    $this->expectException(\RuntimeException::class);
    $stream->write($message);
  }

}
