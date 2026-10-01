<?php

namespace MieuxVoter\MajorityJudgment\Model\Result;

/**
 * Generic, instantiable implementation of a PollResultInterface.
 */
class PollResult implements PollResultInterface
{
    /**
     * @param ProposalResult[] $proposalResults
     * @param ProposalResult[] $proposalResultsRanked
     */
    public function __construct(
        protected iterable $proposalResults,
        protected iterable $proposalResultsRanked,
    )
    {
    }

    /**
     * @inheritdoc
     */
    public function getProposalResults(): iterable
    {
        return $this->proposalResults;
    }

    /**
     * @inheritdoc
     */
    public function getProposalResultsRanked(): iterable
    {
        return $this->proposalResultsRanked;
    }
}
