<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * The most basic implementation of a PollTallyInterface.
 */
class PollTally implements PollTallyInterface
{
    /**
     * @param ProposalTallyInterface[] $proposalsTallies
     */
    public function __construct(
        protected array $proposalsTallies,
    )
    {
    }

    /**
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies(): iterable
    {
        return $this->proposalsTallies;
    }
}
