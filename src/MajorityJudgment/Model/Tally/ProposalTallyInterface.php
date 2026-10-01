<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * The Tally for a Proposal, that is the tallies for each Grade.
 * This is also known as Merit Profile.
 */
interface ProposalTallyInterface
{
    /**
     * Amounts of judgments received of each grade by this proposal.
     * From "worst" grade to "best" grade.
     *
     * @return iterable<int>
     */
    public function getGradesTallies(): iterable;
}