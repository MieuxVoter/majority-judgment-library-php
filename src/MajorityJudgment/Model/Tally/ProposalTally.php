<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * The most basic implementation of a ProposalTallyInterface.
 *
 * Provide it with an array of tallies, one for each grade.
 * The order goes from "worst" grade tally to "best" grade tally.
 */
class ProposalTally implements ProposalTallyInterface
{
    /**
     * @param iterable<int> $gradesTallies
     */
    public function __construct(
        protected array $gradesTallies,
    )
    {
    }

    /**
     * @return iterable<int>
     */
    public function getGradesTallies(): array
    {
        return $this->gradesTallies;
    }
}