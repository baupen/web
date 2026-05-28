<?php

namespace App\Api\CustomController;

use App\Api\Entity\IssueSummary;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class IssuesSummary
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param IssueSummary[] $data
     */
    public function __invoke(array $data): Response
    {
        $json = $this->serializer->serialize($data[0], 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
