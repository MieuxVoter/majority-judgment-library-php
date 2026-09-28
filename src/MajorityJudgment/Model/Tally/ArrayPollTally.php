<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * Create a Poll Tally from a PHP indexed array.
 *
 * For example, for two proposals and seven grades:
 *
 *     $tally = new ArrayPollTally([
 *       [1, 1, 4, 3, 7, 4, 1], // merit profile of the first proposal
 *       [0, 2, 4, 6, 4, 2, 3], // merit profile of the second proposal
 *     ]);
 *
 * For each Proposal, the tallies of the grades must be given from "worst" grade to "best" grade.
 */
class ArrayPollTally implements PollTallyInterface
{
    protected array $proposalsTallies = [];

    /**
     * @param iterable<iterable<int>> $tally_per_proposal
     */
    public function __construct(
        iterable $tally_per_proposal,
    )
    {
        foreach ($tally_per_proposal as $proposal_tally_array) {
            $this->proposalsTallies[] = new ArrayProposalTally($proposal_tally_array);
        }
    }

    /**
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies(): iterable
    {
        return $this->proposalsTallies;
    }
}
