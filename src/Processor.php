<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides a generic processor.
 */
class Processor implements ProcessorInterface {

  /**
   * The processors.
   *
   * @var \Xylemical\Controller\ProcessorInterface[]
   */
  protected array $processors = [];

  /**
   * Processor constructor.
   *
   * @param \Xylemical\Controller\ProcessorInterface[] $processors
   *   The initial processors.
   */
  public function __construct(array $processors = []) {
    foreach ($processors as $processor) {
      $this->addProcessor($processor);
    }
  }

  /**
   * Add a processor to the processors.
   *
   * @param \Xylemical\Controller\ProcessorInterface $processor
   *   The processor.
   *
   * @return $this
   */
  public function addProcessor(ProcessorInterface $processor): static {
    $this->processors[] = $processor;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResult(RouteInterface $route, mixed $contents): ?ResultInterface {
    foreach ($this->processors as $processor) {
      if ($result = $processor->getResult($route, $contents)) {
        return $result;
      }
    }
    return NULL;
  }

}
