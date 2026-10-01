<?php

namespace MieuxVoter\MajorityJudgment\Model\Result;

use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyAnalysis;

/**
 * An element of the leaderboard of a PollResult.
 *
 * Holds the rank of the proposal after ranking using Majority Judgment.
 * Also holds the original index of the proposal in the input array of tallies.
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
     */
    protected string $score;

    /**
     * Median Grade received by the Proposal.
     */
    protected int $median;

    /**
     * In-depth analysis of the candidate tally.
     */
    protected ProposalTallyAnalysis $analysis;

    /**
     * @see $index
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @see $rank
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @see $score
     */
    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): void
    {
        $this->score = $score;
    }

    /**
     * @return int The index of the median grade of this proposal.
     */
    public function getMedian(): int
    {
        return $this->median;
    }

    public function setMedian(int $median): void
    {
        $this->median = $median;
    }

    public function getAnalysis(): ProposalTallyAnalysis
    {
        return $this->analysis;
    }

    public function setAnalysis(ProposalTallyAnalysis $analysis): void
    {
        $this->analysis = $analysis;
    }
}
