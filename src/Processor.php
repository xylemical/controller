<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\Exception\UnavailableException;

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
  public function applies(RequestInterface $request, mixed $contents, ContextInterface $context): bool {
    foreach ($this->processors as $processor) {
      if ($processor->applies($request, $contents, $context)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getResult(RequestInterface $request, mixed $contents, ContextInterface $context): ResultInterface {
    foreach ($this->processors as $processor) {
      if ($processor->applies($request, $contents, $context)) {
        return $processor->getResult($request, $contents, $context);
      }
    }
    throw new UnavailableException('Unable to match a valid processor.');
  }

}
