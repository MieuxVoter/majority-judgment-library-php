<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * A Poll Tally holds the amount of Judgments for each Grade, on each Proposal.
 * It also holds the total amount of Participants, in order to account for default grades.
 *
 * This is the interface of the main input of this library,
 * that which is given to the MajorityJudgmentResolver in order to derive a Result (a ranking of proposals).
 *
 * @see ArrayPollTally
 */
interface PollTallyInterface
{
    /**
     * Tallies for each Proposal.
     * The order matters only in *perfect* equality scenarios.
     * In these extreme cases, the order of ex-æquo proposals in the Result
     * will reflect the order of proposals submitted here.
     *
     * You can make your own convenience class of use one of the provided,
     * such as ArrayProposalTally.
     *
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies(): iterable;
}
