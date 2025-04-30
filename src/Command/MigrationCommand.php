<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\Date;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:migrate')]
class MigrationCommand extends Command
{
    private $entityManager;
    private $kernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Effectue la migration des données depuis un fichier JSON')
            ->setHelp('Cette commande permet d\'importer des utilisateurs à partir d\'un fichier JSON dans la base de données')
            ->addOption(
                'table',        // nom long: --file
                't',           // nom court: -f
                InputOption::VALUE_REQUIRED,
                '--table *nom_de_la_table*'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = $input->getOption('table');

        $jsonFile = $this->kernel->getProjectDir() . '/private/raju8736_prod_table_'.$table.'.json';

        if (!file_exists($jsonFile)) {
            $output->writeln('<error>Le fichier JSON n\'existe pas.</error>');
            return Command::FAILURE;
        }

        $jsonData = file_get_contents($jsonFile);
        $datas = json_decode($jsonData, true);


        if (!$datas) {
            $output->writeln('<error>Erreur lors du décodage du fichier JSON.</error>');
            return Command::FAILURE;
        }

        $this->jsonTOsql($datas);

        $clients = $this->entityManager->getRepository(Client::class);
        $dates = $this->entityManager->getRepository(Date::class);

        foreach ($datas as $type) {
            if (!is_array($type)) {
                continue;
            }
            foreach ($type as $data) {
                switch ($table) {
                    case "client":
                        $client = $clients->findOneByEmail([$data['email']]);
                        if ($client) {
                            break;
                        }
                        $roles = !empty($data['roles']) ? json_decode($data['roles'], true) : [];
                        $user = new Client();
                        $user->setEmail($data['email']);
                        $user->setRoles($roles);
                        $user->setPassword($data['password']);
                        $user->setNom($data['nom']);
                        $user->setIsVerified($data['is_verified']);
                        $user->setTelephone($data['telephone']);

                        $this->entityManager->persist($user);
                        break;
                    case "date":
                        $dat = \DateTimeImmutable::createFromFormat('Y-m-d', $data['date']);
                        $date = $dates->findOneByDate([$dat]);
                        if ($date) {
                            break;
                        }
                        $date = new Date();
                        $date->setDate($dat);
                        $this->entityManager->persist($date);
                        break;
                    case "reservation":
                        break;
                    case "reservation_date":
                        break;
                    case "code":
                        break;
                }
            }
        }

        $this->entityManager->flush();

        $output->writeln('<info>Tous les données ont été importés avec succès !</info>');

        return Command::SUCCESS;
    }

    public function jsonTOsql($datas)
    {
        if (!isset($datas['type']) || $datas['type'] !== 'table' || !isset($datas['data'])) {
            die("Format inattendu\n");
        }

        $table = $datas['name'];
        $data = $datas['data'];
        $database = $datas['database'];

        $output = 'exported_' . $table . '.sql';
        $fp = fopen($output, 'w');

        fwrite($fp, "-- Export from database: $database\n");
        fwrite($fp, "-- Table: $table\n\n");

        $columns = array_keys($data[0]);
        $colList = implode(', ', $columns);

        foreach ($data as $row) {
            $values = array_map(function ($val) {
                if (is_null($val)) return 'NULL';
                return "'" . addslashes($val) . "'";
            }, array_values($row));

            $valList = implode(', ', $values);
            $insert = "INSERT INTO $table ($colList) VALUES ($valList);\n";
            fwrite($fp, $insert);
        }

        fclose($fp);

        echo "✅ Fichier SQL généré : $output\n";
    }
}
