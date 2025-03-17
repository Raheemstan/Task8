<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\Concerns\CreatesApplication;

abstract class TestCase extends BaseTestCase
{

    protected const TEST_SUBJECTS = [
        'Mathematics' => 'MATH101',
        'Physics' => 'PHY101',
        'Chemistry' => 'CHEM101',
        'Biology' => 'BIO101',
        'English' => 'ENG101'
    ];

    protected const TEST_CLASSES = [
        '10A' => ['grade' => '10', 'section' => 'A'],
        '10B' => ['grade' => '10', 'section' => 'B'],
        '11A' => ['grade' => '11', 'section' => 'A'],
        '11B' => ['grade' => '11', 'section' => 'B']
    ];
}
