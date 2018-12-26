<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseDoctrineController;
use App\Service\Interfaces\CronServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cron")
 */
class CronJobController extends BaseDoctrineController
{
    /**
     * @Route("/test/{secret}", name="cron_test")
     *
     * @param $secret
     *
     * @return Response
     */
    public function testAction($secret)
    {
        return new Response($secret === $this->getParameter('APP_SECRET') ? 'successful' : 'access denied');
    }

    /**
     * @Route("/half-hourly/{secret}", name="cron_daily")
     *
     * @param $secret
     * @param CronServiceInterface $cronService
     *
     * @return Response
     */
    public function halfHourlyAction($secret, CronServiceInterface $cronService)
    {
        if ($secret !== $this->getParameter('APP_SECRET')) {
            return new Response('access denied');
        }

        return new Response($cronService->run() ? 'successful' : 'unsuccessful');
    }
}
