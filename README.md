# Majority Judgment PHP Library

## Features

- Majority Judgment deliberation from merit profiles
- Score based
- Extensible to get other judgments (usual, central, etc.)


## Usage example

Require it in your own project, using composer:

    composer require mieuxvoter/mj-library-php


```php
use MieuxVoter\MajorityJudgment\MajorityJudgmentDeliberator;
use MieuxVoter\MajorityJudgment\Model\Settings\MajorityJudgmentSettings;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;

$tally = new ArrayPollTally([
    'Proposal A' => [1, 1, 4, 3, 7, 4, 1], // amount of judgments for each grade
    'Proposal B' => [0, 2, 4, 6, 4, 2, 3], // (worst grade to best grade)
]);
$settings = new MajorityJudgmentSettings();
$deliberator = MajorityJudgmentDeliberator();

$result = $deliberator->deliberate($tally, $settings);
// $result is a PollResultInterface

foreach($result->getProposalResults() as $proposalResult) {
    // … Do something
    print($proposalResult->getProposal());
    print($proposalResult->getRank());
}

```


## Interface-oriented

Any object implementing the PollTallyInterface may be used as input.


### Testing

See the tests in `test/`.

    composer install --dev
    vendor/phpunit/phpunit/phpunit -v test


