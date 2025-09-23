<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Country\Entity\Country;
use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Domain\MacroRegion\Infrastructure\Doctrine\Repository\MacroRegionRepository;
use App\Domain\Shared\Notifier\NotifierInterface;
use App\Helper\FileHelper;
use App\Helper\UuidHelper;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:load-country-info', description: 'Load country info')]
final class LoadCountryInfoCommand extends Command
{
    private const string DEFAULT_SOURCE = 'https://download.geonames.org/export/dump/countryInfo.txt';
    public function __construct(
        protected LoggerInterface $logger,
        protected EntityManagerInterface $entityManager,
        protected CountryRepository $countryRepository,
        protected MacroRegionRepository $macroRegionRepository,
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
            $this->notifier->notify('Error downloading country info');
            return Command::FAILURE;
        }

        $this->processFile($filePath, $io);

        $io->success('Command Complete');
        return Command::SUCCESS;
    }

    private function processFile(string $filePath, SymfonyStyle $io): void
    {
        try {
            $this->countryRepository->truncateTable();

            if (false === ($handle = fopen($filePath, 'r'))) {
                $io->error("Cannot open file: $filePath");
                $this->logger->error("Cannot open file: $filePath");
                $this->notifier->notify("Cannot open file: $filePath");
                return;
            }

            $lineNumber = 0;
            $count = 0;
            while (($line = fgets($handle)) !== false) {
                $lineNumber++;

                //skip comments and empty lines
                if (str_starts_with($line, '#') || trim($line) === '') {
                    continue;
                }

                $columns = explode("\t", trim($line));
                if (count($columns) < 15) {
                    $this->logger->warning("Line $lineNumber skipped: not enough columns");
                    $this->notifier->notify("Line $lineNumber skipped: not enough columns");
                    continue;
                }

                [$iso, $iso3, $isoNumeric, $fips, $countryName, $capital, $area, $population,
                    $continent, $tld, $currencyCode, $currencyName, $phone, $postalFormat,
                    $postalRegex, $languages, $geonameId, $neighbours, $equivalentFips] =
                    array_pad($columns, 19, null);

                // find MacroRegion By code
                $macroRegion = $this->macroRegionRepository->findOneBy(['code' => $continent]);
                if (!$macroRegion) {
                    $io->error("Macro Region not found: $continent");
                    $this->logger->warning("MacroRegion not found for continent $continent");
                    $this->notifier->notify("MacroRegion not found for continent $continent");
                }

                $country = Country::create(
                    id: UuidHelper::create(),
                    iso: $iso,
                    name:  $countryName,
                    capital: $capital,
                    population: (int)$population,
                    phoneCode:  (int)$phone,
                    macroRegion:  $macroRegion,
                );

                $country->setGeonameId((int)$geonameId);

                $this->entityManager->persist($country);
                $count++;

            }

            fclose($handle);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->entityManager->flush();
            $io->info("Saved $count country infos");
            $this->notifier->notify("Result console command: load-country-info - Uploaded $count countries");

        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            $this->logger->error($exception->getMessage());
            $this->notifier->notify("Error console command: load-country-info - " . $exception->getMessage());
            throw new RuntimeException('Error processing country info' . $exception->getMessage());
        }
    }
}
