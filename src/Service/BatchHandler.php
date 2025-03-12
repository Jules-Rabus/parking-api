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
class BatchHandler
{
    private $messageRepository;
    private $batchRepository;

    private const CHAT_BASE_URL = 'https://api.mistral.ai';
    private const CHAT_BATCH_URL = self::CHAT_BASE_URL . '/v1/batch/jobs';
    private const CHAT_COMPLETION_URL = '/v1/chat/completions';
    private const CHAT_FILE_URL = self::CHAT_BASE_URL . '/v1/files';

    private const CASES = [
        '1' => "l'utilisateur veut réserver",
        '2' => "l'utilisateur veut annuler",
        '3' => "l'utilisateur veut modifier",
        '4' => "l'utilisateur veut l'adresse",
        '5' => "l'utilisateur veut un code",
    ];

    private const SYSTEM_PROMPT = 'You are a parking customer service. Your task is to categorize the customer inquiry into the following predefined cases: ' .
    self::CASES['1'] . ', ' .
    self::CASES['2'] . ', ' .
    self::CASES['3'] . ', ' .
    self::CASES['4'] . ', ' .
    self::CASES['5'];

    public function __construct(
        private LoggerInterface                 $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface    $httpClient,
        #[Autowire(env: 'AI_API_KEY')] private readonly string $aiApiKey,
        #[Autowire(env: 'AI_MODEL')] private readonly string $aiModel,
    ) {
        $this->messageRepository = $this->entityManager->getRepository(Message::class);
        $this->batchRepository = $this->entityManager->getRepository(Batch::class);
    }

    public function process(): void
    {
        /* Vous pouvez décommenter cette partie si vous souhaitez générer des messages de test
        MessageFactory::createMany(5, [
            'status' => MessageStatusEnum::PENDING_BATCH->value
        ]);
        */
        $messages = $this->messageRepository->findBy(['status' => MessageStatusEnum::PENDING_BATCH], limit: 1);
        $batch = $this->createBatch($messages);
        $this->sendBatch($batch);

        //$this->handlePendingBatch();
    }

    /**
     * @param array<Message> $messages
     * @return Batch
     */
    private function createBatch(array $messages): Batch
    {
        $batch = new Batch();
        foreach ($messages as $message) {
            $batch->addMessage($message);
        }
        $batch->setStatus(BatchStatusEnum::CREATED);
        $this->entityManager->persist($batch);
        $this->entityManager->flush();
        return $batch;
    }

