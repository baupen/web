<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Service\Interfaces\PathServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheClearCommand extends Command
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    public const RETURN_CODE_NO_FORCE = 1;

    /**
     * ClearCacheCommand constructor.
     */
    public function __construct(PathServiceInterface $pathService)
    {
        parent::__construct();

        $this->pathService = $pathService;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:cache:clear')
            ->setDescription('Clears the cache.')
            ->addOption('renders', null, InputOption::VALUE_NONE, 'Remove all renders.')
            ->addOption('reports', null, InputOption::VALUE_NONE, 'Remove all reports which have not been picked up.')
            ->addOption('construction-sites', null, InputOption::VALUE_NONE, 'Remove the full construction sites cache. Reverses the app:cache:warmup command. Will slow down the application noticeably, and might lead to timeouts when generating reports.')
            ->addOption('authorization', null, InputOption::VALUE_NONE, 'Remove all authorization data.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'If you add the --construction-sites or --authorization you must set the this parameter.');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $clearConstructionSites = $input->getOption('construction-sites');
        $clearAuthentication = $input->getOption('authorization');
        if (($clearConstructionSites || $clearAuthentication) &&
            !$input->getOption('force')) {
            $io->warning('To clear construction sites or authentication, you must run this command --force.');

            return self::RETURN_CODE_NO_FORCE;
        }

        /*
         * The following is cached:
         * - thumbnail, preview and full for each construction site image, map file render and issue image
         * - render of each issue position onto the map file render with a crosshair (to see its position)
         * - renders of map file with issue positions
         * - reports which are about to be downloaded (will be removed after download)
         * - authentication (like whitelists)
         *
         * To further save on storage space, consider the following improvement:
         * - remove rendered images & rendered position for closed issues
         *
         * To further save on computational power, consider the following:
         * - do not clear the render of issue position
         * - do not clear any renders
         */

        if ($clearConstructionSites) {
            $directory = $this->pathService->getTransientFolderForConstructionSites();
            $this->clearFolder($io, $directory);
        }

        if ($clearAuthentication) {
            $directory = $this->pathService->getTransientFolderForAuthorization();
            $this->clearFolder($io, $directory);
        }

        if ($input->getOption('renders')) {
            $directory = $this->pathService->getTransientFolderForRenders();
            $this->clearFolder($io, $directory);
        }

        if ($input->getOption('reports')) {
            $directory = $this->pathService->getTransientFolderForReports();
            $this->clearFolder($io, $directory);
        }

        return 0;
    }

    private function clearFolder(SymfonyStyle $io, string $folder)
    {
        $io->text('Removing the folder at '.$folder);
        exec('rm -rf '.$folder);
    }
}
