# Majority Judgment PHP Library

Deliberate majority judgment polls ⚖.


## Features

- Majority judgment deliberation from merit profiles
- Score based, efficiency should scale well (algo is parallelizable)
- Interface-oriented, test-driven code
- Extensible to get other judgments (usual, central, etc.)
- Made by [MieuxVoter](https://mieuxvoter.fr)'s volunteers


## Usage example

Require it in your own project, using composer:

    composer require mieuxvoter/majority-judgment

Use it:

```php
use MieuxVoter\MajorityJudgment\MajorityJudgmentDeliberator;
use MieuxVoter\MajorityJudgment\Model\Settings\MajorityJudgmentSettings;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;

$tally = new ArrayPollTally([
    'Proposal A' => [1, 1, 4, 3, 7, 4, 1], // amount of judgments for each grade
    'Proposal B' => [0, 2, 4, 6, 4, 2, 3], // (worst grade to best grade)
]);

$deliberator = new MajorityJudgmentDeliberator();

$result = $deliberator->deliberate($tally);
// $result is a PollResultInterface

foreach($result->getProposalResults() as $proposalResult) {
    // … Do something
    print($proposalResult->getProposal());
    print($proposalResult->getRank());
}

```


### Unbalanced Tallies

If your tally is unbalanced, because some proposals received more judgments than others,
you will need to balance the tally using one of the provided balancing methods (or your own):

```php

use MieuxVoter\MajorityJudgment\Model\Tally\Balancer;

$tally = Balancer::applyStaticDefault($tally);
// or
$tally = Balancer::applyMedianDefault($tally);
// or
$tally = Balancer::applyNormalization($tally);

```


## Interface-oriented

Any object implementing `PollTallyInterface` may be used as input.


### Testing

See the tests in `test/`.

    composer install --dev
    vendor/phpunit/phpunit/phpunit -v test


