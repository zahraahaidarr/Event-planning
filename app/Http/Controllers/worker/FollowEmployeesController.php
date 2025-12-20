<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FollowEmployeesController extends Controller
{
    public function index()
    {
        return view('worker.follow-employees');
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $employees = Employee::query()
            ->whereNotNull('user_id')
            ->where('is_active', 1)
            ->with(['user:id,first_name,last_name,email'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('user', function ($u) use ($q) {
                    $u->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->limit(30)
            ->get();

        $followingIds = $request->user()
            ->followingEmployees()
            ->pluck('users.id')
            ->toArray();

        $payload = $employees->map(function ($emp) use ($followingIds) {
            $u = $emp->user;

            $fullName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));

            return [
                'id' => $u->id,
                'name' => $fullName,
                'email' => $u->email ?? '',
                'is_following' => in_array($u->id, $followingIds, true),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $payload,
        ]);
    }

    public function toggleFollow(Request $request, int $employeeId): JsonResponse
    {
        $worker = $request->user();

        $employee = User::where('role', 'EMPLOYEE')->findOrFail($employeeId);

        $isFollowing = $worker->followingEmployees()->where('users.id', $employee->id)->exists();

        if ($isFollowing) {
            $worker->followingEmployees()->detach($employee->id);
            $nowFollowing = false;
        } else {
            $worker->followingEmployees()->attach($employee->id);
            $nowFollowing = true;
        }

        return response()->json([
            'ok' => true,
            'following' => $nowFollowing,
        ]);
    }
}
