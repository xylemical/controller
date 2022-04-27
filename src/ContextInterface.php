<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides a context for the controller operation.
 */
interface ContextInterface {

  /**
   * Get all the context values.
   *
   * @return array
   *   The context values.
   */
  public function all(): array;

  /**
   * Get a context value.
   *
   * @param string $index
   *   The index.
   *
   * @return mixed
   *   The context value, or NULL.
   */
  public function get(string $index): mixed;

  /**
   * Set the context value.
   *
   * @param string $index
   *   The index.
   * @param mixed $value
   *   The value.
   *
   * @return $this
   */
  public function set(string $index, mixed $value): static;

  /**
   * Remove a context value.
   *
   * @param string $index
   *   The index.
   *
   * @return $this
   */
  public function remove(string $index): static;

  /**
   * Check the context has a value.
   *
   * @param string $index
   *   The index.
   *
   * @return bool
   *   The result.
   */
  public function has(string $index): bool;

}
