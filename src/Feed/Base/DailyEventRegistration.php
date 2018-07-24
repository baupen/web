<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Feed\Base;

class DailyEventRegistration
{
    /**
     * @var int[][]
     */
    private $counts = [];

    /**
     * @var \DateTime[]
     */
    private $timeNormalization = [];

    /**
     * @param \DateTime $time
     *
     * @return string
     */
    protected function normalizeTime(\DateTime $time)
    {
        return $time->format('Y.m.d');
    }

    /**
     * @var mixed[]
     */
    private $receivers = [];

    /**
     * @param $receiver
     *
     * @return string
     */
    protected function normalizeReceiver($receiver)
    {
        if (in_array($receiver, $this->receivers, true)) {
            return array_search($receiver, $this->receivers, true);
        }
        $this->receivers[] = $receiver;

        return count($this->receivers) - 1;
    }

    /**
     * @param \DateTime $time
     * @param string $receiver
     */
    protected function register(\DateTime $time, $receiver)
    {
        $timeKey = $this->normalizeTime($time);
        if (!isset($this->counts[$timeKey])) {
            $this->counts[$timeKey] = [];
            $this->timeNormalization[$timeKey] = $time;
        }

        $receiverKey = $this->normalizeReceiver($receiver);
        if (!isset($this->counts[$timeKey][$receiverKey])) {
            $this->counts[$timeKey][$receiverKey] = 0;
        }

        ++$this->counts[$timeKey][$receiverKey];
    }

    /**
     * @return \Generator
     */
    protected function getRegistrations()
    {
        foreach ($this->counts as $timeKey => $entries) {
            foreach ($entries as $receiverKey => $count) {
                yield [$this->timeNormalization[$timeKey], $this->receivers[$receiverKey], $count];
            }
        }
    }
}
