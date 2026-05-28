<?php

namespace App\Tests\Traits;

trait AssertEmailTrait
{
    private function assertSingleEmailSentWithBodyContains(string $content): void
    {
        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $content);
        $this->assertEmailTextBodyContains($email, $content);
    }
}
