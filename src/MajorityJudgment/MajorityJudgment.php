<?php

namespace MieuxVoter\MajorityJudgment;

use MieuxVoter\MajorityJudgment\Model\Exception\UnbalancedTalliesException;
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
     * For a given Poll Tally, this computes a Result and returns it.
     * This is the heart of the Ranking, where the business logic resides.
     *
     * @throws UnbalancedTalliesException
     */
    public function deliberate(
        PollTallyInterface $pollTally,
        bool               $balanceUsingNormalization = false,
    ): PollResultInterface
    {
        if ( ! $balanceUsingNormalization) {
            self::checkTalliesBalance($pollTally);
        }

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
            function (ProposalResult $a, ProposalResult $b) use ($balanceUsingNormalization) {
                return self::compareProposalsResults($b, $a, $balanceUsingNormalization);
            },
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
                    self::compareProposalsResults(
                        $proposalResultsRanked[$i],
                        $proposalResultsRanked[$i - 1],
                        $balanceUsingNormalization,
                    ) === 0  // Wow, we have a *perfect* ex-æquo → same rank
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
     * @throws UnbalancedTalliesException when proposals received different amounts of judgments.
     */
    static protected function checkTalliesBalance(
        PollTallyInterface $pollTally,
    ): void
    {
        $tallies = $pollTally->getProposalsTallies();
        if ( ! empty($tallies)) {
            $expectedAmountOfVoters = array_sum($tallies[0]->getGradesTallies());
            foreach ($tallies as $proposalTally) {
                $actualAmountOfVoters = array_sum($proposalTally->getGradesTallies());
                if ($actualAmountOfVoters !== $expectedAmountOfVoters) {
                    throw new UnbalancedTalliesException($pollTally);
                }
            }
        }
    }

    /**
     * Computes the score of the provided proposal.
     * Does not compute the rank ; this will be done by deliberate().
     *
     * This is a static (context-free) method for (later) easier parallelization.
     */
    static protected function computeProposalResult(
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
        //      Note: we do not use the score for ranking anymore — we use the deep majority gauge
        //      We therefore might remove this score altogether, but it's harmless, so…  Not sure.
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
     * Comparison function for the sort of proposals.
     *
     * It's more expensive than a simple strcmp() on the score, but it supports ad hoc normalization.
     */
    static protected function compareProposalsResults(
        ProposalResult $pa,
        ProposalResult $pb,
        bool           $balanceUsingNormalization = false,
    ): int
    {
        $ma = $pa->getMedian();
        $mb = $pb->getMedian();
        if ($ma < $mb) return -1;
        if ($ma > $mb) return +1;

        $ga = $pa->getAnalysis()->getDeepMajorityGauge();
        $gb = $pb->getAnalysis()->getDeepMajorityGauge();
        $gaugeSize = 2 * ($pa->getAnalysis()->getAmountOfGrades() - 1);
        if ($balanceUsingNormalization) {
            // TBD: divide those by their GCD?  (→ more compute, but marginally better support)
            $ca = $pa->getAnalysis()->getTotalSize();
            $cb = $pb->getAnalysis()->getTotalSize();
            for ($i = 1; $i < $gaugeSize; $i += 2) {
                if ($ga[$i] * $cb < $gb[$i] * $ca) return -1;
                if ($ga[$i] * $cb > $gb[$i] * $ca) return +1;
            }
        } else {
            for ($i = 1; $i < $gaugeSize; $i += 2) {
                if ($ga[$i] < $gb[$i]) return -1;
                if ($ga[$i] > $gb[$i]) return +1;
            }
        }

        return 0;
    }
}
