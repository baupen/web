<?php

namespace App\Service;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Service\Interfaces\FilterServiceInterface;
use Doctrine\Persistence\ManagerRegistry;

readonly class FilterService implements FilterServiceInterface
{
    public function __construct(private ManagerRegistry $manager)
    {
    }

    public function createFromQuery(array $filters): Filter
    {
        $constructionSiteId = $filters['constructionSite'];
        $constructionSiteRepo = $this->manager->getRepository(ConstructionSite::class);
        $constructionSite = $constructionSiteRepo->find($constructionSiteId);

        if (null === $constructionSite) {
            throw new \InvalidArgumentException('The filter must have a valid construction site set.');
        }

        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);

        $filter->setIsDeleted($this->getNullableBoolean($filters, 'isDeleted'));

        $filter->setDescription($this->getNullableValue($filters, 'description'));
        $filter->setNumbers($this->getArray($filters, 'number'));

        $filter->setWasAddedWithClient($this->getNullableBoolean($filters, 'wasAddedWithClient'));
        $filter->setIsMarked($this->getNullableBoolean($filters, 'isMarked'));

        $filter->setState($this->getNullableInt($filters, 'state'));
        $filter->setCraftsmanIds($this->getArray($filters, 'craftsman'));
        $filter->setMapIds($this->getArray($filters, 'map'));

        $dateTimeMethods = ['deadline', 'createdAt', 'registeredAt', 'resolvedAt', 'closedAt'];
        foreach ($dateTimeMethods as $dateTimeMethod) {
            $setter = 'set' . ucfirst($dateTimeMethod);

            $beforeSetter = $setter . 'Before';
            $filter->$beforeSetter($this->getNullableDateTimeImmutable($filters, $dateTimeMethod . '[before]'));

            $afterSetter = $setter . 'After';
            $filter->$afterSetter($this->getNullableDateTimeImmutable($filters, $dateTimeMethod . '[after]'));
        }

        return $filter;
    }

    private function getNullableValue(array $source, string $key): mixed
    {
        return $source[$key] ?? null;
    }

    private function getNullableBoolean(array $source, string $key): ?bool
    {
        if (!isset($source[$key])) {
            return null;
        }

        return in_array($source[$key], ['true', '1', true], true);
    }

    private function getNullableInt(array $source, string $key): ?int
    {
        return isset($source[$key]) ? (int) $source[$key] : null;
    }

    private function getNullableDateTimeImmutable(array $source, string $key): ?\DateTimeImmutable
    {
        return isset($source[$key]) ? new \DateTimeImmutable($source[$key]) : null;
    }

    private function getArray(array $source, string $key): array
    {
        if (!isset($source[$key])) {
            return [];
        }

        $value = $source[$key];

        return is_array($value) ? $value : [$value];
    }
}
