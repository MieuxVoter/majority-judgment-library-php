<?php

namespace MieuxVoter\MajorityJudgment;

use MieuxVoter\MajorityJudgment\Model\Tally\GradeTally;
use MieuxVoter\MajorityJudgment\Model\Tally\GradeTallyInterface;
use MieuxVoter\MajorityJudgment\Model\Tally\MirrorPollTally;
use MieuxVoter\MajorityJudgment\Model\Tally\MirrorProposalTally;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTally;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTallyInterface;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTally;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyAnalysis;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyInterface;

/**
 * Helps attribute default judgments to balance the proposals tallies,
 * so that all the tallies hold the same total amount of judgments.
 */
class Balancer
{
    /**
     * Creates and returns a new poll tally with balanced proposal tallies.
     *
     * @param PollTallyInterface $tally IS TO BE DISCARDED (we don't deepcopy)
     * @param int $defaultGradeIndex 0 === "worst" grade
     * @return PollTallyInterface A new object with balanced tallies
     */
    static function balanceUsingStaticDefaultGrade(
        PollTallyInterface $tally,
        int                $defaultGradeIndex = 0,
        int                $amountOfParticipants = -1,
    ): PollTallyInterface
    {
        assert($defaultGradeIndex >= 0, "Default grade must be ≥ than zero.");
        $guessedAmountOfParticipants = self::guessAmountOfParticipants($tally);
        if ($amountOfParticipants <= 0) {
            $amountOfParticipants = $guessedAmountOfParticipants;
        }
        $proposalsTallies = $tally->getProposalsTallies();

        $newProposalsTallies = [];
        foreach ($proposalsTallies as $proposalTally) {
            $newProposalsTallies[] = self::applyStaticDefaultToProposal(
                $proposalTally,
                $amountOfParticipants,
                $defaultGradeIndex,
            );
        }

        return new PollTally(
            $newProposalsTallies,
        );
    }

    protected static function guessAmountOfParticipants(
        PollTallyInterface $tally,
    ): int
    {
        $maxAmountOfParticipants = 0;
        foreach ($tally->getProposalsTallies() as $proposalTally) {
            $currentAmountOfParticipants = 0;
            foreach ($proposalTally->getGradesTallies() as $gradeTally) {
                $currentAmountOfParticipants += $gradeTally;
            }
            $maxAmountOfParticipants = max($maxAmountOfParticipants, $currentAmountOfParticipants);
        }

        return $maxAmountOfParticipants;
    }

    protected static function applyStaticDefaultToProposal(
        ProposalTallyInterface $proposalTally,
        int                    $totalParticipantsAmount,
        int                    $defaultGradeIndex = 0
    ): ProposalTallyInterface
    {
        $gradesTallies = $proposalTally->getGradesTallies();
        $proposalParticipantsAmount = 0;
        foreach ($gradesTallies as $gradeIndex => $gradeTally) {
            $proposalParticipantsAmount += $gradeTally;
        }
        $missingJudgmentsAmount = $totalParticipantsAmount - $proposalParticipantsAmount;
        assert(
            $missingJudgmentsAmount >= 0,
            "A proposal tally should have less judgments than the registered amount of participants."
        );
        $newGradesTallies = [];
        foreach ($gradesTallies as $gradeIndex => $gradeTally) {
            $gradeMissingJudgmentsAmount = 0;
            if ($defaultGradeIndex === $gradeIndex) {
                $gradeMissingJudgmentsAmount = $missingJudgmentsAmount;
            }
            $newGradesTallies[] = $gradeTally + $gradeMissingJudgmentsAmount;
        }
        return new ProposalTally(
            $newGradesTallies
        );
    }

    static function applyMedianDefault(
        PollTallyInterface $tally
    ): PollTallyInterface
    {
        $totalParticipantsAmount = $tally->getParticipantsAmount();

        $newProposalsTallies = [];
        foreach ($tally->getProposalsTallies() as $proposalTally) {
            $analysis = new ProposalTallyAnalysis($proposalTally);
            $newProposalsTallies[] = self::applyStaticDefaultToProposal(
                $proposalTally,
                $totalParticipantsAmount,
                $analysis->getMedianGradeIndex()
            );
        }

        return new PollTally(
            $newProposalsTallies
        );
    }

    static function applyNormalization(
        PollTallyInterface $tally
    ): PollTallyInterface
    {
        $totalParticipantsAmount = $tally->getParticipantsAmount();

        $newProposalsTallies = [];
//        foreach ($tally->getProposalsTallies() as $proposalTally) {
//            $analysis = new ProposalTallyAnalysis($proposalTally);
//            $newProposalsTallies[] = self::applyStaticDefaultToProposal(
//                $proposalTally,
//                $totalParticipantsAmount,
//                $analysis->getMedianGradeIndex()
//            );
//        }

        return new PollTally(
            $newProposalsTallies
        );
    }

}