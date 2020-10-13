<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

trait AssertEmailTrait
{
    private function assertSingleEmailSentWithBodyContains(string $content)
    {
        $this->assertEmailCount(1);

        $email = $this->getMailerMessage(0);
        $this->assertEmailHtmlBodyContains($email, $content);
        $this->assertEmailTextBodyContains($email, $content);
    }
}
