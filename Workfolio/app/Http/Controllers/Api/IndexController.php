<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    public function usersignup( Request $request)
    {
        //

        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
                'address' => ['required'],
                'contactno' => ['required', 'min:10'],
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('image', 'public');
            $imageURL = ('storage/' . $imagePath);


            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'user',
                'password' => Hash::make($request->password),
                'total_leaves' => '10',
                'remaining_leaves' => '10',
                'status' => 'Inactive',
                'address' => $request->address,
                'contactno' => $request->contactno,
                'image' => $imageURL ?? null,
            ];
            try {
                $users = User::create($data);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollback();
                echo $e->getMessage();
                $users = null;
            }
            if ($users != null) {
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'you have registered successfully',
                        'users' => null,
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'message' => 'Internal server error',
                        'success' => false,
                        'users' => null,

                    ],
                    500
                );
            }
        }
    }




    public function adminsignup(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'admin',
                'password' => Hash::make($request->password),
                'status' => 'Inactive',

            ];
            try {
                $users = User::create($data);
                DB::commit();
            } catch (\Exception $e) {
                echo $e->getMessage();
                die;
                DB::rollback();
                $users = null;
            }
        }
        if ($users != null) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'you have registetred successfully',
                    'users' => null,

                ],
                200
            );
        } else {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Internal server error',
                    'users' => null,
                ],
                500
            );
        }
    }

    public function loginuser(Request $request)
    {


        $validator = Validator::make(
            $request->all(),
            [

                'email' => ['required', 'email'],
                'password' => ['required'],

            ]
        );


        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }


        $user = User::where('email', $request->email)->first();


        if ($user == null) {
            return response()->json('email could not found', 404);
        } else {
            try {
                $users = User::where('password', Hash::check($request->password, $user->password))->first();

                if (!$users) {

                    $token = $user->createToken('Authtoken')->accessToken;
                    return response()->json([
                        'successs' => true,
                        'message' => 'you have logged in successfully  ',
                        'token' => $token,
                        'users' => $users,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'wrong password',
                    ], 404);
                }
            } catch (\Exception $e) {
                $users = null;
                return response()->json([
                    'success' => false,
                    'message' => 'internal server error',
                ], 500);
            }
        }
    }

    public function loginadmin(Request $request)
    {


        $validator = Validator::make(
            $request->all(),
            [

                'email' => ['required', 'email'],
                'password' => ['required'],

            ]
        );


        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }


        $user = User::where('email', $request->email)->first();


        if ($user == null) {
            return response()->json('email could not found', 404);
        } else {
            try {
                $users = User::where('password', Hash::check($request->password, $user->password))->first();



                if (!$users) {

                    $token = $user->createToken('Authtoken')->accessToken;
                    return response()->json([
                        'successs' => true,
                        'message' => 'you have logged in successfully',
                        'token' => $token,
                        'users' => $users,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'please signup before logged in',
                    ], 404);
                }
            } catch (\Exception $e) {
                $users = null;
                return response()->json([
                    'success' => false,
                    'message' => 'internal server error',
                ], 500);
            }
        }
    }

    public function getUserByToken(Request $request)
    {
        $user = Auth::user();

        if ($user != null) {

            return response()->json([

                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'contactno' => $user->contactno,
                'address' => $user->address,
                'gender' => $user->gender,
                'status' => $user->status,
                'image' => $user->image
            ], 200);
        } else {

            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function change_password(Request $request,  $id)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => ['required', 'min:8'],
            'password' => ['required', 'min:8', 'confirmed',],
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
        $users = User::find($id);
        if ($users == null) {
            return response()->json('user doenot exist', 404);
        } else {
            if (Hash::check($request->old_password, $users->password)) { {
                    DB::beginTransaction();
                    try {
                        $users->password = Hash::make($request->password);
                        $users->save();
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        $users = null;
                    }
                }

                if ($users == null) {
                    return response()->json('internal server error', 404);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'PASSWORD CHANGED SUCCESSFULLY', 200
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'old passowrd doesnot match', 404
                ]);
            }
        }
    }

    public function forgetPassword(Request $request)
    {

try {
            $users = User::where('email', $request->email)->first();
            if ($users != null) {
                $otpcode =  random_int(10, 10000);

                try {
                    DB::beginTransaction();
                    $users->otpcode = $otpcode;
                    $users->save();
                    DB::commit();
                    $data['otpcode'] = $otpcode;
                    $data['email'] = $request->email;
                    $data['title'] = "RESET PASSWORD";
                    $data['body'] = "Enter this otp code to reset your password";
                    Mail::send('email', ['data' => $data], function ($message) use ($data) {
                        $message->from('ibeaconofficial@gmail.com');
                        $message->to($data['email']);
                        $message->subject($data['title']);
                        return response()->json([
                            'success' => true,
                            'data' => null,
                            'message' => 'Mail send successfully',

                        ], 200);
                    });
                } catch (\Exception $e) {
                    DB::rollback();
                    $users = null;
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'user not found'
                ], 400);
            }
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 200,);
        }
    }

    public function newpassword(Request $request)
    {

        $validator = Validator::make(
            $request->all(),

            [
                'otpcode' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'min:8', 'confirmed'],
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $matchThese = ['email' => $request->email, 'otpcode' => $request->otpcode];
        $users = User::where($matchThese)->first();

        //  print_r($users);
        if ($users != null) {
            try {
                DB::beginTransaction();
                $users->password = Hash::make($request->password);
                $users->otpcode = null;
                $users->save();
                DB::commit();
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'password updated successfully'
                    ],
                    200
                );
            } catch (\Exception $e) {
                $users = null;
                DB::rollback();
                // return response()->json($data, 200, $headers);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Inavlid credentials'
            ], 200);
        }
    }

    public function leaves(Request $request)
    {
        $user = Auth::user();
        if ($user->id != null) {
            $user_id = $user->id;
        }
        $validator = Validator::make(
            $request->all(),

            [

                // 'id'=>['required'],
                'date_from' => ['required', 'date'],
                'date_to' => ['required', 'date'],
                'reason' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            DB::beginTransaction();
            $data = [
                // 'id'=>$request->id,
                'reason' => $request->reason,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'user_id' => $user_id,
            ];
        }

        try {
            $leaves = Leave::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            echo $e->getMessage();
            DB::rollback();
            $leaves = null;
        }

        if ($leaves != null) {
            return response()->json([
                'success' => true,
                'message' => 'your request has been forwarded and wait for approval ',
            ], 200,);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 200);
        }
    }

    public function userdetails(string $user_id)
    {
        $users = Emp_attend::where('user_id', $user_id)->first();
        if ($users == null) {
            return response()->json([
                'success' => false,
                'message' => 'The user doest exist',
            ], 404);
        } else {
            $attendance = Emp_attend::select('check_in', 'check_out')->whereBetween('check_in', [now()->startOfWeek(), now()->endOfWeek()])->where('user_id', $user_id)->get();

            // print_r($data);
            $totalTime = 0;
            foreach ($attendance as $att) {
                $checkInTime = Carbon::parse($att->check_in);
                $checkOutTime = Carbon::parse($att->check_out);

                $totalTime += $checkOutTime->diffInMinutes($checkInTime);
            }
            $hours = intdiv($totalTime, 60);
            $remainingMinutes = $totalTime % 60;
            $remainingSeconds =  $remainingMinutes % 60;

            $timeFormat = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

            return response()->json([
                'success' => true,
                'message' => 'Total time:',
                'data' => $timeFormat,

            ], 200);
        }
    }
}
