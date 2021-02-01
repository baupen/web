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
        /** @var array $docs */
        $docs = $this->decorated->normalize($object, $format, $context);

        $this->setReportResponse($docs);
        $this->setFeedEntryResponses($docs);
        $this->setSummaryResponse($docs);
        $this->addFilePaths($docs);
        $this->configureEmailEndpoint($docs);
        $this->configureRegistrationEndpoint($docs);

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
        $path = '/api/issues/{id}/image';
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
                'tags' => ['Issue'],
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

    private function configureEmailEndpoint(array &$docs)
    {
        // remove GET method
        unset($docs['paths']['/api/emails/{id}']['get']);

        $docs['paths']['/api/emails']['post']['responses'] = [
            '200' => ['description' => 'E-Mail sent'],
            '503' => ['description' => 'E-Mail server unreachable'],
        ];
    }

    private function configureRegistrationEndpoint(array &$docs)
    {
        $postNode = $docs['paths']['/api/construction_managers']['post'];

        $postNode['description'] = 'If called by construction manager allowed to associate, will never error & return a construction manager body. Else the body is an error identifier or empty. Allows unauthenticated calls.';
        $postNode['responses']['400'] = ['description' => 'Already registered'];
        $postNode['responses']['417'] = ['description' => 'Account is deactivated'];
        $postNode['responses']['503'] = ['description' => 'E-Mail server unreachable'];
    }
}
