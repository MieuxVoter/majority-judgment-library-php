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
     * @var array<int> Amounts of grades received, for each grade, from "worst" grade to "best" grade.
     */
    protected array $meritProfile;

    /**
     * Total amount of available grades, including the ones that received no judgments.
     * This is usually around 7, and cannot be less than two.
     */
    protected int $amountOfGrades;

    /**
     * Total amount of judgments received by this proposal.
     */
    protected int $totalSize;

    /**
     * Index of the median grade.
     */
    protected int $medianGrade;

    /**
     * Size of the median group, also known as the majority group.
     * This can never be zero, unless there are no judgments at all in the tally.
     */
    protected int $medianGroupSize;

    /**
     * Index of the lowest grade of the adhesion group, if any.
     */
    protected int $adhesionGrade;

    /**
     * Size of the adhesion group, if any.
     */
    protected int $adhesionGroupSize;

    /**
     * Index of the highest grade of the contestation group, if any.
     */
    protected int $contestationGrade;

    /**
     * Size of the contestation group, if any.
     */
    protected int $contestationGroupSize;

    /**
     * Index of the significant grade of the biggest group out of the median.
     */
    protected int $secondMedianGrade;

    /**
     * Size of the biggest group out of the median.
     * This is either the size of the adhesion group or the size of the contestation group.
     */
    protected int $secondMedianGroupSize;

    /**
     * -1 for contestation
     * +1 for adhesion
     * ±0 for empty group size
     */
    protected int $secondMedianGroupSign;

    /**
     * The deep majority gauge holds enough data to rank proposals without any approximation.
     * It's a like a more complete, recursive majority gauge.
     *
     * It's a flat array of as many pairs as there are grades.
     * Each pair is made of a grade index and of the signed size of the second median group.
     * We compute it somewhat recursively, like so:
     * 1. Compute the median grade and the second median group signed size
     * 2. Repaint the median grade into the second median grade
     * 3. Go to 1. (but stop after amountOfGrades loops)
     *
     * @var array<int>
     */
    protected array $deepMajorityGauge;

    public function __construct(
        ProposalTallyInterface $proposalTally,
        bool                   $favorContestation = true,
        bool                   $deep = true,
    )
    {
        $this->performAnalysis(
            $proposalTally->getGradesTallies(),
            $favorContestation,
        );

        if ($deep) {
            $this->performDeepAnalysis($favorContestation);
        }
    }

    protected function performAnalysis(
        array $meritProfile,
        bool  $favorContestation = true,
    ): void
    {
        $this->meritProfile = $meritProfile;

        $this->amountOfGrades = count($this->meritProfile);
        $this->totalSize = array_sum($this->meritProfile);

        $this->medianGrade = 0;
        $this->medianGroupSize = 0;
        $this->contestationGrade = 0;
        $this->contestationGroupSize = 0;
        $this->adhesionGrade = 0;
        $this->adhesionGroupSize = 0;

        $medianOffset = 2;
        if ($favorContestation) {
            $medianOffset = 1;
        }
        $medianCursor = intdiv($this->totalSize + $medianOffset, 2);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $tallyBeforeCursor = 0;
        $tallyCursor = 0;
        $foundMedian = false;

        foreach ($this->meritProfile as $grade => $gradeTally) {
            $tallyBeforeCursor = $tallyCursor;
            $tallyCursor += $gradeTally;

            if ( ! $foundMedian) {
                if ($tallyCursor >= $medianCursor) {
                    $foundMedian = true;
                    $this->medianGrade = $grade;
                    $this->contestationGroupSize = $tallyBeforeCursor;
                    $this->medianGroupSize = $gradeTally;
                    $this->adhesionGroupSize = (
                        $this->totalSize - $this->contestationGroupSize - $this->medianGroupSize
                    );
                } else {
                    if ($gradeTally > 0) {
                        $this->contestationGrade = $grade;
                    }
                }
            } else {
                if ($gradeTally > 0 && $this->adhesionGrade == 0) {
                    $this->adhesionGrade = $grade;
                }
            }
        }

        if ($this->adhesionGroupSize > $this->contestationGroupSize) {
            $this->secondMedianGrade = $this->adhesionGrade;
            $this->secondMedianGroupSign = 1;
            $this->secondMedianGroupSize = $this->adhesionGroupSize;
        } elseif ($this->adhesionGroupSize < $this->contestationGroupSize) {
            $this->secondMedianGrade = $this->contestationGrade;
            $this->secondMedianGroupSign = -1;
            $this->secondMedianGroupSize = $this->contestationGroupSize;
        } else { // equality
            if ($favorContestation) {
                $this->secondMedianGrade = $this->contestationGrade;
                $this->secondMedianGroupSign = -1;
                $this->secondMedianGroupSize = $this->contestationGroupSize;
            } else {
                $this->secondMedianGrade = $this->adhesionGrade;
                $this->secondMedianGroupSign = 1;
                $this->secondMedianGroupSize = $this->adhesionGroupSize;
            }
        }

        if ($this->secondMedianGroupSize == 0) {
            $this->secondMedianGroupSign = 0;
        }
    }

    protected function performDeepAnalysis(
        bool $favorContestation = true,
    ): void
    {
        $gauge = [];
        $tally = $this->meritProfile; // PHP copies arrays by default

        $cursorGrade = $this->medianGrade;
        $minProcessedGrade = $cursorGrade;
        $maxProcessedGrade = $cursorGrade;

        for ($i = 0; $i < $this->amountOfGrades - 1; $i++) {
            $analysis = new ProposalTallyAnalysis(
                new ProposalTally($tally),
                $favorContestation,
                false,
            );

            $gauge[] = $analysis->getMedianGrade();
            $gauge[] = $analysis->getSecondMedianGroupSize() * $analysis->getSecondMedianGroupSign();

            if ($analysis->secondMedianGroupSign > 0) {
                $cursorGrade = $maxProcessedGrade + 1;
                $maxProcessedGrade = $cursorGrade;
            } else if ($analysis->secondMedianGroupSign < 0) {
                $cursorGrade = $minProcessedGrade - 1;
                $minProcessedGrade = $cursorGrade;
            }

            if ($analysis->getMedianGrade() !== $cursorGrade) {
                $tally[$analysis->getMedianGrade()] = 0;
                $tally[$cursorGrade] += $analysis->getMedianGroupSize();
            }
        }

        $this->deepMajorityGauge = $gauge;
    }

    /**
     * @see $medianGrade
     */
    public function getMedianGrade(): int
    {
        return $this->medianGrade;
    }

    /**
     * @see $secondMedianGroupSize
     */
    public function getSecondMedianGroupSize(): int
    {
        return $this->secondMedianGroupSize;
    }

    /**
     * @see $secondMedianGroupSign
     */
    public function getSecondMedianGroupSign(): int
    {
        return $this->secondMedianGroupSign;
    }

    /**
     * @see $medianGroupSize
     */
    public function getMedianGroupSize(): int
    {
        return $this->medianGroupSize;
    }

    /**
     * @see $secondMedianGrade
     */
    public function getSecondMedianGrade(): int
    {
        return $this->secondMedianGrade;
    }

    /**
     * @see $amountOfGrades
     */
    public function getAmountOfGrades(): int
    {
        return $this->amountOfGrades;
    }

    /**
     * @see $totalSize
     */
    public function getTotalSize(): int
    {
        return $this->totalSize;
    }

    /**
     * @see $adhesionGrade
     */
    public function getAdhesionGrade(): int
    {
        return $this->adhesionGrade;
    }

    /**
     * @see $adhesionGroupSize
     */
    public function getAdhesionGroupSize(): int
    {
        return $this->adhesionGroupSize;
    }

    /**
     * @see $contestationGrade
     */
    public function getContestationGrade(): int
    {
        return $this->contestationGrade;
    }

    /**
     * @see $contestationGroupSize
     */
    public function getContestationGroupSize(): int
    {
        return $this->contestationGroupSize;
    }

    /**
     * @return int[]
     * @see $deepMajorityGauge
     */
    public function getDeepMajorityGauge(): array
    {
        return $this->deepMajorityGauge;
    }
}
