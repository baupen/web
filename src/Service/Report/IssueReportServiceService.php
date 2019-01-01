<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report;

use App\Service\Report\Document\DocumentInterface;
use App\Service\Report\Interfaces\IssueReportServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class IssueReportServiceService implements IssueReportServiceInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param DocumentInterface $document
     * @param string $constructionSiteName
     * @param string|null $constructionSiteImage
     * @param string $constructionSiteAddressLines
     * @param string $reportElements
     * @param array $filterEntries
     */
    public function addIntroduction(DocumentInterface $document, string $constructionSiteName, ?string $constructionSiteImage, string $constructionSiteAddressLines, string $reportElements, array $filterEntries)
    {
        //three or two column layout
        $columnedRegion = $document->createColumnLayout(3);

        //image
        if ($constructionSiteImage !== null) {
            $columnedRegion->printImage($constructionSiteImage);
        }

        $columnedRegion->goToColumn(1);

        $columnedRegion->printTitle($constructionSiteName);
        $columnedRegion->printParagraph($constructionSiteAddressLines);

        $reportElementsTitle = $this->translator->trans('introduction.elements', [], 'report');
        $columnedRegion->printTitle($reportElementsTitle);
        $columnedRegion->printParagraph($reportElements);

        $columnedRegion->goToColumn(3);

        $filterTitle = $this->translator->trans('entity.name', [], 'entity_filter');
        $columnedRegion->printTitle($filterTitle);
        $columnedRegion->printKeyValueParagraph($filterEntries);
    }
}
