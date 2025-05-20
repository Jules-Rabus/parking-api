<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Code;
use App\Repository\DateRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Code, Code>
 */
class CodePersistProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Code, Code|void> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly DateRepository $dateRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Code
    {
        $data->removeDates();
        $dates = $this->dateRepository->findDatesBetween($data->getStartDate(), $data->getEndDate());
        foreach ($dates as $date) {
            $data->addDate($date);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
