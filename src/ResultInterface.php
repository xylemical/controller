<?php

namespace Xylemical\Controller;

/**
 * Provides a result for processing.
 */
interface ResultInterface {

  /**
   * The request has some undetermined error.
   */
  public const STATUS_ERROR = 500;

  /**
   * The request has been completed in its entirety.
   */
  public const STATUS_COMPLETE = 200;

  /**
   * The request was placed but completion of the task is delayed.
   */
  public const STATUS_DELAYED = 204;

  /**
   * The result has been denied by access controls.
   */
  public const STATUS_ACCESS = 403;

  /**
   * The request has resulted in being unavailable.
   */
  public const STATUS_UNAVAILABLE = 400;

  /**
   * Get the status of the result.
   *
   * @return int
   *   The result.
   */
  public function getStatus(): int;

  /**
   * Get the contents of the result when not an error.
   *
   * @return mixed
   *   The contents.
   */
  public function getContents(): mixed;

}
