<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model;
use ApiPlatform\Core\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    private $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $this->setReportResponse($openApi);
        $this->setFeedEntryResponses($openApi);
        $this->setSummaryResponse($openApi);
        $this->addFilePaths($openApi);
        $this->configureEmailEndpoint($openApi);
        $this->configureRegistrationEndpoint($openApi);

        return $openApi;
    }

    private function setReportResponse(OpenApi $openApi)
    {
        $pdfContent = [
            'application/pdf' => [
                'schema' => [
                    'type' => 'string',
                    'format' => 'binary',
                ],
            ],
        ];

        $response = new Model\Response('Issue pdf report', new \ArrayObject($pdfContent));
        $openApi->getPaths()->getPath('/api/issues/report')->getGet()->addResponse($response, 200);
    }

    private function setFeedEntryResponses(OpenApi $openApi)
    {
        $feedEntrySchemaName = 'FeedEntry';
        $openApi->getComponents()->getSchemas()[$feedEntrySchemaName] = [
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

        $response = new Model\Response('Issue feed', new \ArrayObject($feedEntryArrayContent));
        $openApi->getPaths()->getPath('/api/issues/feed_entries')->getGet()->addResponse($response, 200);

        $response = new Model\Response('Craftsman feed', new \ArrayObject($feedEntryArrayContent));
        $openApi->getPaths()->getPath('/api/craftsmen/feed_entries')->getGet()->addResponse($response, 200);
    }

    private function setSummaryResponse(OpenApi $openApi)
    {
        $summarySchemaName = 'Summary';
        $openApi->getComponents()->getSchemas()[$summarySchemaName] = [
            'type' => 'object',
            'description' => 'Quick count of relevant issue categories.',
            'required' => ['openCount', 'overdueCount', 'resolvedCount', 'closedCount'],
            'properties' => [
                'openCount' => ['type' => 'integer'],
                'resolvedCount' => ['type' => 'integer'],
                'closedCount' => ['type' => 'integer'],
            ],
        ];

        $summarySchemeContent = [
            'application/json' => [
                'schema' => [
                    '$ref' => '#/components/schemas/'.$summarySchemaName,
                ],
            ],
        ];

        $response = new Model\Response('Issue summary', new \ArrayObject($summarySchemeContent));
        $openApi->getPaths()->getPath('/api/issues/summary')->getGet()->addResponse($response, 200);
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

    private function addFilePaths(OpenApi $openApi)
    {
        $imageSchemaName = 'Image';
        $openApi->getComponents()->getSchemas()[$imageSchemaName] = [
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

        $imageContent = [
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
        ];

        $fileUriContent = [
            'text/html' => [
                'schema' => [
                    'type' => 'string',
                    'format' => 'uri',
                ],
            ],
        ];

        $response = new Model\Response('Issue uploaded', new \ArrayObject($fileUriContent));
        $requestBody = new Model\RequestBody('The image to upload and assign to the issue', new \ArrayObject($imageContent));
        $postOperation = (new Model\Operation('postIssueImage'))
            ->withTags(['Issue'])
            ->withParameters([$this->createRequiredPathParameter('issue')])
            ->withRequestBody($requestBody)
            ->addResponse($response, 201);

        $path = (new Model\PathItem())
            ->withPost($postOperation);

        $openApi->getPaths()->addPath('/api/issues/{id}/image', $path);
    }

    private function configureEmailEndpoint(OpenApi $openApi)
    {
        // remove GET method
        $openApi->getPaths()->addPath('/api/emails/{noneIdentifier}', new Model\PathItem());

        $postOperation = $openApi->getPaths()->getPath('/api/emails')->getPost();
        $postOperation->addResponse(new Model\Response('E-Mail sent'), 200);
        $postOperation->addResponse(new Model\Response('E-Mail server unreachable'), 503);
    }

    private function configureRegistrationEndpoint(OpenApi $openApi)
    {
        $pathName = '/api/construction_managers';

        $path = $openApi->getPaths()->getPath($pathName);

        $postOperation = $path->getPost()
            ->withDescription('If called by construction manager allowed to associate, will never error & return a construction manager body. Else the body is an error identifier or empty. Allows unauthenticated calls.')
            ->addResponse(new Model\Response('Already registered'), 400)
            ->addResponse(new Model\Response('Account is deactivated'), 417)
            ->addResponse(new Model\Response('E-Mail server unreachable'), 503);

        $path = $path->withPost($postOperation);
        $openApi->getPaths()->addPath($pathName, $path);
    }
}
