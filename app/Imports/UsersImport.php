<?php

namespace App\Imports;

use App\Models\User;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $organizerId = Auth::user()->organizer_id;

        foreach ($rows as $row) {
            if (empty($row['email']) && empty($row['phone'])) {
                continue;
            }

            $email = trim($row['email'] ?? null);
            $phone = trim($row['phone'] ?? null);

            if (empty($email) && empty($phone)) {
                continue;
            }

            $user = null;
            if (!empty($email)) {
                $user = User::where('email', $email)->first();
            }

            if (!$user && !empty($phone)) {
                $user = User::where('phone', $phone)->first();
            }

            $now = now();

            $userData = [
                'name' => $row['name'] ?? null,
                'address' => $row['address'] ?? null,
                'introduce' => $row['introduce'] ?? null,
                'gender' => isset($row['gender']) ? (bool) $row['gender'] : 0,
                'lang' => $row['lang'] ?? 'vi',
                'updated_at' => $now,
            ];

            if ($user) {
                $user->update($userData);
            } else {
                $password = $row['password'] ?? Str::random(10);

                $userData = array_merge($userData, [
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($password),
                    'role' => RoleUser::CUSTOMER->value,
                    'organizer_id' => $organizerId,
                    'email_verified_at' => !empty($email) ? $now : null,
                    'phone_verified_at' => !empty($phone) ? $now : null,
                    'created_at' => $now,
                ]);

                if (empty($userData['email'])) {
                    unset($userData['email']);
                }
                if (empty($userData['phone'])) {
                    unset($userData['phone']);
                }

                User::create($userData);
            }
        }
    }
}
