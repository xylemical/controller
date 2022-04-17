<?php

namespace Xylemical\Controller;

/**
 * Provide a generic ContextInterface.
 */
class Context implements ContextInterface {

  /**
   * The context values.
   *
   * @var array
   */
  protected array $values;

  /**
   * Context constructor.
   *
   * @param array $values
   *   The initial values.
   */
  public function __construct(array $values = []) {
    $this->values = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function all(): array {
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $index): mixed {
    return $this->values[$index] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function set(string $index, mixed $value): static {
    $this->values[$index] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function remove(string $index): static {
    unset($this->values[$index]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function has(string $index): bool {
    return isset($this->values[$index]);
  }

}
