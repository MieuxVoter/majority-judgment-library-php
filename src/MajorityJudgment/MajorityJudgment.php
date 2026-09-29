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
    // TODO: These could be derived from the data instead of being set arbitrarily like this
    const GRADES_AMOUNT_MAX_DIGITS = 3; // 10e3 = 1000 grades should be more than enough
    const PARTICIPANTS_AMOUNT_MAX_DIGITS = 11; // 10e11 = 10 times the humans on Earth in 2020

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
        $tallies = [];  // working copy of $gradesTallies, mutated by algorithm
        foreach ($gradesTallies as $gradeTally) {
            assert(
                0 <= $gradeTally,
                "Tally is within meaningful range."
            );
            $tallies[] = $gradeTally;
        }

        // II. Analyze the merit profile
        $analysis = new ProposalTallyAnalysis($proposalTally);
        $proposalResult->setAnalysis($analysis);
        $proposalResult->setMedian($analysis->getMedianGrade());

        // III. Compute a lexicographical score (higher is "better")
        $deepMajorityGauge = $analysis->getDeepMajorityGauge();
        $score = "";
        for ($i = 0; $i < $amountOfGrades; $i++) {
            if (0 < $i) {
                $score .= '/';
            }
            $score .= sprintf(
                "%0" . ((string)self::GRADES_AMOUNT_MAX_DIGITS) . "d",
                $deepMajorityGauge[2*$i],
            );
            $score .= '_';
            // Note: the following caps the supported amount of participants.
            // Could be bumped up by deriving the $amountOfDigits from $participantsAmount.
            $amountOfDigits = self::PARTICIPANTS_AMOUNT_MAX_DIGITS;
            $score .= sprintf(
                "%0" . ($amountOfDigits + 1) . "d",
                pow(10, $amountOfDigits) + $deepMajorityGauge[2*$i+1],
            );
        }
        $proposalResult->setScore($score);

        // IV. All is done — except for the rank
        return $proposalResult;
    }
}
