<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        $pdfContent = $this->getPdfContent();
        $docs['paths']['/api/issues/report']['get']['responses']['200']['content'] = $pdfContent;
        $docs['paths']['/api/issues/report']['get']['responses']['200']['description'] = 'Issue pdf report';

        // $docs = $this->addFilePaths($docs);

        return $docs;
    }

    private function createRequiredPathParameter(string $name)
    {
        return [
            'name' => $name,
            'in' => 'path',
            'required' => true,
            'schema' => [
                'type' => 'string',
            ],
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @return \array[][]
     */
    private function getImageContent(): array
    {
        $binarySchema = $this->getBinarySchema();

        $imageJpegContent = [
            'image/jpeg' => [
                $binarySchema,
            ],
        ];
        $imageGifContent = [
            'image/gif' => [
                $binarySchema,
            ],
        ];
        $imagePngContent = [
            'image/png' => [
                $binarySchema,
            ],
        ];

        return $imageJpegContent + $imageGifContent + $imagePngContent;
    }

    /**
     * @return \array[][]
     */
    private function getPdfContent(): array
    {
        $binarySchema = $this->getBinarySchema();

        return [
            'application/pdf' => [
                $binarySchema,
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    private function getBinarySchema(): array
    {
        $binarySchema = [
            'schema' => [
                'type' => 'string',
                'format' => 'binary',
            ],
        ];

        return $binarySchema;
    }

    private function addFilePaths(?float $docs): ?float
    {
        $path = '/maps/{map}/file/{mapFile}/{filename}';
        $imageContent = $this->getImageContent();
        $pathParameters = [
            $this->createRequiredPathParameter('map'),
            $this->createRequiredPathParameter('filename'),
        ];

        $docs['paths'][$path]['post']['parameters'] = $pathParameters;
        $docs['paths'][$path]['post']['requestBody']['content'] = $imageContent;
        $docs['paths'][$path]['post']['requestBody']['description'] = 'The file to upload and assign to the map';
        $docs['paths'][$path]['post']['responses']['201']['description'] = 'File uploaded';
        $docs['paths'][$path]['post']['responses']['201']['content']['text/html']['schema'] = [
            'type' => 'string',
            'format' => 'binary',
        ];

        return $docs;
    }
}
