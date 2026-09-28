<?php

namespace MieuxVoter\MajorityJudgment\Test;

use MieuxVoter\MajorityJudgment\MajorityJudgment;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MajorityJudgmentTest extends TestCase
{
    public static function provideDeliberationTestData(): array
    {
        return [
            "Simple example" => [
                'tallies' => [
                    [1, 2, 3, 4],
                    [4, 3, 2, 1],
                    [3, 3, 3, 1],
                    [9, 0, 0, 1],
                    [0, 0, 1, 9],
                ],
                'expectedRanks' => [
                    2,
                    4,
                    3,
                    5,
                    1,
                ],
                'expectedIndices' => [
                    4,
                    0,
                    2,
                    1,
                    3,
                ],
            ],
            "Paris' real data" => [
                'tallies' => [
                    [0, 2, 0, 7, 5, 4],
                    [1, 1, 1, 7, 4, 4],
                    [2, 4, 2, 1, 5, 4],
                    [1, 2, 4, 6, 2, 3],
                    [1, 2, 5, 4, 3, 3],
                    [1, 4, 3, 2, 5, 3],
                    [1, 1, 2, 4, 4, 6],
                    [1, 3, 1, 4, 3, 6],
                    [1, 2, 2, 6, 3, 4],
                    [2, 2, 1, 5, 2, 6],
                    [0, 0, 0, 0, 1, 17],
                    [1, 0, 2, 3, 7, 5],
                    [0, 2, 1, 1, 4, 10],
                    [1, 1, 3, 1, 5, 7],
                    [1, 1, 0, 1, 11, 4],
                    [1, 2, 3, 6, 5, 1],
                    [1, 0, 2, 0, 3, 12],
                ],
                'expectedRanks' => [
                    9,
                    12,
                    10,
                    15,
                    17,
                    16,
                    7,
                    8,
                    13,
                    11,
                    1,
                    6,
                    3,
                    4,
                    5,
                    14,
                    2,
                ],
            ],
            "Equality is allowed & order is stable" => [
                'tallies' => [
                    [1, 2, 3, 4],
                    [4, 3, 2, 1],
                    [1, 2, 3, 4],
                ],
                'expectedRanks' => [
                    1,
                    3,
                    1,
                ],
                'expectedIndices' => [
                    0,
                    2,
                    1,
                ],
            ],
            "A single candidate is allowed" => [
                'tallies' => [
                    [1, 1, 1],
                ],
                'expectedRanks' => [
                    1,
                ],
                'expectedIndices' => [
                    0,
                ],
            ],
        ];
    }

    #[DataProvider('provideDeliberationTestData')]
    public function testMajorityJudgment(
        array $tallies,
        array|null $expectedRanks = null,
        array|null $expectedIndices = null,
    )
    {
        $mj = new MajorityJudgment();
        $result = $mj->deliberate(new ArrayPollTally($tallies));

        if ($expectedRanks != null) {
            $actualRanks = array_map(function ($e) {
                return $e->getRank();
            }, $result->getProposalResults());

            $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
                $expectedRanks,
                $actualRanks,
                [],
            );
        }

        if ($expectedIndices != null) {
            $actualIndices = array_map(function ($e) {
                return $e->getIndex();
            }, $result->getProposalResultsRanked());

            $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
                $expectedIndices,
                $actualIndices,
                [],
            );
        }
    }

    public function testReadmeExample01() {
        $grades = [
            "to reject",
            "insufficient",
            "passable",
            "somewhat good",
            "good",
            "very good",
            "excellent",
        ];
        $proposals = [
            "Arancini",
            "Burger",
            "Chips",
        ];
        $merit_profiles = [
            [1, 1, 4, 3, 7, 4, 1], // for each proposal, tally the
            [2, 2, 5, 5, 5, 0, 2], // amount of judgments for each grade
            [0, 1, 2, 1, 6, 7, 4], // from "worst" grade to "best" grade
        ];

        $pollTally = new ArrayPollTally($merit_profiles);
        $mj = new MajorityJudgment();
        $result = $mj->deliberate($pollTally);

        foreach($result->getProposalResultsRanked() as $proposalResult) {
            // … Do something
            print(sprintf(
                "#%d %s (%s)\n",
                $proposalResult->getRank(),
                $proposals[$proposalResult->getIndex()],
                $grades[$proposalResult->getMedian()],
            ));
        }

        // #1 Chips (very good)
        // #2 Arancini (good)
        // #3 Burger (somewhat good)

        $this->assertEquals(
            2,
            $result->getProposalResults()[0]->getRank(),
        );
        $this->assertEquals(
            3,
            $result->getProposalResults()[1]->getRank(),
        );
        $this->assertEquals(
            1,
            $result->getProposalResults()[2]->getRank(),
        );
    }

    public function testGetMedianGradeIndex()
    {
        $expectations = [
            [
                'tallies' => [1, 1],
                'index' => 0,
            ],
            [
                'tallies' => [2, 2, 2],
                'index' => 1,
            ],
            [
                'tallies' => [2, 2, 7],
                'index' => 2,
            ],
            [
                'tallies' => [2, 2, 5, 1, 3],
                'index' => 2,
            ],
            [
                'tallies' => [2, 3, 5, 7, 11, 13],
                'index' => 4,
            ],
            [
                'tallies' => [0, 0, 0, 0, 0, 0],
                'index' => 0,
            ],
            [
                'tallies' => [0, 0, 0, 1, 0, 0],
                'index' => 3,
            ],
            [
                'tallies' => [0, 0, 1, 0, 1, 0],
                'index' => 2,
            ],
            [
                'tallies' => [0, 2, 2],
                'index' => 1,
            ],
        ];

        foreach ($expectations as $expectation) {
            $this->assertEquals(
                $expectation['index'],
                MajorityJudgment::getMedianGradeIndex($expectation['tallies']),
                "Found the expected median grade index."
            );
        }
    }

    function testBiggestGroup()
    {
        $expectations = [
            [
                'tallies' => [1, 4, 7, 0, 6],
                'around' => 2, // median
                'size' => 6,
                'sign' => 1,
                'grade' => 4,
            ],
            [
                'tallies' => [1, 2, 1, 0, 6],
                'around' => 4, // median
                'size' => 4,
                'sign' => -1,
                'grade' => 2,
            ],
            [
                'tallies' => [1, 2, 1, 0, 6],
                'around' => 4, // median
                'size' => 4,
                'sign' => -1,
                'grade' => 2,
            ],
            [
                'tallies' => [0, 1, 0, 1, 0],
                'around' => 1, // median
                'size' => 1,
                'sign' => 1,
                'grade' => 3,
            ],
            [
                'tallies' => [0, 0, 0, 17, 0],
                'around' => 3, // median
                'size' => 0,
                'sign' => -1,
                'grade' => 0,
            ],
            [
                'tallies' => [5, 0, 0, 0, 0],
                'around' => 0, // median
                'size' => 0,
                'sign' => -1,
                'grade' => 0,
            ],
        ];

        foreach ($expectations as $expectation) {
            [$size, $sign, $grade] = MajorityJudgment::getBiggestGroup(
                $expectation['around'],
                $expectation['tallies']
            );
            $this->assertEquals(
                $expectation['size'], $size,
                "Group size matches."
            );
            $this->assertEquals(
                $expectation['sign'], $sign,
                "Group sign matches."
            );
            $this->assertEquals(
                $expectation['grade'], $grade,
                "Group grade matches."
            );
        }
    }

}
