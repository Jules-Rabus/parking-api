<?php

namespace App\Service;

use App\Entity\Batch;
use App\Entity\Message;
use App\Enum\BatchStatusEnum;
use App\Enum\MessageStatusEnum;
use App\Factory\MessageFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCronTask('%env(CRON_SCHEDULER_BATCH)%', method: 'process')]
class MessageCasesHandler
{
    private $messageRepository;
    private $batchRepository;

    private const CHAT_BASE_URL = 'http://host.docker.internal:1234';
    private const CHAT_COMPLETION_URL = self::CHAT_BASE_URL . '/v1/chat/completions';
    private const CASES = [
        'book' => ['title' => "l'utilisateur veut réserver et n'a pas de réservation", 'type' => 'boolean'],
        'cancelled' => ['title' => "l'utilisateur veut annuler", 'type' => 'boolean'],
        'modify' => ['title' => "l'utilisateur veut modifier", 'type' => 'boolean'],
        'address' => ['title' => "l'utilisateur veut l'adresse", 'type' => 'boolean'],
        'code' => ['title' => "l'utilisateur veut un code", 'type' => 'boolean'],
        'startDate' => ['title' => "donne la date de début de la réservation si elle existe", 'type' => 'string'],
        'endDate' => ['title' => "donne la date de fin de la réservation si elle existe", 'type' => 'string'],
        'vehicleCount' => ['title' => "donne le nombre de véhicules de la réservation par défaut 1", 'type' => 'integer'],
    ];

    private const SYSTEM_PROMPT = 'You are a parking customer service. Your task is to categorize the customer inquiry into the following predefined cases: ' .
    self::CASES['book']['title'] . ', ' .
    self::CASES['cancelled']['title'] . ', ' .
    self::CASES['modify']['title'] . ', ' .
    self::CASES['address']['title'] . ', ' .
    self::CASES['code']['title'] . ', ' .
    self::CASES['startDate']['title'] . ', ' .
    self::CASES['endDate']['title'] . ', ' .
    self::CASES['vehicleCount']['title'];


    public function __construct(
        private LoggerInterface                                $logger,
        private readonly EntityManagerInterface                $entityManager,
        private readonly HttpClientInterface                   $httpClient,
        #[Autowire(env: 'AI_API_KEY')] private readonly string $aiApiKey,
        #[Autowire(env: 'AI_MODEL')] private readonly string   $aiModel,
    )
    {
        $this->messageRepository = $this->entityManager->getRepository(Message::class);
        $this->batchRepository = $this->entityManager->getRepository(Batch::class);
    }

    public function process(): void
    {
        // Vous pouvez décommenter cette partie si vous souhaitez générer des messages de test
        /*MessageFactory::createMany(5, [
            'status' => MessageStatusEnum::PENDING_BATCH
        ]);
        return;
        */

        $messages = $this->messageRepository->findBy(['status' => MessageStatusEnum::PENDING_BATCH], limit: 1);
        foreach ($messages as $message) {
            $this->sendMessage($message);
        }
    }

    private function generateJsonl(Message $message): string
    {
        $data = [
            'model' => $this->aiModel,
            //'max_tokens' => 250,
            'temperature' => 0.15,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => self::SYSTEM_PROMPT . '. Today is ' . date('Y-m-d')
                ],
                [
                    'role' => 'user',
                    'content' => $message->getContent()
                ]
            ],
            'response_format' => $this->generateJsonSchema()
        ];
        return json_encode($data);
    }

    private function sendMessage(Message $message): void
    {
        $this->logger->info('Sending message ' . $message->getId());

        $body = $this->generateJsonl($message);

        $response = $this->httpClient->request('POST', self::CHAT_COMPLETION_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => $body
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Message send failed', [
                'status' => $response->getStatusCode(),
                'content' => $response->getContent(false),
            ]);
            throw new \RuntimeException('Error sending message');
        }

        dd($response->toArray());

        $cases = json_decode($response->toArray()['choices'][0]['message']['content'], true);
        $message->setCases($cases);
        $message->setStatus(MessageStatusEnum::PENDING_SEND);
        $this->entityManager->flush();

        dd($cases);

        $this->logger->info('Message sent', [
            'response_status' => $response->getStatusCode(),
            'response_content' => $response->getContent(false),
        ]);
    }

    private function generateJsonSchema(): array
    {

        $properties = [];
        $required = [];
        foreach (self::CASES as $key => $value) {
            $properties[$key] = [
                'title' => $value['title'],
                'type' => $value['type']
            ];
            $required[] = $key;
        }

        return [
            'type' => 'json_schema',
            'json_schema' => [
                'schema' => [
                    'title' => 'Cases',
                    'type' => 'object',
                    'properties' => $properties,
                    'required' => $required,
                    'additionalProperties' => false
                ],
                'name' => 'cases',
                'strict' => true
            ]
        ];
    }
}
