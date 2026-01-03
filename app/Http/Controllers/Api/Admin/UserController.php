<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function downloadAllBlocked()
    {
        $blockedUsers = User::where('status', 'blocked')->orderBy('type')->orderBy('created_at', 'desc')->get();
        return $this->generateCsv($blockedUsers, "all_blocked_users.csv");
    }

    public function toggleBlock($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['status' => false, 'message' => 'User not found'], 404);

        if ($user->status === 'blocked') {
            $user->status = 'active';
            $user->blocked_at = null;
            $message = 'User unblocked successfully';
        } else {
            $user->status = 'blocked';
            $user->blocked_at = now();
            $message = 'User blocked successfully';
        }
        $user->save();

        return response()->json(['status' => true, 'message' => $message, 'data' => $user]);
    }

public function bulkToggleBlock(Request $request)
{
    $validator = Validator::make($request->all(), [
        'users' => 'required|array',
        'users.*.id' => 'required|exists:users,id',
        'users.*.action' => 'required|in:block,unblock',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
    }

    foreach ($request->users as $item) {
        $status = ($item['action'] === 'block') ? 'blocked' : 'active';
        $blockedAt = ($item['action'] === 'block') ? now() : null;

        User::where('id', $item['id'])->update([
            'status' => $status,
            'blocked_at' => $blockedAt,
        ]);
    }

    return response()->json(['status' => true, 'message' => "Users updated successfully"]);
}

    private function generateCsv($users, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Type', 'Blocked At', 'Created At']); 

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                $user->type,
                $user->blocked_at,
                $user->created_at,
            ]);
        }

        fseek($handle, 0);
        
        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
