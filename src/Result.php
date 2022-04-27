<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * A generic result.
 */
class Result implements ResultInterface {

  /**
   * The result status.
   *
   * @var int
   */
  protected int $status;

  /**
   * The contents of the result.
   *
   * @var mixed
   */
  protected mixed $contents;

  /**
   * Result constructor.
   *
   * @param int $status
   *   The status.
   * @param mixed $contents
   *   The contents.
   */
  final public function __construct(int $status, mixed $contents) {
    $this->status = $status;
    $this->contents = $contents;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus(): int {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function getContents(): mixed {
    return $this->contents;
  }

  /**
   * Create a completed result.
   *
   * @param mixed $contents
   *   The contents of the completed result.
   *
   * @return static
   *   The result.
   */
  public static function complete(mixed $contents): static {
    return new static(ResultInterface::STATUS_COMPLETE, $contents);
  }

  /**
   * Create a delayed result.
   *
   * @param mixed $contents
   *   The contents of the delayed result.
   *
   * @return static
   *   The result.
   */
  public static function delayed(mixed $contents): static {
    return new static(ResultInterface::STATUS_DELAYED, $contents);
  }

  /**
   * Create an access result.
   *
   * @param string $message
   *   The message concerning the access control result.
   *
   * @return static
   *   The result.
   */
  public static function access(string $message): static {
    return new static(ResultInterface::STATUS_ACCESS, $message);
  }

  /**
   * Create an unavailable result.
   *
   * @param mixed $contents
   *   The contents of the unavailable result.
   *
   * @return static
   *   The result.
   */
  public static function unavailable(mixed $contents): static {
    return new static(ResultInterface::STATUS_UNAVAILABLE, $contents);
  }

  /**
   * Create an unavailable result.
   *
   * @param int $status
   *   The HTTP status code.
   * @param string $message
   *   The message.
   *
   * @return static
   *   The result.
   */
  public static function exception(int $status, string $message): static {
    return new static(
      $status ?: ResultInterface::STATUS_ERROR,
      $message
    );
  }

}
