<?php

namespace App\State;

use App\Entity\Message;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\MessageGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MessageProvider implements ProviderInterface
{


    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface         $itemProvider,
        private readonly MessageGenerator $messageFormator
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $message = $this->itemProvider->provide($operation, $uriVariables, $context);

        /* @var $message Message */

        if (!$message) return null;

        if (!$message->getReservation()) return $message;

        $this->messageFormator->setReservation($message->getReservation());
        $this->messageFormator->setMessage($message);
        $this->messageFormator->setCases($message->getCases());

        $answerContent = $this->messageFormator->getContent();
        $message->setAnswerContent($answerContent);

        return $message;
    }
}
