<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Sync\Interfaces;

interface DisplayNameServiceInterface
{
    /**
     * @return string
     */
    public function forConstructionSiteImage(string $filename);

    /**
     * @return string
     */
    public function forMapFile(string $mapName);

    /**
     * @param string[] $mapNames
     *
     * @return string[]
     */
    public function normalizeMapNames(array $mapNames);

    /**
     * @return string
     */
    public function forConstructionSite(string $folderName);

    /**
     * @param string[] $elementNames        as an (int id => string name) structure
     * @param callable $createNewElement    called as $addElement(string $name); should return int id of the new element
     * @param callable $assignChildToParent called with $assignParent(string $childId, string $parentId)
     * @param callable $clearParent         called with $clearParent(string $childId)
     */
    public function putIntoTreeStructure(array $elementNames, callable $createNewElement, callable $assignChildToParent, callable $clearParent);
}
