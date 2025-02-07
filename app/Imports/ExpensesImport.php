<?php

namespace App\Imports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\ToModel;

class ExpensesImport implements ToModel
{
    protected $messGroupId;

    public function __construct($messGroupId) {
        $this->messGroupId = $messGroupId;
    }

    public function model(array $row) {
        return new Expense([
            'mess_group_id' => $this->messGroupId,
            'member_id'     => $row[1], // Match "Paid By" to member ID
            'date'          => $row[0],
            'description'   => $row[2],
            'amount'        => $row[3],
        ]);
    }
}
