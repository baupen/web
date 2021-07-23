<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Base;

use App\Entity\ConstructionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    /**
     * @return ConstructionManager|null
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayWarning($message, $link = null)
    {
        $this->displayFlash('warning', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayError($message, $link = null)
    {
        $this->displayFlash('danger', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displaySuccess($message, $link = null)
    {
        $this->displayFlash('success', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayDanger($message, $link = null)
    {
        $this->displayFlash('danger', $message, $link);
    }

    /**
     * @param string $message the translation message to display
     * @param string $link
     */
    protected function displayInfo($message, $link = null)
    {
        $this->displayFlash('info', $message, $link);
    }

    /**
     * @param $type
     * @param $message
     * @param string $link
     */
    private function displayFlash($type, $message, $link = null)
    {
        if (null !== $link) {
            $message = '<a href="'.$link.'">'.$message.'</a>';
        }
        $this->addFlash($type, $message);
    }
}
