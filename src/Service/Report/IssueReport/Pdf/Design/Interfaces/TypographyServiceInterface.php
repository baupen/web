<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Pdf\Design\Interfaces;

interface TypographyServiceInterface
{
    /**
     * for footers/headers.
     *
     * @return float
     */
    public function getFooterFontSize();

    /**
     * for footers/headers.
     *
     * @return float
     */
    public function getHeaderFontSize();

    /**
     * for text.
     *
     * @return float
     */
    public function getTextFontSize();

    /**
     * for titles.
     *
     * @return float
     */
    public function getTitleFontSize();
}
