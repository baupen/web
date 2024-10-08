<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\ConstructionManager;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\Email\ConstructionSiteReport;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WeeklySendCommand extends Command
{
    private ManagerRegistry $registry;

    private EmailServiceInterface $emailService;

    private ReportServiceInterface $reportService;

    /**
     * ImportLdapUsersCommand constructor.
     */
    public function __construct(ManagerRegistry $registry, EmailServiceInterface $emailService, ReportServiceInterface $reportService)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->emailService = $emailService;
        $this->reportService = $reportService;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:weekly:send')
            ->setDescription('Sends the weekly construction sites overview email.')
            ->addOption('only', 'o', InputOption::VALUE_OPTIONAL, 'Only send to specific E-Mail');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $constructionManagerRepository = $this->registry->getRepository(ConstructionManager::class);
        if ($input->getOption('only')) {
            $constructionManagers = $constructionManagerRepository->findBy(['email' => $input->getOption('only')]);
        } else {
            $constructionManagers = $constructionManagerRepository->findBy(['isEnabled' => true, 'receiveWeekly' => true]);
        }

        $relevantConstructionSites = [];
        foreach ($constructionManagers as $constructionManager) {
            foreach ($constructionManager->getConstructionSites() as $constructionSite) {
                $relevantConstructionSites[$constructionSite->getId()] = $constructionSite;
            }
        }
        $io->text('Found '.count($relevantConstructionSites).' relevant construction sites.');

        $nowWeekAgo = new \DateTime('now - 1 week');
        $constructionSiteReportLookup = [];
        foreach ($relevantConstructionSites as $constructionSite) {
            $report = $this->reportService->createConstructionSiteReport($constructionSite, $nowWeekAgo);
            $constructionSiteReportLookup[$constructionSite->getId()] = $report;
        }
        $io->text('Created '.count($constructionSiteReportLookup).' reports.');

        foreach ($constructionManagers as $constructionManager) {
            $relevantConstructionSiteReports = $this->getOrderedConstructionSiteReportsForManager($constructionManager, $constructionSiteReportLookup);

            $this->emailService->sendConstructionSitesReport($constructionManager, $relevantConstructionSiteReports);
        }
        $io->text('Sent '.count($constructionManagers).' emails.');

        return 0;
    }

    /**
     * @param ConstructionSiteReport[] $constructionSiteReportLookup
     *
     * @return ConstructionSiteReport[]
     */
    private function getOrderedConstructionSiteReportsForManager(ConstructionManager $constructionManager, array $constructionSiteReportLookup): array
    {
        $relevantConstructionSiteReports = [];

        foreach ($constructionManager->getConstructionSites() as $constructionSite) {
            $relevantConstructionSiteReports[] = $constructionSiteReportLookup[$constructionSite->getId()];
        }

        usort($relevantConstructionSiteReports, function (ConstructionSiteReport $a, ConstructionSiteReport $b): int {
            return strcmp($a->getConstructionSite()->getName(), $b->getConstructionSite()->getName());
        });

        return $relevantConstructionSiteReports;
    }
}
