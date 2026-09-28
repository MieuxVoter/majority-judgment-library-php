<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * The Tally for a Proposal, that is the tallies for each Grade.
 * This is also known as Merit Profile.
 */
interface ProposalTallyInterface
{
    /**
     * @return iterable<int>
     */
    public function getGradesTallies(): iterable;
}