<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * Convenience class for ProposalTallyInterface.
 *
 * Provide it with an array of tallies, one for each grade.
 * The order goes from "worst" grade tally to "best" grade tally.
 */
class ArrayProposalTally implements ProposalTallyInterface
{
    protected array $grades_tallies = [];

    public function __construct(array $grades_tallies_array)
    {
        foreach ($grades_tallies_array as $grade => $grade_tally_value) {
            assert(is_int($grade_tally_value), "A tally must be an integer.");
            assert(0 <= $grade_tally_value, "A tally must be positive.");

            $this->grades_tallies[] = $grade_tally_value;
        }
    }

    public function getGradesTallies(): iterable
    {
        return $this->grades_tallies;
    }
}