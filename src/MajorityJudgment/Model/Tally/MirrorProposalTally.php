<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * Convenience class for ProposalTallyInterface.
 *
 * Class MirrorProposalTally
 * @package MieuxVoter\MajorityJudgment\Tally
 */
class MirrorProposalTally implements ProposalTallyInterface
{

    protected mixed $proposal;

    /** @var GradeTallyInterface[] $grades_tallies */
    protected array $grades_tallies = [];

    /**
     * @param $proposal
     * @param GradeTallyInterface[] $grades_tallies
     */
    public function __construct($proposal, array $grades_tallies)
    {
        $this->proposal = $proposal;
        $this->grades_tallies = $grades_tallies;
    }

    public function getProposal(): mixed
    {
        return $this->proposal;
    }

    /**
     * @return GradeTallyInterface[]
     */
    public function getGradesTallies(): array
    {
        return $this->grades_tallies;
    }
}