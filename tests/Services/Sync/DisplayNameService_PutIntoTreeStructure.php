<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services\Sync;

use App\Service\Sync\DisplayNameService;
use App\Tests\Services\Sync\Model\ChildModel;
use PHPUnit\Framework\TestCase;

class DisplayNameService_PutIntoTreeStructure extends TestCase
{
    /**
     * @var DisplayNameService
     */
    private $service;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->service = new DisplayNameService();
    }

    public function testSimpleParentWithChildren()
    {
        $elementNames = [
            1 => 'parent',
            2 => 'parent child1',
            3 => 'parent child2',
        ];

        $createNewElement = function () {
            $this->fail('no parent should be created');
        };

        $assignChildToParentCallCount = 0;
        $seenChildIds = [];
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCallCount, &$seenChildIds) {
            $this->assertSame(1, $parentId);
            $seenChildIds[] = $childId;

            ++$assignChildToParentCallCount;
        };

        $clearParentCallCount = 0;
        $seenParentIds = [];
        $clearParent = function ($childId) use (&$clearParentCallCount, &$seenParentIds) {
            $seenParentIds[] = $childId;

            ++$clearParentCallCount;
        };

        $this->service->putIntoTreeStructure($elementNames, $createNewElement, $assignChildToParent, $clearParent);

        $this->assertSame(2, $assignChildToParentCallCount);
        $this->assertEquals([2, 3], $seenChildIds, '$canonicalize = true', 0.0, 1, true);
        $this->assertSame(1, $clearParentCallCount);
        $this->assertEquals([1], $seenParentIds, '$canonicalize = true', 0.0, 1, true);
    }

    private function assertDetailedExample(array $testCase, array $expectedResult, int $expectedCreateNewElementCalls, int $expectedAssignChildToParentCall, int $expectedClearParentCallCount)
    {
        $testCase = [
            new ChildModel(1, 'parent'),
            new ChildModel(2, 'parent child1'),
            new ChildModel(3, 'parent child2'),
        ];

        $expectedResult = [
            new ChildModel(1, 'parent'),
            1 => [
                2, 3,
            ],
        ];

        $newParentId = 4;
        $createNewElementCall = 0;
        $createNewElement = function ($name) use (&$createNewElementCall, $newParentId) {
            $this->assertSame($name, 'parent child');
            ++$createNewElementCall;

            return $newParentId;
        };

        $assignChildToParentCall = 0;
        $seenChildIds = [];
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCall, $newParentId, &$seenChildIds) {
            if ($childId === $newParentId) {
                $this->assertSame(1, $parentId);
            } else {
                $this->assertSame($newParentId, $parentId);
            }
            $seenChildIds[] = $childId;

            ++$assignChildToParentCall;
        };

        $clearParentCallCount = 0;
        $seenParentIds = [];
        $clearParent = function ($childId) use (&$clearParentCallCount, &$seenParentIds) {
            $seenParentIds[] = $childId;

            ++$clearParentCallCount;
        };

        $this->service->putIntoTreeStructure($testCase, $createNewElement, $assignChildToParent, $clearParent);

        $this->assertSame($expectedAssignChildToParentCall, $assignChildToParentCall);
        $this->assertSame($expectedCreateNewElementCalls, $createNewElementCall);
        $this->assertEquals([2, 3, 4], $seenChildIds, '$canonicalize = true', 0.0, 1, true);
        $this->assertSame($expectedClearParentCallCount, $clearParentCallCount);
        $this->assertEquals([1], $seenParentIds, '$canonicalize = true', 0.0, 1, true);
    }

    public function testSimilarFiles()
    {
        $elementNames = [
            1 => 'parent',
            2 => 'parent child1',
            3 => 'parent child2',
        ];

        $createNewElement = function () {
            $this->fail('no parent should be created');
        };

        $assignChildToParentCallCount = 0;
        $seenChildIds = [];
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCallCount, &$seenChildIds) {
            $this->assertSame(1, $parentId);
            $seenChildIds[] = $childId;

            ++$assignChildToParentCallCount;
        };

        $clearParentCallCount = 0;
        $seenParentIds = [];
        $clearParent = function ($childId) use (&$clearParentCallCount, &$seenParentIds) {
            $seenParentIds[] = $childId;

            ++$clearParentCallCount;
        };

        $this->service->putIntoTreeStructure($elementNames, $createNewElement, $assignChildToParent, $clearParent);

        $this->assertSame(2, $assignChildToParentCallCount);
        $this->assertEquals([2, 3], $seenChildIds, '$canonicalize = true', 0.0, 1, true);
        $this->assertSame(1, $clearParentCallCount);
        $this->assertEquals([1], $seenParentIds, '$canonicalize = true', 0.0, 1, true);
    }

    public function testParentCreatedOnDemand()
    {
        $elementNames = [
            1 => 'parent',
            2 => 'parent child 1',
            3 => 'parent child 2',
        ];

        $newParentId = 4;
        $createNewElementCallCount = 0;
        $createNewElement = function ($name) use (&$createNewElementCallCount, $newParentId) {
            $this->assertSame($name, 'parent child');
            ++$createNewElementCallCount;

            return $newParentId;
        };

        $assignChildToParentCallCount = 0;
        $seenChildIds = [];
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCallCount, $newParentId, &$seenChildIds) {
            if ($childId === $newParentId) {
                $this->assertSame(1, $parentId);
            } else {
                $this->assertSame($newParentId, $parentId);
            }
            $seenChildIds[] = $childId;

            ++$assignChildToParentCallCount;
        };

        $clearParentCallCount = 0;
        $seenParentIds = [];
        $clearParent = function ($childId) use (&$clearParentCallCount, &$seenParentIds) {
            $seenParentIds[] = $childId;

            ++$clearParentCallCount;
        };

        $this->service->putIntoTreeStructure($elementNames, $createNewElement, $assignChildToParent, $clearParent);

        $this->assertSame(3, $assignChildToParentCallCount);
        $this->assertSame(1, $createNewElementCallCount);
        $this->assertEquals([2, 3, 4], $seenChildIds, '$canonicalize = true', 0.0, 1, true);
        $this->assertSame(1, $clearParentCallCount);
        $this->assertEquals([1], $seenParentIds, '$canonicalize = true', 0.0, 1, true);
    }
}
