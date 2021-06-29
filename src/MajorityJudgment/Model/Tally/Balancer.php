<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;


class Balancer
{

    /**
     * @param PollTallyInterface $tally IS TO BE DISCARDED (we don't deepcopy)
     * @param int $defaultGradeIndex
     * @return PollTallyInterface A new object with balanced tallies
     */
    static function applyStaticDefault(PollTallyInterface $tally, int $defaultGradeIndex = 0) : PollTallyInterface
    {
        assert($defaultGradeIndex >= 0, "Default grade must be ≥ than zero.");
        $totalParticipantsAmount = $tally->getParticipantsAmount();
        $proposalTallies = $tally->getProposalsTallies();

        foreach ($proposalTallies as $proposalIndex => $proposalTally) {
            /** @var ProposalTallyInterface $proposalTally */
            $gradesTallies = $proposalTally->getGradesTallies();
            $proposalParticipantsAmount = 0;
            foreach ($gradesTallies as $gradeIndex => $gradeTally) {
                /** @var GradeTallyInterface $gradeTally */
                $proposalParticipantsAmount += $gradeTally->getTally();
            }
            $missingJudgmentsAmount = $totalParticipantsAmount - $proposalParticipantsAmount;
            assert(
                $missingJudgmentsAmount >= 0,
                "A proposal tally has more judgments " .
                "than the registered amount of participants."
            );
            if ($missingJudgmentsAmount > 0) {
                $gradesTallies[$defaultGradeIndex] = new GradeTally(
                    $gradesTallies[$defaultGradeIndex]->getGrade(),
                    $gradesTallies[$defaultGradeIndex]->getProposal(),
                    $gradesTallies[$defaultGradeIndex]->getTally() + $missingJudgmentsAmount
                );
                $proposalTallies[$proposalIndex] = new MirrorProposalTally(
                    $proposalTally->getProposal(), $gradesTallies
                );
            }
        }

        return new MirrorPollTally(
            $totalParticipantsAmount,
            $proposalTallies
        );
    }



}