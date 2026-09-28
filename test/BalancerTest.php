<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

use JetBrains\PhpStorm\NoReturn;
use MieuxVoter\MajorityJudgment\MajorityJudgment;
use PHPUnit\Framework\TestCase;

class BalancerTest extends TestCase
{
    #[NoReturn]
    public function testApplyNormalization()
    {
        // FIXME: actually test the normalization :3  (I'm rewriting everything)
        $pollTally = new ArrayPollTally(
            [
                "Arancini" => [1, 2, 3, 4],
                "Burger" => [4, 3, 2, 1],
                "Chips" => [2, 3, 3, 2],
            ],
        );
        $mj = new MajorityJudgment();
        $result = $mj->deliberate($pollTally);

        $this->assertSameSize(
            $result->getProposalResults(),
            $result->getProposalResultsRanked(),
        );
    }
}
