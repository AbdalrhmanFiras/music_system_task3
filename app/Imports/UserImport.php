<?php

namespace App\Imports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ShouldQueue, ToCollection, WithChunkReading, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $now = Carbon::now();
        $collection = [];
        foreach ($rows as $row) {

            if (! filter_var($row['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $collection[] = [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => ! empty($row['password']) ? Hash::make($row['password']) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        User::upsert($collection, ['email'], ['name', 'password', 'updated_at']);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
