<?php

namespace Xylemical\Controller;

use Psr\Http\Message\StreamInterface;

/**
 * A generic response stream.
 */
class Stream implements StreamInterface {

  /**
   * The contents.
   *
   * @var string
   */
  protected string $contents;

  /**
   * The position within the contents.
   *
   * @var int
   */
  protected int $pointer;

  /**
   * ResponseBody constructor.
   *
   * @param string $contents
   *   The contents.
   */
  public function __construct(string $contents) {
    $this->contents = $contents;
    $this->pointer = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $this->pointer = strlen($this->contents);
    return $this->contents;
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
  }

  /**
   * {@inheritdoc}
   */
  public function detach() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSize() {
    return strlen($this->contents);
  }

  /**
   * {@inheritdoc}
   */
  public function tell() {
    return $this->pointer;
  }

  /**
   * {@inheritdoc}
   */
  public function eof() {
    return strlen($this->contents) <= $this->pointer;
  }

  /**
   * {@inheritdoc}
   */
  public function isSeekable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function seek($offset, $whence = SEEK_SET): void {
    $length = strlen($this->contents);
    switch ($whence) {
      case SEEK_SET:
        break;

      case SEEK_END:
        $offset += $length;
        break;

      case SEEK_CUR:
        $offset += $this->pointer;
        break;

    }
    $this->pointer = min($length, max(0, $offset));
  }

  /**
   * {@inheritdoc}
   */
  public function rewind(): void {
    $this->pointer = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function isWritable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function write($string) {
    throw new \RuntimeException();
  }

  /**
   * {@inheritdoc}
   */
  public function isReadable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function read($length) {
    $max = strlen($this->contents);
    $pos = $this->pointer;
    $length = min(($max - $pos), $length);
    $contents = substr($this->contents, $this->pointer, $length);
    $this->pointer += $length;
    return $contents;
  }

  /**
   * {@inheritdoc}
   */
  public function getContents() {
    $pos = $this->pointer;
    $this->pointer = strlen($this->contents);
    return substr($this->contents, $pos);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata($key = NULL) {
    return NULL;
  }

}
