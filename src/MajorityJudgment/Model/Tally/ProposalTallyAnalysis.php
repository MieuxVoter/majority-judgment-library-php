<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * This does not compute the score nor the rank,
 * but provides the data we need to compute them.
 *
 * We use the lower median grade (in case of odd amounts of judges),
 * but that could be a constructor parameter.
 */
class ProposalTallyAnalysis
{
    /**
     * Input proposal tally to analyze.
     */
    protected ProposalTallyInterface $proposalTally;

    /**
     * Total amount of available grades, including the ones that received no judgments.
     * This is usually around 7, and cannot be less than two.
     */
    protected int $amountOfGrades;

    /**
     * Total amount of judgments received by this proposal.
     */
    protected int $amountOfJudgments;

    /**
     * Index of the median grade.
     */
    protected int $medianGrade;

    /**
     * ProposalTallyAnalysis constructor.
     * @param ProposalTallyInterface $proposalTally
     */
    public function __construct(ProposalTallyInterface $proposalTally)
    {
        $this->proposalTally = $proposalTally;
        $gradesTallies = $proposalTally->getGradesTallies();

        $this->amountOfGrades = 0;
        $this->amountOfJudgments = 0;
        $tallies = []; // same as ProposalTallyInterface but in primitives form
        foreach ($gradesTallies as $gradeTally) {
            $tallies[] = $gradeTally->getTally();
            $this->amountOfJudgments += $gradeTally->getTally();
            $this->amountOfGrades += 1;
        }

        $this->medianGrade = self::computeMedianGradeIndex($tallies);
    }

    /**
     * Find the index of the median grade from the given array of tallies.
     *
     * @param int[] $tallies
     *   Indexed array of integers.
     *   Tally for each Grade, in the 'worst" grade to "best" grade order.
     *   A Tally here is an amount of Judgments emitted with a specific Grade.
     *   This looks like the merit profile, in other words.
     *   Eg: A value of [1, 4, 3] would mean (Reject=1, Passable=4, Good=3)
     * @param int|null $total
     * @param bool $low
     *   Use the low (default) or high median, when there's an EVEN amount of judgments.
     * @return int
     */
    static function computeMedianGradeIndex(array $tallies, ?int $total = null, $low = true): int
    {
        if (null === $total) {
            $total = 0;
            foreach ($tallies as $tally) {
                $total += $tally;
            }
        }
        assert(0 <= $total, "A negative amount of judgments is absurd.  Integer buffer Overflow?");

        if (0 == $total) {
            return 0;
        }

        $adjustedTotal = $total - 1;
        if ( ! $low) {
            $adjustedTotal = $total + 1;
        }

        $medianIndex = intdiv($adjustedTotal, 2);
        $cursorIndex = 0;
        foreach ($tallies as $gradeIndex => $tally) {
            if (0 == $tally) {
                continue;
            }

            $startIndex = $cursorIndex;
            $cursorIndex += $tally;

            if (
                $startIndex <= $medianIndex
                &&
                $medianIndex < $cursorIndex
            ) {
                return $gradeIndex;
            }
        }

        return 0;
    }

    /**
     * @see $medianGrade
     */
    public function getMedianGrade(): int
    {
        return $this->medianGrade;
    }
}