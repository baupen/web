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
        $this->addFileUrlProperties($openApi);
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

    private function setFeedEntryResponses(OpenApi &$openApi)
    {
        $feedEntrySchemaName = 'FeedEntry';
        $feedEntrySchema = [
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
        $this->patchSchema($openApi, $feedEntrySchemaName, $feedEntrySchema);

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

    private function setSummaryResponse(OpenApi &$openApi)
    {
        $summarySchemaName = 'Summary';
        $summarySchema = [
            'type' => 'object',
            'description' => 'Quick count of relevant issue categories.',
            'required' => ['openCount', 'overdueCount', 'resolvedCount', 'closedCount'],
            'properties' => [
                'openCount' => ['type' => 'integer'],
                'resolvedCount' => ['type' => 'integer'],
                'closedCount' => ['type' => 'integer'],
            ],
        ];
        $this->patchSchema($openApi, $summarySchemaName, $summarySchema);

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

    private function addFilePaths(OpenApi &$openApi)
    {
        $imageSchema = [
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
        $this->patchSchema($openApi, 'Image', $imageSchema);

        $imageContent = [
            'multipart/form-data' => [
                'schema' => [
                    '$ref' => '#/components/schemas/Image',
                ],
                'encoding' => [
                    'image' => [
                        'contentType' => 'image/jpeg, image/gif, image/png',
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
            ->withSummary('Add / replace the image of the issue')
            ->withParameters([$this->createRequiredPathParameter('id')])
            ->withRequestBody($requestBody)
            ->addResponse($response, 201);

        $response = new Model\Response('Issue image deleted');
        $deleteOperation = (new Model\Operation('deleteIssueImage'))
            ->withTags(['Issue'])
            ->withSummary('Remove the image of the issue')
            ->withParameters([$this->createRequiredPathParameter('id')])
            ->addResponse($response, 204);

        $path = (new Model\PathItem())
            ->withPost($postOperation)
            ->withDelete($deleteOperation);

        $openApi->getPaths()->addPath('/api/issues/{id}/image', $path);
    }

    private function configureEmailEndpoint(OpenApi &$openApi)
    {
        $openApi = $this->removePath($openApi, '/api/emails/{noneIdentifier}');

        $postOperation = $openApi->getPaths()->getPath('/api/emails')->getPost();
        $postOperation->addResponse(new Model\Response('E-Mail sent'), 200);
        $postOperation->addResponse(new Model\Response('E-Mail server unreachable'), 503);
    }

    private function configureRegistrationEndpoint(OpenApi $openApi)
    {
        $pathName = '/api/construction_managers';

        $path = $openApi->getPaths()->getPath($pathName);

        if ($path && ($post = $path->getPost())) {
            $enrichedPost = $post
                ->withDescription('If called by construction manager allowed to associate, will never error & return a construction manager body. Else the body is an error identifier or empty. Allows unauthenticated calls.')
                ->addResponse(new Model\Response('Already registered'), 400)
                ->addResponse(new Model\Response('Account is deactivated'), 417)
                ->addResponse(new Model\Response('E-Mail server unreachable'), 503);

            $path = $path->withPost($enrichedPost);
            $openApi->getPaths()->addPath($pathName, $path);
        }
    }

    private function patchSchema(OpenApi &$openApi, string $schemaName, array $schemaPatch)
    {
        $schemas = $openApi->getComponents()->getSchemas()->getArrayCopy();

        $currentSchema = isset($schemas[$schemaName]) ? $schemas[$schemaName]->getArrayCopy() : [];
        $schemas[$schemaName] = new \ArrayObject(array_merge_recursive($currentSchema, $schemaPatch));

        $components = $openApi->getComponents()->withSchemas(new \ArrayObject($schemas));
        $openApi = $openApi->withComponents($components);
    }

    private function removePath(OpenApi $openApi, string $path): OpenApi
    {
        $paths = $openApi->getPaths()->getPaths();
        unset($paths[$path]);

        $newPaths = new Model\Paths();
        foreach ($paths as $url => $path) {
            $newPaths->addPath($url, $path);
        }

        return $openApi->withPaths($newPaths);
    }

    private function addFileUrlProperties(OpenApi &$openApi)
    {
        $schemaPatch = ['properties' => ['imageUrl' => ['type' => 'string', 'nullable' => true]]];
        $this->patchSchema($openApi, 'Issue.jsonld-issue-read', $schemaPatch);
        $this->patchSchema($openApi, 'Issue-issue-read', $schemaPatch);

        $schemaPatch = ['properties' => ['imageUrl' => ['type' => 'string', 'nullable' => true]]];
        $this->patchSchema($openApi, 'ConstructionSite.jsonld-construction-site-read', $schemaPatch);
        $this->patchSchema($openApi, 'ConstructionSite-construction-site-read', $schemaPatch);

        $schemaPatch = ['properties' => ['fileUrl' => ['type' => 'string', 'nullable' => true]]];
        $this->patchSchema($openApi, 'Map.jsonld-map-read', $schemaPatch);
        $this->patchSchema($openApi, 'Map-map-read', $schemaPatch);
    }
}