    /**
     * Génère le contenu JSONL à partir des messages du batch et lance l'upload.
     *
     * @param Batch $batch
     * @return string L'identifiant du fichier retourné par l'API.
     */
    private function generateBatchJsonl(Batch $batch): string
    {
        $jsonl = '';
        foreach ($batch->getMessages() as $message) {
            $data = json_encode([
                'custom_id' => (string) $message->getId(),
                'body' => [
                    'max_tokens' => 500,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => self::SYSTEM_PROMPT
                        ],
                        [
                            'role' => 'user',
                            'content' => $message->getContent()
                        ]
                    ],
                    'response_format' => $this->generateJsonSchema()
                ]
            ]);
            $jsonl .= $data . "\n";
        }
        // On passe le contenu directement à la méthode d'upload
        $file_id = $this->uploadFile($jsonl, $batch);
        $batch->setFileId($file_id);
        $this->entityManager->flush();
        return $file_id;
    }

    /**
     * Upload le contenu du fichier à l'API en utilisant multipart/form-data.
     *
     * @param string $fileContent Le contenu du fichier à uploader.
     * @return string L'identifiant du fichier retourné par l'API.
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function uploadFile(string $fileContent, Batch $batch): Uuid
    {
        $params = [
            'purpose'  => 'batch',
            'filename' => 'batch-' . $batch->getId() . '.jsonl',
            'file'     => $fileContent,
        ];

        $boundary = $this->generateMultipartBoundary();
        $multipartBody = $this->createMultipartStream($params, $boundary);

        dd($multipartBody);

        $response = $this->httpClient->request('POST', self::CHAT_FILE_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type'  => "multipart/form-data; boundary={$boundary}",
                'Accept'        => 'application/json'
            ],
            'body' => $multipartBody,
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('File upload failed', [
                'status'  => $response->getStatusCode(),
                'content' => $response->getContent(false),
            ]);
            throw new \RuntimeException('Error uploading file');
        }

        $responseData = $response->toArray();
        return new Uuid($responseData['id']);
    }

    private function downloadFile(Uuid $fileId): void
    {
        $response = $this->httpClient->request('GET', self::CHAT_FILE_URL . '/' . $fileId . '/content', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
            ]
        ]);

        dd($response->getContent());
    }

    /**
     * Envoie le fichier batch à l'API pour lancer le traitement.
     *
     * @param Batch $batch
     *
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function sendBatch(Batch $batch): void
    {
        $this->logger->info('Sending batch File' . $batch->getId() . ' with ' . $batch->getMessages()->count() . ' messages');

        $file_id = $this->generateBatchJsonl($batch);

        $body = json_encode([
            'endpoint'    => self::CHAT_COMPLETION_URL,
            'model'       => $this->aiModel,
            'metadata'    => [
                'batch_id' => (string) $batch->getId(),
                'job_type' => 'testing'
            ],
            'input_files' => [
                $file_id
            ],
        ]);

        $response = $this->httpClient->request('POST', self::CHAT_BATCH_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ],
            'body' => $body
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Batch send failed', [
                'status'  => $response->getStatusCode(),
                'content' => $response->getContent(false),
            ]);
            throw new \RuntimeException('Error sending batch');
        }

        $responseData = $response->toArray();
        $batch->setStatus(BatchStatusEnum::QUEUED);
        $batch->setMistralId(new Uuid($responseData['id']));
        $this->entityManager->flush();

        $this->logger->info('Batch sent', [
            'response_status'  => $response->getStatusCode(),
            'response_content' => $response->getContent(false),
        ]);
    }

    private function handlePendingBatch(): void
    {
        $batchs = $this->batchRepository->findBy(['status' => BatchStatusEnum::QUEUED]);
        if ($batchs === null) {
            return;
        }
        foreach ($batchs as $batch) {
            $this->getBatchJob($batch);
        }

    }

    private function getBatchJob(Batch $batch){

        $response = $this->httpClient->request('GET', self::CHAT_BATCH_URL . '/' . $batch->getMistralId(), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Batch get failed', [
                'status'  => $response->getStatusCode(),
                'content' => $response->getContent(false),
            ]);
            throw new \RuntimeException('Error getting batch');
        }

        $responseData = $response->toArray();
        $output_file = new Uuid($responseData['output_file']);
        $this->downloadFile($output_file);

        dd($response->getContent());
    }

    private function generateJsonSchema(): array
    {
        $jsonSchema = [
            'type' => 'json_schema',
            'json_schema' => [
                'schema' => [
                    'title' => 'Cases',
                    'type' => 'object',
                    'properties' => [
                        '1' => [
                            'title' => self::CASES['1'],
                            'type'  => 'boolean'
                        ],
                        '2' => [
                            'title' => self::CASES['2'],
                            'type'  => 'boolean'
                        ],
                        '3' => [
                            'title' => self::CASES['3'],
                            'type'  => 'boolean'
                        ],
                        '4' => [
                            'title' => self::CASES['4'],
                            'type'  => 'boolean'
                        ],
                        '5' => [
                            'title' => self::CASES['5'],
                            'type'  => 'boolean'
                        ]
                    ],
                    'required' => ['1', '2', '3', '4', '5'],
                    'additionalProperties' => false
                ],
                'name' => 'cases',
                'strict' => true
            ]
        ];
        return $jsonSchema;
    }

    /**
     * Génère une chaîne de séparation unique pour le multipart.
     *
     * @return string
     */
    private function generateMultipartBoundary(): string
    {
        return '----BatchHandler' . bin2hex(random_bytes(16));
    }

    /**
     * Crée le flux multipart à partir des paramètres.
     *
     * @param array $params   Les paramètres à inclure dans la requête.
     * @param string $boundary La chaîne de séparation multipart.
     *
     * @return string Le flux multipart sous forme de chaîne.
     */
    private function createMultipartStream(array $params, string $boundary): string
    {
        $multipartStream = '';

        foreach ($params as $key => $value) {
            $multipartStream .= "--{$boundary}\r\n";
            $multipartStream .= "Content-Disposition: form-data; name=\"{$key}\"";

            if ($key === 'file') {
                $filename = $params['filename'] ?? 'upload_file.bin';
                $multipartStream .= "; filename=\"{$filename}\"\r\n";
                $multipartStream .= "Content-Type: application/octet-stream\r\n\r\n";
                $multipartStream .= $value . "\r\n";
            } else {
                $multipartStream .= "\r\n\r\n{$value}\r\n";
            }
        }

        $multipartStream .= "--{$boundary}--\r\n";

        return $multipartStream;
    }
}
