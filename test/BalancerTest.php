<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

use JetBrains\PhpStorm\NoReturn;
use MieuxVoter\MajorityJudgment\Balancer;
use PHPUnit\Framework\TestCase;

class BalancerTest extends TestCase
{
    #[NoReturn]
    public function testBalanceUsingStaticDefaultGrade()
    {
        $initialPollTally = new ArrayPollTally(
            [
                "Arancini" => [1, 2, 3, 4],
                "Burger" => [4, 0, 0, 0],
                "Chips" => [0, 1, 1, 2],
            ],
        );

        $balancedPollTally = Balancer::balanceUsingStaticDefaultGrade(
            $initialPollTally,
        );

        //print_r($balancedPollTally->getProposalsTallies()[0]->getGradesTallies());
        //print_r($balancedPollTally->getProposalsTallies()[1]->getGradesTallies());
        //print_r($balancedPollTally->getProposalsTallies()[2]->getGradesTallies());

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [1, 2, 3, 4],
            $balancedPollTally->getProposalsTallies()[0]->getGradesTallies(),
            [],
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [10, 0, 0, 0],
            $balancedPollTally->getProposalsTallies()[1]->getGradesTallies(),
            [],
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [6, 1, 1, 2],
            $balancedPollTally->getProposalsTallies()[2]->getGradesTallies(),
            [],
        );

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [1, 2, 3, 4],
            $initialPollTally->getProposalsTallies()[0]->getGradesTallies(),
            [],
            "Initial poll tally should be unchanged (1/3)",
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [4, 0, 0, 0],
            $initialPollTally->getProposalsTallies()[1]->getGradesTallies(),
            [],
            "Initial poll tally should be unchanged (2/3)",
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [0, 1, 1, 2],
            $initialPollTally->getProposalsTallies()[2]->getGradesTallies(),
            [],
            "Initial poll tally should be unchanged (3/3)",
        );
    }

    #[NoReturn]
    public function testBalanceUsingMedianDefaultGrade()
    {
        $initialPollTally = new ArrayPollTally(
            [
                "Arancini" => [1, 2, 3, 4],
                "Burger" => [2, 0, 0, 0],
                "Chips" => [0, 1, 1, 0],
            ],
        );

        $balancedPollTally = Balancer::balanceUsingMedianDefaultGrade(
            $initialPollTally,
        );

        //print_r($balancedPollTally->getProposalsTallies()[0]->getGradesTallies());
        //print_r($balancedPollTally->getProposalsTallies()[1]->getGradesTallies());
        //print_r($balancedPollTally->getProposalsTallies()[2]->getGradesTallies());

        // [1, 2, 3, 4]
        // [10, 0, 0, 0]
        // [0, 9, 1, 0]

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [1, 2, 3, 4],
            $balancedPollTally->getProposalsTallies()[0]->getGradesTallies(),
            [],
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [10, 0, 0, 0],
            $balancedPollTally->getProposalsTallies()[1]->getGradesTallies(),
            [],
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [0, 9, 1, 0],
            $balancedPollTally->getProposalsTallies()[2]->getGradesTallies(),
            [],
        );

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [1, 2, 3, 4],
            $initialPollTally->getProposalsTallies()[0]->getGradesTallies(),
            [],
            "Initial poll tally should be unchanged (1/3)",
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [2, 0, 0, 0],
            $initialPollTally->getProposalsTallies()[1]->getGradesTallies(),
            [],
            "Initial poll tally should be unchanged (2/3)",
        );
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            [0, 1, 1, 0],
            $initialPollTally->getProposalsTallies()[2]->getGradesTallies(),
            [],
            "Initial poll tally should be unchanged (3/3)",
        );
    }
}
