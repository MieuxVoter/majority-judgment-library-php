<?php

//
// The purpose of this file is to use all the entry-level classes this library has to offer,
// so that phpfilemerger can use it as an entrypoint to bundle all of our files into one.
//
//     php phpfilemerger.php merge --exclude-entry entry/merge.php
//

use MieuxVoter\MajorityJudgment\Balancer;
use MieuxVoter\MajorityJudgment\MajorityJudgment;
use MieuxVoter\MajorityJudgment\Model\Exception\UnbalancedTalliesException;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;

$meritProfiles = [
    [1, 1, 4, 3, 7, 4, 1],
    [2, 2, 5, 5, 5, 0, 2],
    [0, 1, 2, 1, 6, 7, 4],
];

$pollTally = new ArrayPollTally($meritProfiles);
$balancedPollTally = Balancer::balanceUsingStaticDefaultGrade($pollTally);

$mj = new MajorityJudgment();
try {
    $result = $mj->deliberate($balancedPollTally);
} catch (UnbalancedTalliesException $e) {
    die("FAILURE: " . $e->getMessage());
}
