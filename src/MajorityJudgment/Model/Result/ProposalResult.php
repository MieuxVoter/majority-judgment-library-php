<?php


namespace MieuxVoter\MajorityJudgment\Model\Result;


/**
 * An element of the leaderboard of a PollResult.
 *
 * Class ProposalResult
 * @package MieuxVoter\MajorityJudgment\Result
 */
class ProposalResult
{

    /**
     * One of the proposals submitted in the PollTally.
     * It may have any type, for convenience.
     *
     * @var mixed $proposal
     */
    protected $proposal;

    /**
     * The amount of judgments received by this proposal on each grade,
     * from 'lowest|worst' grade to 'highest|best' grade.
     *
     * @var array $tally Array of int
     */
    protected $tally;

    /**
     * Rank of the Proposal, in the Result.
     *
     * Two proposals may share the same rank.
     * The "best" proposal will have rank 1.
     * The rank increases continuously.
     *
     * @var int $rank
     */
    protected $rank;


    /**
     * The higher the score, the better this Proposal is considered.
     * It depends on the meaning of the grades, of course.
     * Higher scores means higher grades; and vice-versa.
     * Scores are strings, compared lexicographically.
     *
     * @var string $score
     */
    protected $score;


    /**
     * Median Grade.
     *
     * @var mixed $median
     */
    protected $median;


    /**
     * @return mixed
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * @param mixed $proposal
     */
    public function setProposal($proposal): void
    {
        $this->proposal = $proposal;
    }

    /**
     * @return array
     */
    public function getTally(): array
    {
        return $this->tally;
    }

    /**
     * @param array $tally
     */
    public function setTally(array $tally): void
    {
        $this->tally = $tally;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return string
     */
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

    /**
     * @return mixed
     */
    public function getMedian()
    {
        return $this->median;
    }

    /**
     * @param mixed $median
     */
    public function setMedian($median): void
    {
        $this->median = $median;
    }

}