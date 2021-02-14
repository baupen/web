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

class ClearCacheCommand extends Command
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    public const RETURN_CODE_NO_ACTIONS = 1;
    public const RETURN_CODE_NO_FORCE = 2;

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
            ->addOption('construction-sites', 'cs', InputOption::VALUE_NONE, 'Remove the full construction sites cache (includes reports).')
            ->addOption('renders', 'r', InputOption::VALUE_NONE, 'Remove all renders.')
            ->addOption('authorization', 'a', InputOption::VALUE_NONE, 'Remove all authorization data.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'You must set this parameter to execute this action.');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $applyChanges = $input->getOption('force');

        $clearsExecuted = 0;
        if ($input->getOption('construction-sites')) {
            $directory = $this->pathService->getTransientFolderForConstructionSites();
            $this->clearFolder($io, $directory, $applyChanges);
            ++$clearsExecuted;
        }

        if ($input->getOption('renders')) {
            $directory = $this->pathService->getTransientFolderForRenders();
            $this->clearFolder($io, $directory, $applyChanges);
            ++$clearsExecuted;
        }

        if ($input->getOption('authorization')) {
            $directory = $this->pathService->getTransientFolderForAuthorization();
            $this->clearFolder($io, $directory, $applyChanges);
            ++$clearsExecuted;
        }

        if (!$clearsExecuted) {
            $io->warning('Nothing to clear specified. Run the operation with --construction-sites or --authorization to do so.');

            return self::RETURN_CODE_NO_ACTIONS;
        }

        if (!$applyChanges) {
            $io->warning('No changes have been applied to the file system. Run the operation with --force to do so.');

            return self::RETURN_CODE_NO_FORCE;
        }

        return 0;
    }

    private function clearFolder(SymfonyStyle $io, string $folder, bool $applyChanges)
    {
        $io->text('Removing the folder at '.$folder);
        if ($applyChanges) {
            exec('rm -rf '.$folder);
        }
    }
}
