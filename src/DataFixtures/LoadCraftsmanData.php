<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\Craftsman;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCraftsmanData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $entries = [];

        $handle = fopen(__DIR__ . '/Resources/1230_Unternehmerliste.csv', 'r');
        while (($data = fgetcsv($handle, null, ",")) !== false) {
            if (count($data) >= 10 && $data[0] != "") {
                $entries[] = [$data[3], $data[2], $data[1], $data[10]];
            }
        }

        foreach ($entries as $data) {
            $craftsman = new Craftsman();
            $craftsman->setName($data[0] . " (" . $data[1] . ")");
            $craftsman->setDescription($data[2]);
            $craftsman->setEmail($data[3]. ".vu");
            $manager->persist($craftsman);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }

    /**
     * create an instance with all random values.
     *
     * @return Craftsman
     */
    protected function getAllRandomInstance()
    {
        $craftsmen = new Craftsman();
        $this->fillRandomCommunication($craftsmen);
        $this->fillRandomThing($craftsmen);

        return $craftsmen;
    }
}
