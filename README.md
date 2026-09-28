# Majority Judgment PHP Library

[![MIT](https://img.shields.io/github/license/MieuxVoter/majority-judgment-library-php?style=for-the-badge)](./LICENSE)
[![Release](https://img.shields.io/github/v/release/MieuxVoter/majority-judgment-library-php?sort=semver&style=for-the-badge)](https://github.com/MieuxVoter/majority-judgment-library-php/releases)
[![Build Status](https://img.shields.io/github/actions/workflow/status/MieuxVoter/majority-judgment-library-php/build.yml?style=for-the-badge)](https://github.com/MieuxVoter/majority-judgment-library-php/actions)
[![Code Quality](https://img.shields.io/codefactor/grade/github/MieuxVoter/majority-judgment-library-php?style=for-the-badge)](https://www.codefactor.io/repository/github/mieuxvoter/majority-judgment-library-php)
[![Join the Discord chat at https://discord.gg/k9YRuZPSZs](https://img.shields.io/discord/705322981102190593.svg?style=for-the-badge)](https://discord.gg/k9YRuZPSZs)


Rank candidates of Majority Judgment polls.


## Features

- [x] Majority judgment deliberation from merit profiles
- [x] Fast & extensible
- [x] Supports billions of voters
- [x] Supports thousands of candidates
- [x] Test-Driven Development
- [x] No floating-point arithmetic
- [x] Using composer and namespaces


## Installation

Require it in your own project, using composer:

    composer require mieuxvoter/majority-judgment


## Usage example

Let's say you have a poll with three candidates and merit profiles like so:

![Three merit profiles showing the amount of judgments received per grade, per candidate](./docs/merit-example.svg)

You can get the rank of each candidate like so:

```php
use MieuxVoter\MajorityJudgment\MajorityJudgment;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;

$grades = [
    "to reject",
    "insufficient",
    "passable",
    "somewhat good",
    "good",
    "very good",
    "excellent",
];
$proposals = [
    "Arancini",
    "Burger",
    "Chips",
];
$meritProfiles = [
    [1, 1, 4, 3, 7, 4, 1], // for each proposal, tally the
    [2, 2, 5, 5, 5, 0, 2], // amount of judgments for each grade
    [0, 1, 2, 1, 6, 7, 4], // from "worst" grade to "best" grade
];

$pollTally = new ArrayPollTally($meritProfiles);
$mj = new MajorityJudgment();
$result = $mj->deliberate($pollTally);

foreach($result->getProposalResultsRanked() as $proposalResult) {
    // … Do something, for example:
    print(sprintf(
        "#%d %s (%s)\n",
        $proposalResult->getRank(),
        $proposals[$proposalResult->getIndex()],
        $grades[$proposalResult->getMedian()],
    ));
}

// #1 Chips (very good)
// #2 Arancini (good)
// #3 Burger (somewhat good)

```

![Three ranked merit profiles showing the amount of judgments received per grade, per candidate](./docs/merit-example-ranked.svg)


### Unbalanced Tallies

If your tally is unbalanced, because some proposals received more judgments than others,
you will need to balance the tally using one of the provided balancing methods (or your own):

```php

use MieuxVoter\MajorityJudgment\Model\Tally\Balancer;

$tally = Balancer::applyStaticDefault($tally);
// or
$tally = Balancer::applyMedianDefault($tally);
// or (TODO)
//$tally = Balancer::applyNormalization($tally);

```


### Testing

See the tests in `test/`.

    composer install
    vendor/bin/phpunit test

