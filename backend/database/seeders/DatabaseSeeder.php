<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Enums\EquipmentStatus;
use App\Enums\ReservationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* Create Users */
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('password@123'),
            'role' => 'admin',
        ]);

        $userA = User::create([
            'name' => 'Nguyen Van A',
            'email' => 'usera@company.com',
            'password' => Hash::make('password@123'),
            'role' => 'user',
        ]);

        $userB = User::create([
            'name' => 'Tran Thi B',
            'email' => 'userb@company.com',
            'password' => Hash::make('password@123'),
            'role' => 'user',
        ]);

        /* Create Equipment */
        $eq1 = Equipment::create([
            'name' => 'Dell XPS 15',
            'serial_number' => 'DELL-XPS-9530',
            'category' => 'Laptop',
            'status' => EquipmentStatus::AVAILABLE,
            'assigned_to' => null,
            'due_date' => null,
        ]);

        $eq2 = Equipment::create([
            'name' => 'iPhone 15 Pro',
            'serial_number' => 'APPLE-IPHONE-15PRO',
            'category' => 'Mobile',
            'status' => EquipmentStatus::RESERVED,
            'assigned_to' => $userA->id,
            'due_date' => Carbon::now()->addDays(7)->toDateString(),
        ]);

        $eq3 = Equipment::create([
            'name' => 'MacBook Pro M3',
            'serial_number' => 'APPLE-MBP-M3',
            'category' => 'Laptop',
            'status' => EquipmentStatus::MAINTENANCE,
            'assigned_to' => null,
            'due_date' => null,
        ]);

        $eq4 = Equipment::create([
            'name' => 'iPad Pro M2',
            'serial_number' => 'APPLE-IPAD-M2',
            'category' => 'Tablet',
            'status' => EquipmentStatus::AVAILABLE,
            'assigned_to' => null,
            'due_date' => null,
        ]);

        // Seed 30 more mock equipment for pagination testing
        $categories = ['Laptop', 'Mobile', 'Tablet', 'Monitor', 'Keyboard'];
        $statuses = [EquipmentStatus::AVAILABLE, EquipmentStatus::AVAILABLE, EquipmentStatus::MAINTENANCE];
        for ($i = 1; $i <= 30; $i++) {
            Equipment::create([
                'name' => 'Thiết bị mẫu ' . $i,
                'serial_number' => 'SERIAL-MOCK-' . sprintf('%03d', $i),
                'category' => $categories[array_rand($categories)],
                'status' => $statuses[array_rand($statuses)],
                'assigned_to' => null,
                'due_date' => null,
            ]);
        }

        /* Create Reservations */
        // Approved Reservation for User A
        Reservation::create([
            'equipment_id' => $eq2->id,
            'user_id' => $userA->id,
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'status' => ReservationStatus::APPROVED,
        ]);

        // Pending Reservation for User B
        Reservation::create([
            'equipment_id' => $eq1->id,
            'user_id' => $userB->id,
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(5)->toDateString(),
            'status' => ReservationStatus::PENDING,
        ]);

        // Rejected Reservation for User A
        Reservation::create([
            'equipment_id' => $eq3->id,
            'user_id' => $userA->id,
            'start_date' => Carbon::now()->subDays(5)->toDateString(),
            'end_date' => Carbon::now()->subDays(2)->toDateString(),
            'status' => ReservationStatus::REJECTED,
        ]);
    }
}
