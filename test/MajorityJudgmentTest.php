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

        if ($expectedRanks !== null) {
            $actualRanks = array_map(function ($e) {
                return $e->getRank();
            }, $result->getProposalResults());

            $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
                $expectedRanks,
                $actualRanks,
                [],
            );
        }

        if ($expectedIndices !== null) {
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
}
