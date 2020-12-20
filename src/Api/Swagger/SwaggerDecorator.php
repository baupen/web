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

        $this->setReportResponse($docs);
        $this->setFeedEntryResponses($docs);
        $this->setSummaryResponse($docs);
        // $docs = $this->addFilePaths($docs);

        return $docs;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    private function setReportResponse(array &$docs)
    {
        $pdfContent = [
            'application/pdf' => [
                'schema' => [
                    'type' => 'string',
                    'format' => 'binary',
                ],
            ],
        ];
        $docs['paths']['/api/issues/report']['get']['responses']['200']['content'] = $pdfContent;
        $docs['paths']['/api/issues/report']['get']['responses']['200']['description'] = 'Issue pdf report';
    }

    private function setFeedEntryResponses(array &$docs)
    {
        $feedEntry = [
            'type' => 'object',
            'description' => 'Some action which happened on the construction site.',
            'required' => ['date', 'subject', 'type', 'count'],
            'properties' => [
                'date' => ['type' => 'string', 'format' => 'date'],
                'subject' => ['type' => 'string', 'format' => 'iri-reference'],
                'type' => ['type' => 'integer'],
                'count' => ['type' => 'integer'],
            ],
        ];
        $feedEntrySchemaName = 'FeedEntry';
        $docs['components']['schemas'][$feedEntrySchemaName] = $feedEntry;

        $feedEntryArrayContent = [
            'application/json' => [
                'schema' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/'.$feedEntrySchemaName,
                    ],
                ],
            ],
        ];
        $docs['paths']['/api/issues/feed_entries']['get']['responses']['200']['content'] = $feedEntryArrayContent;
        $docs['paths']['/api/issues/feed_entries']['get']['responses']['200']['description'] = 'Issue feed';
        $docs['paths']['/api/craftsmen/feed_entries']['get']['responses']['200']['content'] = $feedEntryArrayContent;
        $docs['paths']['/api/craftsmen/feed_entries']['get']['responses']['200']['description'] = 'Craftsman feed';
    }

    private function setSummaryResponse(array &$docs)
    {
        $summary = [
            'type' => 'object',
            'description' => 'Quick count of relevant issue categories.',
            'required' => ['date', 'subject', 'type', 'count'],
            'properties' => [
                'date' => ['type' => 'string', 'format' => 'date'],
                'subject' => ['type' => 'string', 'format' => 'iri-reference'],
                'type' => ['type' => 'integer'],
                'count' => ['type' => 'integer'],
            ],
        ];
        $feedEntrySchemaName = 'FeedEntry';
        $docs['components']['schemas'][$feedEntrySchemaName] = $feedEntry;

        $feedEntryArrayContent = [
            'application/json' => [
                'schema' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/'.$feedEntrySchemaName,
                    ],
                ],
            ],
        ];
        $docs['paths']['/api/issues/feed_entries']['get']['responses']['200']['content'] = $feedEntryArrayContent;
        $docs['paths']['/api/issues/feed_entries']['get']['responses']['200']['description'] = 'Issue feed';
        $docs['paths']['/api/craftsmen/feed_entries']['get']['responses']['200']['content'] = $feedEntryArrayContent;
        $docs['paths']['/api/craftsmen/feed_entries']['get']['responses']['200']['description'] = 'Craftsman feed';
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

    /**
     * @return \array[][]
     */
    private function getImageContent(): array
    {
        $supportedImageTypes = ['image/jpeg', 'image/gif', 'image/png'];
        $imageContents = [];
        foreach ($supportedImageTypes as $supportedImageType) {
            $imageContents[$supportedImageType] = [
                'schema' => [
                    'type' => 'string',
                    'format' => 'binary',
                ],
            ];
        }

        return $imageContents;
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
