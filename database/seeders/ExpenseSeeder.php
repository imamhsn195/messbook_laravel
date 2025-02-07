<?php
namespace Database\Seeders;

use App\Models\MessGroup;
use App\Models\Member;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the mess group with group_id = 1 using the MessGroup model
        $messGroup = MessGroup::find(1);

        if ($messGroup) {
            // Predefined expenses
            $expenses = [
                [
                    'date' => '2025-02-01',
                    'paid_by' => Member::whereName('Iqbal')->first()->id,
                    'description' => 'তাজা মুরগি',
                    'amount' => 34.00,
                ],
                [
                    'date' => '2025-02-01',
                    'paid_by' => Member::whereName('Iqbal')->first()->id,
                    'description' => 'ছাগলের সিনার মাংস',
                    'amount' => 10.75,
                ],
                [
                    'date' => '2025-02-01',
                    'paid_by' => Member::whereName('Iqbal')->first()->id,
                    'description' => 'চিংড়ি, তেলাপিয়া, চুড়ি মাছ, বাধাকপি, লবন, সবজি, ডাল, হলুদ গুড়া, বেগুন, মরিচ গুড়া, বরবিটি',
                    'amount' => 197.50,
                ],
                [
                    'date' => '2025-02-02',
                    'paid_by' => Member::whereName('Siraj')->first()->id,
                    'description' => 'ধনেপাতা, পেয়াহজ, আদা, টমেটো, তেল',
                    'amount' => 63.00,
                ],
                [
                    'date' => '2025-02-02',
                    'paid_by' => Member::whereName('Siraj')->first()->id,
                    'description' => 'চাউল ৪০ কেজি',
                    'amount' => 126.00,
                ],
                [
                    'date' => '2025-02-02',
                    'paid_by' => Member::whereName('Jafar')->first()->id,
                    'description' => 'ডাল, সবজি',
                    'amount' => 6.50,
                ],
                [
                    'date' => '2025-02-04',
                    'paid_by' => Member::whereName('Kamrul Hasan')->first()->id,
                    'description' => 'চিংড়ি',
                    'amount' => 23.50,
                ],
            ];

            // Insert predefined expenses for group_id = 1
            foreach ($expenses as $expense) {
                Expense::create([
                    'mess_group_id' => $messGroup->id,
                    'member_id' => $expense['paid_by'],
                    'date' => $expense['date'],
                    'description' => $expense['description'],
                    'amount' => $expense['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
