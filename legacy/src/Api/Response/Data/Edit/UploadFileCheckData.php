<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\Edit;

use App\Model\UploadFileCheck;

class UploadFileCheckData
{
    /**
     * @var UploadFileCheck
     */
    private $uploadFileCheck;

    public function getUploadFileCheck(): UploadFileCheck
    {
        return $this->uploadFileCheck;
    }

    public function setUploadFileCheck(UploadFileCheck $uploadFileCheck): void
    {
        $this->uploadFileCheck = $uploadFileCheck;
    }
}
