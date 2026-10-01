<?php

namespace MieuxVoter\MajorityJudgment\Model\Result;

/**
 * This is the output of a deliberation.
 * It holds a ranking of the Proposals (a leaderboard?).
 */
interface PollResultInterface
{
    /**
     * These results are in the same order as the input proposals.
     *
     * @return ProposalResult[]
     */
    public function getProposalResults(): iterable;

    /**
     * These results are ordered by rank, "best" first.
     * Two or more results may share the same rank, if they share the exact same merit profiles.
     * In this case, you will find them in the order the proposals were added, i.e. the sort is stable.
     *
     * @return ProposalResult[]
     */
    public function getProposalResultsRanked(): iterable;
}