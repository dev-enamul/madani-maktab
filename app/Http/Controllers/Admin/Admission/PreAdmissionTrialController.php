<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\PreAdmissionTrial;
use App\Models\StudentNote;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\PhoneMessageService;
use Illuminate\Support\Facades\Auth;

class PreAdmissionTrialController extends Controller
{ 
    public function schedule(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'candidate_id'  => 'required|exists:users,id',
            'date'          => ['required', 'date', 'after:now'], 
            'time'          => ['nullable', 'date_format:H:i'],
            'message'         => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        // DB::beginTransaction();
        try {  
            $messageService = new PhoneMessageService; 

            $message = $request->message; 
            $user = User::find($request->candidate_id);
            $requested_at = Carbon::createFromFormat(
                'Y-m-d H:i',
                $request->date . ' ' . ($request->time ?? '00:00')
            ); 
    
            $progress = AdmissionProgressStatus::where('user_id', $request->candidate_id)->first();
            if (!$progress) {
                return error_response('প্রার্থী পাওয়া যায়নি', 404);   
            } 

            $progress->is_invited_for_trial = true;
            $progress->save();   
            PreAdmissionTrial::updateOrCreate(
                ['candidate_id' => $request->candidate_id],
                [
                    'requested_at' => $requested_at,
                    'notes'     => $message,
                ]
            );  
            $messageService->sendMessage($user->phone, $message);  
            // DB::commit();
            return success_response(null, "ট্রায়াল রিকোয়েস্ট সফলভাবে পাঠানো হয়েছে");  
        } catch (\Exception $e) {
            // DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    }


    public function attend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:users,id',
            'date'         => ['required', 'date', 'after:now'],
            'time'         => ['required', 'date_format:H:i'],
            'notes'         => 'nullable|string|max:500',
        ]);
 
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        try { 
            $attended_at = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time); 
            $trial = PreAdmissionTrial::where('candidate_id', $request->candidate_id)->first(); 
            if (!$trial) {
                return error_response('No trial record found for this candidate.', 404);
            } 
            $trial->update([
                'attended_at' => $attended_at,
                'status'      => 'attended',
                'notes'        => $request->notes,
            ]);   
            return success_response(null,"The student has successfully attended the trial session.");
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

    public function result(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:pre_admission_trials,candidate_id',
            'message'        => 'nullable|string|max:1000',
            'result'       => 'required|boolean',
        ]);
 
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }  

        // DB::beginTransaction(); 
        try { 
            $messageService = new PhoneMessageService; 
            $progress = AdmissionProgressStatus::where('user_id', $request->candidate_id)->first();
            if (!$progress) {
                return error_response('Candidate not found in admission progress status.', 404);
            }

            $trial = PreAdmissionTrial::where('candidate_id', $request->candidate_id)->first();
            if (!$trial) {
                return error_response('No trial record found for this candidate.', 404);
            } 

            $message = $request->message; 
            $progress->is_passed_trial = $request->result;
            $progress->save(); 
 
            $trial->status = 'completed';
            $trial->result = $request->result;
            $trial->notes =  $message;
            $trial->save();

            $user = User::find($request->candidate_id);
            $messageService->sendMessage($user->phone, $message);   
            
            // DB::commit();  
            return success_response(null, "The trial session result has been successfully updated."); 
        } catch (Exception $e) {
            // DB::rollBack(); 
            return error_response('An error occurred while processing the request: ' . $e->getMessage(), 500);
        }
    }



}
