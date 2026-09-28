<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

/**
 * Helps attribute default judgments to balance the proposals tallies,
 * so that all the tallies hold the same total amount of judgments.
 */
class Balancer
{
    static function guessAmountOfParticipants(
        PollTallyInterface $tally,
    ): int {
        $maxAmountOfParticipants = 0;
        foreach ($tally->getProposalsTallies() as $proposalTally) {
            $currentAmountOfParticipants = 0;
            foreach ($proposalTally->getGradesTallies() as $gradeTally) {
                $currentAmountOfParticipants += $gradeTally->getTally();
            }
            $maxAmountOfParticipants = max($maxAmountOfParticipants, $currentAmountOfParticipants);
        }

        return $maxAmountOfParticipants;
    }

    /**
     * Creates and returns a new poll tally with balanced proposal tallies.
     *
     * @param PollTallyInterface $tally IS TO BE DISCARDED (we don't deepcopy)
     * @param int $defaultGradeIndex 0 === "worst" grade
     * @return PollTallyInterface A new object with balanced tallies
     */
    static function applyStaticDefault(
        PollTallyInterface $tally,
        int                $defaultGradeIndex = 0,
        int                $amountOfParticipants = 0,
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

        return new MirrorPollTally(
            $amountOfParticipants,
            $newProposalsTallies,
        );
    }

    static function applyStaticDefaultToProposal(
        ProposalTallyInterface $proposalTally,
        int                    $totalParticipantsAmount,
        int                    $defaultGradeIndex = 0
    ): ProposalTallyInterface
    {
        $gradesTallies = $proposalTally->getGradesTallies();
        $proposalParticipantsAmount = 0;
        foreach ($gradesTallies as $gradeIndex => $gradeTally) {
            /** @var GradeTallyInterface $gradeTally */
            $proposalParticipantsAmount += $gradeTally->getTally();
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
            $newGradesTallies[] = new GradeTally(
                $gradeTally->getGrade(),
                $gradeTally->getProposal(),
                $gradeTally->getTally() + $gradeMissingJudgmentsAmount
            );
        }
        return new MirrorProposalTally(
            $proposalTally->getProposal(),
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

        return new MirrorPollTally(
            $totalParticipantsAmount,
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

        return new MirrorPollTally(
            $totalParticipantsAmount,
            $newProposalsTallies
        );
    }

}