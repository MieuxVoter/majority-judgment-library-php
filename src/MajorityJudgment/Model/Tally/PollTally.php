<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * The most basic implementation of a PollTallyInterface.
 *
 * @see ArrayPollTally
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
     * @inheritdoc
     */
    public function getProposalsTallies(): iterable
    {
        return $this->proposalsTallies;
    }
}
