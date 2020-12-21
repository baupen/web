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
        $this->addFilePaths($docs);

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

        $docs['paths']['/api/issues/report']['get']['responses'] = [
            '200' => [
                'description' => 'Issue pdf report',
                'content' => $pdfContent,
            ],
        ];
    }

    private function setFeedEntryResponses(array &$docs)
    {
        $feedEntrySchemaName = 'FeedEntry';
        $docs['components']['schemas'][$feedEntrySchemaName] = [
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

        $docs['paths']['/api/issues/feed_entries']['get']['responses'] = [
            '200' => [
                'description' => 'Issue feed',
                'content' => $feedEntryArrayContent,
            ],
        ];

        $docs['paths']['/api/craftsmen/feed_entries']['get']['responses'] = [
            '200' => [
                'description' => 'Craftsman feed',
                'content' => $feedEntryArrayContent,
            ],
        ];
    }

    private function setSummaryResponse(array &$docs)
    {
        $summarySchemaName = 'Summary';
        $docs['components']['schemas'][$summarySchemaName] = [
            'type' => 'object',
            'description' => 'Quick count of relevant issue categories.',
            'required' => ['openCount', 'overdueCount', 'resolvedCount', 'closedCount'],
            'properties' => [
                'openCount' => ['type' => 'integer'],
                'resolvedCount' => ['type' => 'integer'],
                'closedCount' => ['type' => 'integer'],
            ],
        ];

        $docs['paths']['/api/issues/summary']['get']['responses'] = [
            '200' => [
                'description' => 'Issue summary',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/'.$summarySchemaName,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function createRequiredPathParameter(string $name): array
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

    private function addFilePaths(array &$docs)
    {
        $path = '/issues/{issue}/image';
        $pathParameters = [
            $this->createRequiredPathParameter('issue'),
        ];

        $imageSchemaName = 'Image';
        $docs['components']['schemas'][$imageSchemaName] = [
            'type' => 'object',
            'description' => 'Image (.jpg, .gif, .png).',
            'properties' => [
                'image' => [
                    'type' => 'string',
                    'format' => 'binary',
                ],
            ],
            'required' => ['image'],
        ];

        $docs['paths'][$path] = [
            'post' => [
                'tags' => ['File'],
                'parameters' => $pathParameters,
                'requestBody' => [
                    'description' => 'The image to upload and assign to the issue',
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'description' => 'Image (.jpg, .gif, .png).',
                                'properties' => [
                                    'image' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                                'required' => ['image'],
                            ],
                            'encoding' => [
                                'image' => [
                                    'contentType' => ['image/jpeg', 'image/gif', 'image/png'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'File uploaded',
                        'content' => [
                            'text/html' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'uri',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $docs;
    }
}
