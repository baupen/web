<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

interface DisplayNameServiceInterface
{
    /**
     * @param string $filename
     *
     * @return string
     */
    public function forConstructionSiteImage(string $filename);

    /**
     * @param string $mapName
     *
     * @return string
     */
    public function forMapFile(string $mapName);

    /**
     * @param string[] $mapNames
     *
     * @return string
     */
    public function normalizeMapNames(array $mapNames);

    /**
     * @param string $folderName
     *
     * @return string
     */
    public function forConstructionSite(string $folderName);
}
