<?php

namespace MieuxVoter\MajorityJudgment\Test;

use Exception;
use MieuxVoter\MajorityJudgment\MajorityJudgment;
use MieuxVoter\MajorityJudgment\Model\Exception\UnbalancedTalliesException;
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
            "Millions of voters" => [
                'tallies' => [
                    [5_000_000, 2_000_000, 3_000_000],
                    [7_000_000, 1_000_000, 2_000_000],
                ],
                'expectedRanks' => [
                    1,
                    2,
                ],
                'expectedIndices' => [
                    0,
                    1,
                ],
            ],
            "Billions of voters" => [
                'tallies' => [
                    [5_000_000_000, 2_000_000_000, 3_000_000_000],
                    [7_000_000_000, 1_000_000_000, 2_000_000_000],
                ],
                'expectedRanks' => [
                    1,
                    2,
                ],
                'expectedIndices' => [
                    0,
                    1,
                ],
            ],
            "Trillions of voters" => [
                'tallies' => [
                    [1e12, 2e12, 4e12],
                    [2e12, 3e12, 2e12],
                    [1e12, 1e12, 5e12],
                ],
                'expectedRanks' => [
                    2,
                    3,
                    1,
                ],
                'expectedIndices' => [
                    2,
                    0,
                    1,
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
            "Unbalanced tallies raise an exception (1)" => [
                'tallies' => [
                    [1, 1, 1],
                    [1, 0, 1],
                ],
                'expectedException' => UnbalancedTalliesException::class,
            ],
            "Unbalanced tallies raise an exception (2)" => [
                'tallies' => [
                    [0, 0, 1e3],
                    [333, 333, 333],
                ],
                'expectedException' => UnbalancedTalliesException::class,
            ],
            "Balance using normalization (1)" => [
                'tallies' => [
                    [1, 1, 1],
                    [1, 0, 1],
                    [7, 7, 7],
                    [7, 0, 7],
                ],
                'balanceUsingNormalization' => true,
                'expectedRanks' => [
                    1,
                    3,
                    1,
                    3,
                ],
                'expectedIndices' => [
                    0,
                    2,
                    1,
                    3,
                ],
            ],
            "Balance using normalization (2)" => [
                'tallies' => [
                    [1, 2, 3],
                    [4, 5, 6],
                ],
                'balanceUsingNormalization' => true,
                'expectedRanks' => [
                    1,
                    2,
                ],
                'expectedIndices' => [
                    0,
                    1,
                ],
            ],
        ];
    }

    #[DataProvider('provideDeliberationTestData')]
    public function testMajorityJudgment(
        array   $tallies,
        bool    $balanceUsingNormalization = false,
        ?array  $expectedRanks = null,
        ?array  $expectedIndices = null,
        ?string $expectedException = null,
    )
    {
        /** @var Exception $actualException */
        $actualException = null;
        $actualExceptionClass = "";
        try {
            $mj = new MajorityJudgment();
            $result = $mj->deliberate(
                new ArrayPollTally($tallies),
                $balanceUsingNormalization,
            );
        } catch (Exception $e) {
            $actualException = $e;
            $actualExceptionClass = $e::class;
        }

        if ($expectedException !== null) {
            $this->assertEquals(
                expected: $expectedException,
                actual: $actualException::class,
                message: "A $expectedException exception should be thrown.",
            );
        } else {
            $message = $actualException?->getMessage();
            $this->assertEmpty(
                actual: $actualException,
                message: "$actualExceptionClass was thrown with message:\n$message\n",
            );
        }

        if ($expectedRanks !== null && isset($result)) {
            $actualRanks = array_map(callback: function ($e) {
                return $e->getRank();
            }, array: $result->getProposalResults());

            $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
                expected: $expectedRanks,
                actual: $actualRanks,
                keysToBeIgnored: [],
            );
        }

        if ($expectedIndices !== null && isset($result)) {
            $actualIndices = array_map(callback: function ($e) {
                return $e->getIndex();
            }, array: $result->getProposalResultsRanked());

            $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
                expected: $expectedIndices,
                actual: $actualIndices,
                keysToBeIgnored: [],
            );
        }
    }

    public function testReadmeExample01()
    {
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

        foreach ($result->getProposalResultsRanked() as $proposalResult) {
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
            expected: 2,
            actual: $result->getProposalResults()[0]->getRank(),
        );
        $this->assertEquals(
            expected: 3,
            actual: $result->getProposalResults()[1]->getRank(),
        );
        $this->assertEquals(
            expected: 1,
            actual: $result->getProposalResults()[2]->getRank(),
        );

        $this->assertEquals(
            expected: 4,
            actual: $result->getProposalResults()[0]->getAnalysis()->getMedianGrade(),
        );
    }
}
