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
        $this->loadSomeRandoms($manager);

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
