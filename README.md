# Majority Judgment PHP Library

## Features

- Majority Judgment deliberation from merit profiles
- Score based
- Extensible to get other judgments (usual, central, etc.)


## Usage example

```php

$tally = new Tally\ArrayPollTally([
    'Proposal A' => [1, 1, 4, 3, 7, 4, 1], // amount of judgments for each grade
    'Proposal B' => [0, 2, 4, 6, 4, 2, 3], // (worst grade to best grade)
]);
$options = new Options\MajorityJudgmentOptions();
$deliberator = MajorityJudgmentDeliberator();
$result = $deliberator->deliberate($tally, $options);
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