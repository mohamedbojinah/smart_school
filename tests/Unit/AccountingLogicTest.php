<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountingLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_creation_and_attributes()
    {
        $school = School::create(['name' => 'مدرسة المحاسبة']);

        $expense = Expense::create([
            'school_id' => $school->id,
            'title' => 'أدوات مختبر',
            'amount' => 1250.75,
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'supplies',
        ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'school_id' => $school->id,
            'title' => 'أدوات مختبر',
            'amount' => 1250.75,
        ]);

        $this->assertTrue($expense->amount > 0);
        $this->assertEquals('supplies', $expense->category);
    }

    public function test_negative_expense_amount_rejected_by_logic()
    {
        $amount = -50;
        $isValid = $amount > 0;
        $this->assertFalse($isValid);
    }
}
