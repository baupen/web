<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Service\Interfaces\CronServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SyncServiceInterface;
use const DIRECTORY_SEPARATOR;
use Psr\Log\LoggerInterface;

class CronService implements CronServiceInterface
{
    /**
     * @var SyncServiceInterface
     */
    private $syncService;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SyncServiceInterface $syncService, PathServiceInterface $pathService, LoggerInterface $logger)
    {
        $this->syncService = $syncService;
        $this->pathService = $pathService;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function run()
    {
        $this->callScripts();
        $this->syncService->sync();

        return true;
    }

    /**
     * looks for all scripts at an agreed by location / name and calls them.
     */
    private function callScripts()
    {
        $scriptsRoot = $this->pathService->getScriptsRoot();
        $constructionSiteRoot = $this->pathService->getConstructionSiteFolderRoot();
        $dirs = glob($scriptsRoot . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $possibleScriptName = $dir . DIRECTORY_SEPARATOR . 'runner.py';
            if (file_exists($possibleScriptName)) {
                $command = $possibleScriptName . ' -d ' . $constructionSiteRoot;
                exec($possibleScriptName . ' -d ' . $constructionSiteRoot, $output, $exitCode);
                $this->logger->info('command ' . $command . ' terminated with exit code ' . $exitCode, ['full_output' => $output]);
            }
        }
    }
}
