<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Action;

use App\Entity\Craftsman;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Response;

class SendCraftsmanEmail
{
    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * SendCraftsmanEmail constructor.
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function __invoke(Craftsman $craftsman): Response
    {
        return new Response('sent');
    }
}
