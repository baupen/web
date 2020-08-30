<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Sync;

use App\Service\Sync\DisplayNameService;
use App\Tests\Service\Sync\Model\ChildModel;
use function count;
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
        $this->assertExpectedStructureCreated(
            [
                new ChildModel(1, 'parent'),
                new ChildModel(2, 'parent child1'),
                new ChildModel(3, 'parent child2'),
            ],
            [
                new ChildModel(1, 'parent', [
                    new ChildModel(2, 'parent child1'),
                    new ChildModel(3, 'parent child2'),
                ]),
            ]
        );
    }

    public function testParentCreatedOnDemand()
    {
        $this->assertExpectedStructureCreated(
            [
                new ChildModel(1, 'parent'),
                new ChildModel(2, 'parent child 1'),
                new ChildModel(3, 'parent child 2'),
            ],
            [
                new ChildModel(1, 'parent', [
                    new ChildModel(4, 'parent child', [
                        new ChildModel(2, 'parent child 1'),
                        new ChildModel(3, 'parent child 2'),
                    ]),
                ]),
            ]
        );
    }

    public function testDeepParentStructure()
    {
        $this->assertExpectedStructureCreated(
            [
                new ChildModel(1, 'parent'),
                new ChildModel(2, 'parent child child child 1'),
                new ChildModel(3, 'parent child child child 2'),
            ],
            [
                new ChildModel(1, 'parent', [
                    new ChildModel(6, 'parent child', [
                        new ChildModel(5, 'parent child child', [
                            new ChildModel(4, 'parent child child child', [
                                new ChildModel(2, 'parent child child child 1'),
                                new ChildModel(3, 'parent child child child 2'),
                            ]),
                        ]),
                    ]),
                ]),
            ]
        );
    }

    public function testNestedParentStructure()
    {
        $this->assertExpectedStructureCreated(
            [
                new ChildModel(1, 'parent'),
                new ChildModel(2, 'parent child'),
                new ChildModel(3, 'parent child child'),
            ],
            [
                new ChildModel(1, 'parent', [
                    new ChildModel(2, 'parent child', [
                        new ChildModel(3, 'parent child child'),
                    ]),
                ]),
            ]
        );
    }

    public function testNoNeedlessParentsCreated()
    {
        $this->assertExpectedStructureCreated(
            [
                new ChildModel(1, 'parent'),
                new ChildModel(3, 'parent child child'),
            ],
            [
                new ChildModel(1, 'parent', [
                    new ChildModel(3, 'parent child child'),
                ]),
            ]
        );
    }

    /**
     * @param ChildModel[] $testCase
     * @param ChildModel[] $expectedResult
     */
    private function assertExpectedStructureCreated(array $testCase, array $expectedResult)
    {
        // calculate expected calls
        $expectedClearParentCallCount = count($expectedResult);
        $expectedAssignChildToParentCall = 0;
        foreach ($expectedResult as $item) {
            $expectedAssignChildToParentCall += $item->countChildren();
        }
        $expectedResultElements = $expectedAssignChildToParentCall + count($expectedResult);
        $testCaseElements = 0;
        foreach ($testCase as $item) {
            $testCaseElements += $item->countChildren() + 1;
        }
        $expectedCreateNewElementCalls = max($expectedResultElements - $testCaseElements, 0);

        /** @var ChildModel[] $lookup */
        $lookup = [];
        $maxId = 0;
        foreach ($testCase as $item) {
            $id = $item->getId();

            $lookup[$id] = $item;
            $maxId = max($maxId, $id);
        }

        $createNewElementCall = 0;
        $createNewElement = function ($name) use (&$createNewElementCall, &$lookup, &$maxId) {
            ++$maxId;

            $lookup[$maxId] = new ChildModel($maxId, $name);
            ++$createNewElementCall;

            return $maxId;
        };

        $assignChildToParentCall = 0;
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCall, &$lookup) {
            if (!isset($lookup[$childId])) {
                $this->fail('child id does not exist');
            }

            if (!isset($lookup[$parentId])) {
                $this->fail('parent id does not exist');
            }

            $lookup[$childId]->setParent($lookup[$parentId]);
            ++$assignChildToParentCall;
        };

        $clearParentCall = 0;
        $clearParent = function ($childId) use (&$clearParentCall, &$lookup) {
            if (!isset($lookup[$childId])) {
                $this->fail('child id does not exist');
            }

            $lookup[$childId]->setParent(null);
            ++$clearParentCall;
        };

        // prepare service argument
        $elementNames = [];
        foreach ($testCase as $item) {
            $elementNames[$item->getId()] = $item->getName();
        }

        // act
        $this->service->putIntoTreeStructure($elementNames, $createNewElement, $assignChildToParent, $clearParent);

        // actual result should be of the same form as the expected result; hence remove all elements which have a non-null parent
        $actualResult = [];
        foreach ($testCase as $item) {
            if ($item->getParent() === null) {
                $actualResult[] = $item;
            }
        }

        // assert
        $this->assertStructureMatches($expectedResult, $actualResult);
        $this->assertSame($expectedAssignChildToParentCall, $assignChildToParentCall);
        $this->assertSame($expectedCreateNewElementCalls, $createNewElementCall);
        $this->assertSame($expectedClearParentCallCount, $clearParentCall);
    }

    /**
     * @param ChildModel[] $expectedResult
     * @param ChildModel[] $actualResult
     * @param ChildModel[] $fullExpected
     * @param ChildModel[] $fullActual
     */
    private function assertStructureMatches(array $expectedResult, array $actualResult, array $fullExpected = [], array $fullActual = [])
    {
        if (count($fullExpected) === 0 && count($fullActual) === 0) {
            $fullExpected = $expectedResult;
            $fullActual = $actualResult;
        }

        $this->assertSame(count($expectedResult), count($actualResult), $this->dumpStructures($fullExpected, $fullActual));

        /** @var ChildModel[] $lookup */
        $lookup = [];
        foreach ($actualResult as $childModel) {
            $lookup[$childModel->getId()] = $childModel;
        }

        foreach ($expectedResult as $expected) {
            // get actual for comparison
            $this->assertTrue(isset($lookup[$expected->getId()]), $this->dumpStructures($fullExpected, $fullActual, $expected));
            $actual = $lookup[$expected->getId()];

            $failMessage = $this->dumpStructures($fullExpected, $fullActual, $expected, $actual);

            $this->assertSame($expected->getName(), $actual->getName(), $failMessage);
            $this->assertSame($expected->countChildren(), $actual->countChildren(), $failMessage);
            if ($expected->getParent() === null) {
                $this->assertNull($actual->getParent(), $failMessage);
            } else {
                $this->assertNotNull($actual->getParent(), $failMessage);
                $this->assertSame($expected->getParent()->getId(), $actual->getParent()->getId(), $failMessage);
            }

            $this->assertStructureMatches($expected->getChildren(), $actual->getChildren(), $fullExpected, $fullActual);
        }
    }

    /**
     * @param ChildModel $markExpected
     * @param ChildModel $markActual
     * @param ChildModel[] $expectedList
     * @param ChildModel[] $actualList
     *
     * @return string
     */
    private function dumpStructures(array $expectedList, array $actualList, ChildModel $markExpected = null, ChildModel $markActual = null)
    {
        $dump = "expected:\n";
        $dump .= $this->dumpStructure($expectedList, $markExpected);

        $dump .= "\n\nactual:\n";
        $dump .= $this->dumpStructure($actualList, $markActual);

        return $dump;
    }

    /**
     * @param ChildModel $mark
     * @param ChildModel[] $structure
     * @param int $indentation
     *
     * @return string
     */
    private function dumpStructure(array $structure, ChildModel $mark = null, int $indentation = 0)
    {
        $dump = '';
        $prefix = str_repeat("\t", $indentation);
        foreach ($structure as $item) {
            $marker = $mark === $item ? '>' : '';
            $dump .= $prefix . $marker . $item->getId() . ': ' . $item->getName() . "\n";
            $dump .= $this->dumpStructure($item->getChildren(), $mark, $indentation + 1);
        }

        return $dump;
    }
}
