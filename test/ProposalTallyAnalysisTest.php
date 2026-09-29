<?php

namespace MieuxVoter\MajorityJudgment\Model\Tally;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProposalTallyAnalysisTest extends TestCase
{
    public static function provideTestData(): array
    {
        return [
            "Simple example" => [
                'tally' => [1, 2, 3, 4],
                'amountOfGrades' => 4,
                'totalSize' => 10,
                'medianGrade' => 2,
                'medianGroupSize' => 3,
                'adhesionGrade' => 3,
                'adhesionGroupSize' => 4,
                'contestationGrade' => 1,
                'contestationGroupSize' => 3,
                'secondMedianGrade' => 3,
                'secondMedianGroupSize' => 4,
                'secondMedianGroupSign' => 1,
            ],
            "Favor contestation by default" => [
                'tally' => [1, 1, 1, 1],
                'amountOfGrades' => 4,
                'totalSize' => 4,
                'medianGrade' => 1,
                'medianGroupSize' => 1,
                'adhesionGrade' => 2,
                'adhesionGroupSize' => 2,
                'contestationGrade' => 0,
                'contestationGroupSize' => 1,
                'secondMedianGrade' => 2,
                'secondMedianGroupSize' => 2,
                'secondMedianGroupSign' => 1,
            ],
            "Allow empty tally" => [
                'tally' => [0, 0, 0],
                'amountOfGrades' => 3,
                'totalSize' => 0,
                'medianGrade' => 0,
                'medianGroupSize' => 0,
                'adhesionGrade' => 0,
                'adhesionGroupSize' => 0,
                'contestationGrade' => 0,
                'contestationGroupSize' => 0,
                'secondMedianGrade' => 0,
                'secondMedianGroupSize' => 0,
                'secondMedianGroupSign' => 0,
            ],
            "Allow void tally" => [
                'tally' => [],
                'amountOfGrades' => 0,
                'totalSize' => 0,
                'medianGrade' => 0,
                'medianGroupSize' => 0,
                'adhesionGrade' => 0,
                'adhesionGroupSize' => 0,
                'contestationGrade' => 0,
                'contestationGroupSize' => 0,
                'secondMedianGrade' => 0,
                'secondMedianGroupSize' => 0,
                'secondMedianGroupSign' => 0,
            ],
        ];
    }

    #[DataProvider('provideTestData')]
    function testAnalysis(
        array $tally,
        ?int  $amountOfGrades = null,
        ?int  $totalSize = null,
        ?int  $medianGrade = null,
        ?int  $medianGroupSize = null,
        ?int  $adhesionGrade = null,
        ?int  $adhesionGroupSize = null,
        ?int  $contestationGrade = null,
        ?int  $contestationGroupSize = null,
        ?int  $secondMedianGrade = null,
        ?int  $secondMedianGroupSize = null,
        ?int  $secondMedianGroupSign = null,
    )
    {
        $analysis = new ProposalTallyAnalysis(new ProposalTally($tally));

        if ($amountOfGrades !== null) {
            $this->assertEquals($amountOfGrades, $analysis->getAmountOfGrades());
        }
        if ($totalSize !== null) {
            $this->assertEquals($totalSize, $analysis->getTotalSize());
        }
        if ($medianGrade !== null) {
            $this->assertEquals($medianGrade, $analysis->getMedianGrade());
        }
        if ($medianGroupSize !== null) {
            $this->assertEquals($medianGroupSize, $analysis->getMedianGroupSize());
        }
        if ($adhesionGrade !== null) {
            $this->assertEquals($adhesionGrade, $analysis->getAdhesionGrade());
        }
        if ($adhesionGroupSize !== null) {
            $this->assertEquals($adhesionGroupSize, $analysis->getAdhesionGroupSize());
        }
        if ($contestationGrade !== null) {
            $this->assertEquals($contestationGrade, $analysis->getContestationGrade());
        }
        if ($contestationGroupSize !== null) {
            $this->assertEquals($contestationGroupSize, $analysis->getContestationGroupSize());
        }
        if ($secondMedianGrade !== null) {
            $this->assertEquals($secondMedianGrade, $analysis->getSecondMedianGrade());
        }
        if ($secondMedianGroupSize !== null) {
            $this->assertEquals($secondMedianGroupSize, $analysis->getSecondMedianGroupSize());
        }
        if ($secondMedianGroupSign !== null) {
            $this->assertEquals($secondMedianGroupSign, $analysis->getSecondMedianGroupSign());
        }
    }
}
