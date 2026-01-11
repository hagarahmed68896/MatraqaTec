<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('type', 'individual')->first() ?? User::factory()->create(['type' => 'individual']);
        $admin = User::where('type', 'admin')->first() ?? User::where('email', 'admin@example.com')->first();

        $complaints = [
            [
                'ticket_number' => 'TICK-' . rand(1000, 9999),
                'user_id' => $user->id,
                'account_type' => 'client',
                'phone' => '0123456789',
                'type' => 'complaint_technician',
                'description' => 'الفني تأخر عن الموعد المحدد ولم يقم بتبليغي',
                'status' => 'resolved',
            ],
            [
                'ticket_number' => 'TICK-' . rand(1000, 9999),
                'user_id' => $user->id,
                'account_type' => 'company',
                'phone' => '0123456789',
                'type' => 'general_inquiry',
                'description' => 'كيف يمكنني تحديث بيانات الشركة؟',
                'status' => 'resolved',
            ],
            [
                'ticket_number' => 'TICK-' . rand(1000, 9999),
                'user_id' => $user->id,
                'account_type' => 'client',
                'phone' => '0123456789',
                'type' => 'complaint_technician',
                'description' => 'هذه شكوى تجريبية قيد المراجعة',
                'status' => 'pending',
            ],
        ];

        foreach ($complaints as $data) {
            $complaint = Complaint::create($data);

            if ($data['status'] == 'resolved') {
                ComplaintAction::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'clarification',
                    'notes' => 'تم التواصل مع الفني وحل المشكلة',
                    'admin_id' => $admin ? $admin->id : 1,
                ]);
                
                ComplaintAction::create([
                    'complaint_id' => $complaint->id,
                    'action_type' => 'refund',
                    'notes' => 'تم استرجاع المبلغ للعميل',
                    'admin_id' => $admin ? $admin->id : 1,
                ]);
            }
        }
    }
}
