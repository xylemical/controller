<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Generic response.
 */
class Response implements ResponseInterface {

  /**
   * The protocol string.
   *
   * @var string
   */
  protected string $version = '1.0';

  /**
   * The status code.
   *
   * @var int
   */
  protected int $status = 200;

  /**
   * The reason for the status.
   *
   * @var string
   */
  protected string $reason = '';

  /**
   * The headers.
   *
   * @var string[][]
   */
  protected array $headers = [];

  /**
   * The body.
   *
   * @var \Psr\Http\Message\StreamInterface
   */
  protected StreamInterface $body;

  /**
   * Response constructor.
   *
   * @param int $status
   *   The status.
   * @param string $contents
   *   The contents.
   */
  public function __construct(int $status = 200, string $contents = '') {
    $this->status = $status;
    $this->body = new Stream($contents);
  }

  /**
   * {@inheritdoc}
   */
  public function getProtocolVersion() {
    return $this->version;
  }

  /**
   * {@inheritdoc}
   */
  public function withProtocolVersion($version) {
    $clone = clone $this;
    $clone->version = $version;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaders() {
    return $this->headers;
  }

  /**
   * {@inheritdoc}
   */
  public function hasHeader($name) {
    return isset($this->headers[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader($name) {
    return $this->headers[$name] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaderLine($name) {
    return implode(', ', $this->headers[$name] ?? []);
  }

  /**
   * {@inheritdoc}
   */
  public function withHeader($name, $value) {
    $clone = clone $this;
    $clone->headers[$name] = [$value];
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withAddedHeader($name, $value) {
    $clone = clone $this;
    $clone->headers[$name][] = $value;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withoutHeader($name) {
    $clone = clone $this;
    unset($clone->headers[$name]);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function withBody(StreamInterface $body) {
    $clone = clone $this;
    $clone->body = $body;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusCode() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function withStatus($code, $reasonPhrase = '') {
    $clone = clone $this;
    $clone->status = $code;
    $clone->reason = $reasonPhrase;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getReasonPhrase() {
    return $this->reason;
  }

}
