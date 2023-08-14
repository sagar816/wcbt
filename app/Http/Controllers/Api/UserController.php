<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index($flag)
    public function index()
    {
        // p($flag);
        // die;
        //flag -> 1 (Active)
        // Flag -> 0 (All)
        // All Users (Active and Inactive)
        // Active
        $users = User::all();
        // $query = User::select('email', 'name');
        // if ($flag == 1){
        //     $query->where('status', 1);    //to query me humlog condition ko ek tarikese concat kar rahe hai
        // }elseif($flag == 0){
        //     //empty               
        // } else {
        //     return response()->json([
        //         'message' => 'Invalid parameter passed, it can be either 0 or 1',
        //         'status' => 0
        //     ], 400);
        // }
        // $users = $query -> get();
        //abhi user:select - dollar query me pada hai
        if (count($users) > 0) {
            //user exixts
            $response = [
                'message' => count($users). ' users found',
                'status' => 1,
                'data' => $users
            ];
        } else {
            //does'nt exists
            $response = [
                'message' => count($users). ' users found',
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required']
        ]);
        if ($validator->fails()){
            return response()->json($validator->messages(), 400);
        } else {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // 'password' => $request->password,
            ];
            // p($data);
            DB::beginTransaction();
            try {
                $user = User::create($data);  //required create kiya
                DB::commit();
            } catch (\Exception $e) {
                //DB::rollBack();
                p($e->getMessage());
                $user = null;
            }
            if ($user != null){
                //okay
                return response()->json([
                    'message' => 'User registered successfully'
                ], 200);
            } else {
                //
                return response()->json([
                    'message' => 'Internal server error'
                ], 500);
            }
        }
    } 

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user =  User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'User not found',
                'status' => 0
            ];
        } else {
            $response = [
                'message' => 'User Found',
                'status' => 1,
                'data' => $user
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        // p($request->all());
        // die;
        if(is_null($user)){
            //user does not exist
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'User does not exists'
                ],
                404
            );
        } else {
            DB::beginTransaction();
            try{
            $user->name = $request['name'];
            $user->email = $request['email'];
            $user->contact = $request['contact'];
            $user->pincode = $request['pincode'];
            $user->address = $request['address'];
            $user->save();
            DB::commit();
        } catch(\Exception $err){
            DB::rollBack();
            $user = null;
        }

        if(is_null($user)){
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Internal server error',
                    'error_msg' => $err->getMessage(),
                ], 500
            );
        } else {
            return response()->json(
                [
                    'status' => 1,
                    'message' => 'User data Updated Successfully'
                ], 200
            );
        }
                     
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)){
            $response = [
                'message' => "User doesn't exists",
                'status' => 0
            ];
            $respCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user -> delete();
                DB::commit();
                $response = [
                    'message' => "User deleted successfully",
                    'status' => 1
                ];
                $respCode = 200;
            } catch (\Exception $err) {
                DB::rollBack();
                $response = [
                    'message' => "Internal server error",
                    'status' => 0
                ];
                $respCode = 500;
            }

        }
        return response()->json($response, $respCode);
             
    }

public function changePassword(Request $request, $id){
    $user = User::find($id);
    if(is_null($user)){
        //user does not exist
        return response()->json(
            [
                'status' => 0,
                'message' => 'User does not exists'
            ],
            404
        );
    } else {
        //main -> change password code
        if($user->password == $request['old_password']){
            
             if($request['new_password'] == $request['confirm_password']){
                //change
                DB::beginTransaction();
                try{
                    $user->password = $request['new_password'];
                    $user->save();
                    DB::commit();
                }
                catch(\Exception $err){
                        $user = null;
                        DB::rollBack();
                }
                if(is_null($user)){
                    return response()->json(
                        [
                            'status'=>0,
                            'message'=>'Internal server error',
                            'error_msg'=> $err-> getMessage()
                        ],
                        500
                    );
                }else{
                    return response()->json(
                        [
                            'status' => 1,
                            'message' => 'Password updated successfully'
                        ], 200
                    );
                }
                
            
            } else {
                return response()->json(
                    [
                        'status' => 0,
                        'message' => 'New password and confirmed password does not match'
                    ],
                    400
                );
                }
            
               
        } else {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Old password does not match'
                ], 
                400
            );
        }
    }
    }

    public function register(Request $request){
        // echo "<pre>";
        // print_r($request->all());
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => ['required', 'email'],
            'password' => ['min:8', 'confirmed']
        ]);

        $user = User::create($validatedData);
        // echo "<pre>";
        // print_r($user);
        $token = $user->createToken("auth_token")->accessToken;
        return response()->json(
            [
                'token' => $token,
                'user' => $user,
                'message' => 'User created successfully',
                'status' => 1
            ]
            );
    }

    public function login(Request $request){
        $validatedData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $user = User::where(['email' => $validatedData['email'], 'password' => $validatedData['password']])->first();
        $token = $user->createToken("auth_token")->accessToken;
        return response()->json(
            [
                'token' => $token,
                'user' => $user,
                'message' => 'Logged In Succesfully',
                'status' => 1
            ]
            );
        // echo "<pre>";
        // print_r($user);
    }

    public function getUser($id){
        $user = User::find($id);
        if(is_null($user)){
            return response()->json(
            [
                'user' => null,
                'message' => 'User Not Found',
                'status' => 0
            ]
            );
        } else {
            return response()->json(
                [
                    'user' => $user,
                    'message' => 'User Found',
                    'status' => 1
                ]
                );
        }
    }




}


 





