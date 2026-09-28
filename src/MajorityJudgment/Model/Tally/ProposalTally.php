<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * The most basic implementation of a ProposalTallyInterface.
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