<?php

namespace MieuxVoter\MajorityJudgment\Model\Exception;

use Exception;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTallyInterface;
use Throwable;

final class UnbalancedTalliesException extends Exception
{

    public function __construct(
        PollTallyInterface $pollTally,
        int                $code = 0,
        ?Throwable         $previous = null,
    )
    {
        $message = "The poll's proposals' tallies do not all have the same amount of judgments.\n";
        if ( ! empty($pollTally->getProposalsTallies())) {
            $message .= "Here's what we got:\n";
            foreach ($pollTally->getProposalsTallies() as $proposalTally) {
                $message .= "\t";
                $message .= json_encode($proposalTally->getGradesTallies());
                $message .= " ";
                $message .= array_sum($proposalTally->getGradesTallies());
                $message .= " judgments";
                $message .= "\n";
            }
        } else {
            // This exception should never be raised if the poll tally is empty, but safe > sorry
            $message .= "(the poll does not even have proposal tallies — what the fork?)";
        }
        parent::__construct($message, $code, $previous);
    }

}
