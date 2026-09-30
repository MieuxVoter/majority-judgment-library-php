<?php

namespace MieuxVoter\MajorityJudgment;

use MieuxVoter\MajorityJudgment\Model\Result\PollResult;
use MieuxVoter\MajorityJudgment\Model\Result\PollResultInterface;
use MieuxVoter\MajorityJudgment\Model\Result\ProposalResult;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTallyInterface;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyAnalysis;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyInterface;

/**
 * Rank proposals using Majority Judgment.
 *
 * Ideally, since this algorithm is parallelizable per proposal,
 * we could support `parallel` if the need arises.
 * See https://www.php.net/manual/fr/intro.parallel.php
 * This would enable us to get support for huge amounts of proposals.
 */
class MajorityJudgment
{
    /**
     * Count the digits in the decimal representation of $n.
     * Works with negative numbers, even though we do not need that feature here.
     */
    static protected function countDigits(int $n): int
    {
        if ($n < 0) return self::countDigits(-$n) + 1;
        return $n !== 0 ? floor(log10($n) + 1) : 1;
    }

    /**
     * For a given Poll Tally, this computes a Result and returns it.
     * This is the heart of the Ranking, where the business logic resides.
     */
    public function deliberate(
        PollTallyInterface $pollTally,
    ): PollResultInterface
    {
        $proposalResults = [];
        $proposalResultsRanked = [];

        // I. Compute the score of each proposal, skip the rank for now
        foreach ($pollTally->getProposalsTallies() as $proposalIndex => $proposalsTally) {
            $scoredProposal = self::computeProposalResult($proposalsTally);
            $scoredProposal->setIndex($proposalIndex);
            $proposalResults[] = $scoredProposal;
            $proposalResultsRanked[] = $scoredProposal;
        }

        // II. Sort the proposals using their score (higher is "better")
        $sortSuccess = usort(
            $proposalResultsRanked,
            function (ProposalResult $a, ProposalResult $b) {
                return strcmp($b->getScore(), $a->getScore());
            }
        );
        assert($sortSuccess, "Sorting by score must succeed!");

        // III. Compute the rank of each proposal
        $rank = 1;  // human-centric value, so starts at 1 ("best" proposal)
        $amountOfProposals = count($proposalResultsRanked);
        for ($i = 0; $i < $amountOfProposals; $i++) {

            if ($i == 0) {
                $proposalResultsRanked[$i]->setRank($rank);
            } else {
                if (
                    $proposalResultsRanked[$i]->getScore()
                    ==  // Wow, we have a *perfect* ex-æquo → same rank
                    $proposalResultsRanked[$i - 1]->getScore()
                ) {
                    $proposalResultsRanked[$i]->setRank(
                        $proposalResultsRanked[$i - 1]->getRank()
                    );
                } else {
                    $proposalResultsRanked[$i]->setRank($rank);
                }
            }

            $rank++;
        }

        // IV. We've got everything we need, time to build the Result
        return new PollResult(
            $proposalResults,
            $proposalResultsRanked,
        );
    }

    /**
     * Computes the score of the provided proposal.
     * Does not compute the rank ; this will be done by deliberate().
     *
     * This is a static (context-free) method for (later) easier parallelization.
     */
    static function computeProposalResult(
        ProposalTallyInterface $proposalTally,
    ): ProposalResult
    {
        $proposalResult = new ProposalResult();

        // I. Collect data and check its sanity
        $gradesTallies = $proposalTally->getGradesTallies();
        $amountOfGrades = count($gradesTallies);

        // II. Analyze the merit profile
        $analysis = new ProposalTallyAnalysis($proposalTally);
        $proposalResult->setAnalysis($analysis);
        $proposalResult->setMedian($analysis->getMedianGrade());

        // III. Compute a lexicographical score (higher is "better")
        $amountOfDigitsForGrades = self::countDigits($amountOfGrades);
        $amountOfDigitsForTallies = self::countDigits($analysis->getTotalSize());
        $deepMajorityGauge = $analysis->getDeepMajorityGauge();
        $score = "";
        for ($i = 0; $i < $amountOfGrades - 1; $i++) {
            if (0 < $i) {
                $score .= '/';
            }
            $score .= sprintf(
                "%0" . ($amountOfDigitsForGrades) . "d",
                $deepMajorityGauge[2 * $i],
            );
            $score .= '_';
            $score .= sprintf(
                "%0" . ($amountOfDigitsForTallies + 1) . "d",
                pow(10, $amountOfDigitsForTallies) + $deepMajorityGauge[2 * $i + 1],
            );
        }
        $proposalResult->setScore($score);

        // IV. All is done — except for the rank
        return $proposalResult;
    }
}
