<?php

namespace MieuxVoter\MajorityJudgment\Model\Result;

/**
 * An element of the leaderboard of a PollResult.
 */
class ProposalResult
{
    /**
     * Index of the Proposal in the input array of tallies.
     */
    protected int $index;

    /**
     * Rank of the Proposal, in the Result.
     *
     * Two proposals may share the same rank.
     * The "best" proposal will have rank 1.
     * The rank increases continuously.
     */
    protected int $rank;

    /**
     * The higher the score, the better this Proposal is considered.
     * It depends on the meaning of the grades, of course.
     * Higher scores means higher grades; and vice versa.
     * Scores are strings, compared lexicographically.
     *
     * @var string $score
     */
    protected string $score;

    /**
     * Median Grade received by the Proposal.
     */
    protected int $median;

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getScore(): string
    {
        return $this->score;
    }

    /**
     * @param string $score
     */
    public function setScore(string $score): void
    {
        $this->score = $score;
    }

    public function getMedian(): int
    {
        return $this->median;
    }

    /**
     * @param int $median
     */
    public function setMedian(int $median): void
    {
        $this->median = $median;
    }
}
