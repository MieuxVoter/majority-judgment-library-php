<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;



/**
 *
 *
 * Class MirrorPollTally
 * @package MieuxVoter\MajorityJudgment\Tally
 */
class MirrorPollTally implements PollTallyInterface
{

    protected int $participants_amount;

    protected array $proposals_tallies = [];


    /**
     * @param int $participants_amount
     * @param array $proposals_tallies  Must have the same shape as $tallies.
     */
    public function __construct(int $participants_amount, array $proposals_tallies)
    {
        $this->participants_amount = $participants_amount;
        $this->proposals_tallies = $proposals_tallies;
    }


    /**
     * Total amount of Participants in the Poll.
     * Participants are not required to give a Grade to each Proposal,
     * so this information helps accounting for default Grades.
     *
     * @return int
     */
    public function getParticipantsAmount(): int
    {
        return $this->participants_amount;
    }


    /**
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies(): iterable
    {
        return $this->proposals_tallies;
    }
}
