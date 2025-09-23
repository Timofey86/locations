<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Domain\Region\Infrastructure\Doctrine\Repository\RegionRepository;
use App\Domain\Shared\Notifier\NotifierInterface;
use App\Helper\FileHelper;
use App\Helper\UuidHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use http\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:load-region-info', description: 'Load regions info')]
final class LoadRegionInfoCommand extends Command
{
    private const string DEFAULT_SOURCE = 'https://download.geonames.org/export/dump/admin1CodesASCII.txt';
    public function __construct(
        protected LoggerInterface $logger,
        protected EntityManagerInterface $entityManager,
        protected CountryRepository $countryRepository,
        protected RegionRepository $regionRepository,
        protected NotifierInterface $notifier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = FileHelper::downloadFile(self::DEFAULT_SOURCE);

        if (!$filePath) {
            $this->logger->error('Error downloading country info');
            $io->error('Error downloading country info');
            return Command::FAILURE;
        }

        $this->parseFile($filePath, $io);
        @unlink($filePath);

        $io->success('Command Complete');
        return Command::SUCCESS;
    }

    private function parseFile(string $filePath, SymfonyStyle $io): void
    {
        try {

            $this->regionRepository->truncateTable();

            if (false === ($handle = fopen($filePath, 'r'))) {
                $io->error("Cannot open file: $filePath");
                $this->logger->error("Cannot open file: $filePath");
                $this->notifier->notify("Cannot open file: $filePath");
                return;
            }


            $count = 0;
            $countryIdsByIso = $this->countryRepository->getIsoToIdMap();

            $conn = $this->entityManager->getConnection();
            $sql = "INSERT INTO region (id, country_id, code, name, geoname_id, created_at, updated_at)
                    VALUES (:id, :country_id, :code, :name, :geoname_id, now(), now())";
            $stmt = $conn->prepare($sql);

            while (($line = fgets($handle)) !== false) {
                $columns = explode("\t", trim($line));
                if (count($columns) < 4) {
                    continue;
                }

                [$code, $localName, $name, $geonameId] =
                    array_pad($columns, 4, null);

                $countryIso = substr($code, 0, 2);
                $countryId = $countryIdsByIso[$countryIso] ?? null;

                if ($countryId === null) {
                    $io->error("Country not found: $code");
                    $this->logger->warning("Country not found: $code");
                    $this->notifier->notify("Country not found: $code");
                    continue;
                }

                $stmt->executeStatement([
                    'id' => UuidHelper::create(),
                    'country_id' => $countryId,
                    'code' => $code,
                    'name' => $name,
                    'geoname_id' => $geonameId,
                ]);
                $count++;

            }

            fclose($handle);

            $io->info("Uploaded $count regions");
            $this->notifier->notify("Result console command: load-region-info - Uploaded $count regions");

        } catch (Exception $exception) {
            $io->error($exception->getMessage());
            $this->logger->error($exception->getMessage());
            $this->notifier->notify("Error console command: load-region-info - " . $exception->getMessage());
            throw new RuntimeException('Error processing region info' . $exception->getMessage());
        }
    }
}
