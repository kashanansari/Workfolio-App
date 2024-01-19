<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\Input;

class UserController extends Controller
{
    // function userDetailCheck()
    // {

    //     $emp_attndn = User::with('getAtndn')->get();

    //     $leave = Leave::with('getleave',)->get();
    //     $approvedLeaves = $leave->where('status', 'approved')->first();

    //     if ($emp_attndn) {

    //         return response([
    //             'success' => True,
    //             'data' => [
    //                 'Emp Check in' => $emp_attndn,
    //                 'Emp on leave' => $approvedLeaves
    //             ],
    //             'msg' => 'data found'
    //         ], 200);
    //     }
    //     return response([
    //         'success' => false,
    //         'data' => null,
    //         'msg' => 'data not found'
    //     ], 404);
    // }
    function userLeave()
    {

        $leave = Leave::with('getleave')->get();
        if ($leave == null) {

            return response([
                'success' => false,
                'data' => null,
                'msg' => "Leave doesn't Exist"
            ]);
        }



        $approvedLeaves = $leave->where('status', 'approved')->all();

        $pendingLeaves = $leave->where('status', 'pending')->all();

        $rejectedLeaves = $leave->where('status', 'reject')->all();

        return response([
            'success' => True,

            'data' => [
                'approved Leaves' => $approvedLeaves,
                'pending Leaves' => $pendingLeaves,
                'rejected Leaves' => $rejectedLeaves
            ],
            'msg' => 'data found'
        ], 200);
    }

    function userCheckIn(Request $request)
    {

        $userAut = auth()->user();
        $userId = User::where('id', $userAut->id)->first();
        $checkInTime = Carbon::now('Asia/Karachi');

        $data = [
            'user_id' => $userId->id,
            'check_in' => $checkInTime,
        ];
        $saveChckIn = Emp_attend::create($data);

        User::where('id', $userAut->id)->update(['status' => 'active']);
        return response()->json([
            'success' => True,
            'data' => null,
            'msg' => 'Check IN successfully',
        ], 200);
    }

    function userCheckOut()
    {
        $userAut = auth()->user();
        $userId = User::where('id', $userAut->id)->first();
        $userAttn = Emp_attend::latest('user_id', $userAut->id)->first();
        $checkOut = Carbon::now('Asia/Karachi');
        $userAttn->where('check_out', null)->update(['check_out' => $checkOut]);
        User::where('id', $userAut->id)->update(['status' => 'inactive']);

        if ($userAttn) {

            $checkInTime = Carbon::parse($userAttn->check_in);
            $checkOutTime = Carbon::parse($userAttn->check_out);

            $total = $checkInTime->diff($checkOutTime);
            $totalDuration = $total->format('%H:%I:%S');
        }


        return response()->json([
            'success' => True,
            'msg' => 'Check Out successfully',
            'Total Duration' => $totalDuration,
        ], 200);
    }

    function leaveApproved($id)
    {
        $userAut = auth()->user();
        // $userId = User::where('id', $userAut->id)->first();

        $userId = User::where('id', $id)->first();
        // $approvedLeaves = Leave::where('user_id', $userId->id)->first();
        if ($userId == null) {
            return response([
                'success' => false,
                'data' => null,
                'msg' => "User doesn't found"
            ]);
        }
        $latestLeave = Leave::where('user_id', $userId->id)->latest()->first();
        $fromDate = Carbon::parse($latestLeave->date_from); // Replace with your "from" date
        $toDate = Carbon::parse($latestLeave->date_to);   // Replace with your "to" date

        $daysDifference = $toDate->diffInDays($fromDate);

        // print_r($updateLeave);
        // die;

        if ($latestLeave ) {
            $latestLeave->status = 'approved';
            $latestLeave->admin_id = $userAut->id;
            $latestLeave->save();

            $updateLeave = $userId->remaining_leaves - $daysDifference;
            User::where('id', $userId->id)->update(['remaining_leaves' => $updateLeave]);



            return response([
                'success' => true,
                'data' => 'data found',
                'msg' => 'Leave approved successfully'
            ]);
        }
        return response([
            'success' => false,
            'data' => null,
            'msg' => 'data not found'
        ]);
    }
    function leaveReject($id)
    {
        $userAut = auth()->user();
        // $userId = User::where('id', $userAut->id)->first();

        $userId = User::where('id', $id)->first();
        // $approvedLeaves = Leave::where('user_id', $userId->id)->first();
        if ($userId == null) {
            return response([
                'success' => false,
                'data' => null,
                'msg' => "User doesn't found"
            ]);
        }
        $latestLeave = Leave::where('user_id', $userId->id)->latest()->first();


        if ($latestLeave) {
            $latestLeave->status = 'reject';
            $latestLeave->admin_id = $userAut->id;
            $latestLeave->save();

            return response([
                'success' => true,
                'data' => $latestLeave,
                'msg' => 'Leave rejected successfully'
            ]);
        }
        return response([
            'success' => false,
            'data' => null,
            'msg' => 'data not found'
        ]);
    }

    function allUsers(Request $request)
    {
        $userId = User::where('role', 'user')->get();
        if ($userId == null) {
            return response([
                'success' => false,
                'data' => $userId,
                'msg' => "User doesn't exist"
            ]);
        }

        $perPage = $request->input('perPage', 10);

        $search = $request->input('search');

        $query = User::query();


        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }
        $users = $query->paginate($perPage);
        return response()->json($users);
    }




    function leaveAndAttendence($id)
    {

        $userId = User::where('id', $id)->first();
        if ($userId == null) {
            return response([
                'success' => false,
                'data' => null,
                'msg' => "User doesn't exist"
            ]);
        }

        $perPage = 15;
        $userAttendence = Emp_attend::where('user_id', $id)->paginate($perPage);
        $userLeave = Leave::where('user_id', $id)->paginate($perPage);
        $user = User::where('id', $id)->get();

        // $totalLeave = User::where('id', $id)->sum('total_leaves');
        $user = User::find($id);


        // if ($user) {
        //     $remainingLeave = $user->remaining_leaves;
        //     // $allowedLeave =  $allowedLeave - $user->remaining_leaves;
        // }


        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'attendence' => $userAttendence,
                'Leaves' => $userLeave

            ],
        ]);
    }


    function todayLeaveOrCheckIn()
    {
        $users = User::get();
        $today = Carbon::now('Asia/Karachi')->format('Y-m-d');
        if ($users) {
            $usersWithAttends = DB::table('users')
                ->where('role', 'user')->where('status', 'active')
                ->join('emp_attends', 'users.id', '=', 'emp_attends.user_id')
                ->select('users.id', 'users.name', 'users.status', 'emp_attends.check_in as $today', 'emp_attends.check_out as $today')
                ->get();

            $UsersWithLeave = DB::table('users')
                ->where('role', 'user')
                ->whereDate('date_from', '<=', $today)
                ->whereDate('date_to', '>=', $today)
                ->join('leaves', 'users.id', '=', 'leaves.user_id')
                ->where('leaves.status', 'approved')
                ->select('users.id', 'users.name', 'users.status', 'leaves.date_to')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'Active Users' => $usersWithAttends,
                    'Users On Leave' => $UsersWithLeave
                ],
                'msg' => 'data found',
            ]);
        }
        return response()->json([
            'success' => false,
            'data' => null,
            'msg' => 'data not found',
        ]);
    }
}
