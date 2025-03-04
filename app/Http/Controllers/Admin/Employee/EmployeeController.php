<?php

namespace App\Http\Controllers\Admin\Employee;
 
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreResource; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{ 
    public function index()
    {
        try {
            $data = User::where('user_type', 'teacher')
                        ->with('role')  
                        ->get();
            
            $result = $data->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image,
                    'role' => $user->role ? $user->role->name : null, 
                ];
            });

            return success_response($result);  
        } catch (\Exception $e) {   
            return error_response($e->getMessage(), 500);  
        }
    }

 
    public function store(EmployeeStoreResource $request)
    {
        DB::beginTransaction();  
        try { 
            if (User::where('email', $request->user_email)->exists()) {
                return error_response(null,400,"Email already exists!");
            }
     
            $profilePicPath = null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            }
     
            User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'password' => Hash::make("12345678"),  
                'user_type' => 'teacher',  
                'profile_image' => $profilePicPath, 
                'role_id' => $request->role_id,  
            ]);   
    
            DB::commit();   
    
            return success_response(null, 'Employee has been created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  
            return error_response($e->getMessage(), 500);   
        }
    }


    public function update($id, Request $request)
    {
        DB::beginTransaction();  
        try {
            $user = User::findOrFail($id);  
 
            if (User::where('email', $request->user_email)->where('id', '!=', $user->id)->exists()) {
                return error_response(null,400,"Email already exists!");
            }
 
            $profilePicPath = $user->profile_image;  
            if ($request->hasFile('profile_image')) { 
                $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            }
 
            $user->update([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'profile_image' => $profilePicPath,
                'role_id' => $request->role_id,  
            ]); 
            DB::commit();   
            return success_response(null, 'Employee details have been updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); 
            return error_response($e->getMessage());  
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();  
        try {
            $user = User::findOrFail($id);   
            $user->delete(); 
            return success_response(null, 'Employee has been deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();   
            return error_response($e->getMessage());  
        }
    }



    
     
}
