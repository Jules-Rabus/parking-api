<?php

namespace App\Command;

use App\Entity\Code;
use App\Entity\Date;
use App\Entity\Phone;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\ReservationStatusEnum;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-data',
    description: 'Import old data from JSON files into the database',
)]
class ImportCommand extends Command
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Kernel $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '1024M');

        $clientsJson = file_get_contents($this->kernel->getProjectDir().'/private/client.json');
        $reservationsJson = file_get_contents($this->kernel->getProjectDir().'/private/reservation.json');
        $codesJson = file_get_contents($this->kernel->getProjectDir().'/private/code.json');

        $clientsDecoded = json_decode($clientsJson, true);
        $reservationsDecoded = json_decode($reservationsJson, true);
        $codesDecoded = json_decode($codesJson, true);

        if (!is_array($clientsDecoded) || !is_array($reservationsDecoded) || !is_array($codesDecoded)) {
            $output->writeln('❌ Erreur : structure JSON invalide, non tableau.');

            return Command::FAILURE;
        }

        $userMap = [];

        foreach ($codesDecoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (empty($row['date_debut']) || empty($row['date_fin'])) {
                $output->writeln("⏭ code #" . (isset($row['id']) ? $row['id'] : '') ."skip: nom manquant");
                continue;
            }

            $deb = \DateTime::createFromFormat('Y-m-d', $row['date_debut']);
            $fin = \DateTime::createFromFormat('Y-m-d', $row['date_fin']);

            $code = new Code();
            $code->setAjout(((bool)$row['ajout']))
                ->setStartDate($deb)
                ->setEndDate($fin)
                ->setContent($row['code']);

            $this->entityManager->persist($code);
        }

        foreach ($clientsDecoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (empty($row['nom'])) {
                $output->writeln("⏭ client #" . (isset($row['id']) ? $row['id'] : '') ."skip: nom manquant");
                continue;
            }
            if (empty($row['telephone']) && empty($row['email'])) {
                $output->writeln("⏭ client #{$row['id']} skip: téléphone et email manquant");
                continue;
            }

            $user = new User();
            $user->setLastName((string) $row['nom']);
            if (!empty($row['email'])) {
                $user->setEmail($row['email']);
            }
            $this->entityManager->persist($user);

            if (!empty($row['telephone'])) {
                $doublon = $this->entityManager->getRepository(Phone::class)->findOneBy(['phoneNumber' => $row['telephone']]);
                if ($doublon) {
                    continue;
                }
                $phone = new Phone();
                try {
                    $phone->setPhoneNumber((string) $row['telephone']);
                } catch (\InvalidArgumentException $e) {
                    $output->writeln("⏭ client #{$row['id']} skip: téléphone invalide");
                    continue;
                }
                $phone->setOwner($user);
                $this->entityManager->persist($phone);
            }

            $userMap[$row['id']] = $user;
        }

        $this->entityManager->flush();
        $output->writeln('✅ Import des utilisateurs et téléphones terminé.');

        $phoneRepository = $this->entityManager->getRepository(Phone::class);
        $dateRepository = $this->entityManager->getRepository(Date::class);

        $i = 0;
        foreach ($reservationsDecoded as $row) {
            ++$i;

            if (
                empty($row['nombre_place'])
                || empty($row['date_arrivee'])
                || empty($row['date_depart'])
                || empty($row['date_reservation'])
            ) {
                $output->writeln("⏭ reservation #".  (isset($row['id']) ? $row['id'] : '') ."skip: données manquantes");
                continue;
            }

            $user = null;

            if (!empty($row['client_id']) && isset($userMap[$row['client_id']])) {
                $user = $userMap[$row['client_id']];
            }

            if (!$user && !empty($row['telephone'])) {
                try {
                    $normalized = $this->normalizePhone($row['telephone']);
                    $phone = $phoneRepository->findOneBy(['phoneNumber' => $normalized]);

                    if ($phone) {
                        $user = $phone->getOwner();

                        if (!empty($row['client_id'])) {
                            $userMap[$row['client_id']] = $user;
                        }
                    }
                } catch (\InvalidArgumentException) {
                }
            }

            if (!$user) {
                $output->writeln("⏭ reservation #{$row['id']} skip: client introuvable");
                continue;
            }

            try {
                $startDate = new \DateTimeImmutable($row['date_arrivee']);
                $endDate = new \DateTimeImmutable($row['date_depart']);
                $bookingDate = new \DateTimeImmutable($row['date_reservation']);
            } catch (\Exception) {
                $output->writeln("⏭ reservation #{$row['id']} skip: date invalide");
                continue;
            }

            $reservation = new Reservation();
            $reservation->setHolder($user);
            $reservation->setVehicleCount((int) $row['nombre_place']);
            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
            $reservation->setBookingDate($bookingDate);
            $reservation->setStatus(ReservationStatusEnum::CONFIRMED);

            foreach ($dateRepository->findDatesBetween($startDate, $endDate) as $date) {
                $reservation->addDate($date);
            }

            $codeAssign = $this->entityManager->getRepository(Code::class)->getCodeByDate($startDate, $endDate);

            $reservation->setCode($codeAssign);

            $this->entityManager->persist($reservation);

            if (0 === $i % self::BATCH_SIZE) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        $output->writeln('✅ Import des réservations terminé.');

        return Command::SUCCESS;
    }

    private function normalizePhone(string $raw): string
    {
        $raw = trim($raw);
        $raw = preg_replace('/[^\d\+]/', '', $raw);

        switch (true) {
            case preg_match('/^\+33[67]\d{8}$/', $raw):
                return $raw;
            case preg_match('/^0033([67]\d{8})$/', $raw, $m):
                return '+33'.$m[1];
            case preg_match('/^0?([67]\d{8})$/', $raw, $m):
                return '+33'.$m[1];
            default:
                throw new \InvalidArgumentException('Numéro mobile français invalide');
        }
    }
}
