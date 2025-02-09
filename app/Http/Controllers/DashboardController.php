<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    { 
        $maktab = $this->getStudentsByDepartment(1); 
        $kitab = $this->getStudentsByDepartment(2); 
        return success_response([
            "kitab" => $this->getStudentCounts($kitab),
            "maktab" => $this->getStudentCounts($maktab),
        ]);
    } 
    private function getStudentsByDepartment($departmentId)
    {
        return User::where('user_type', 'student')
            ->whereHas('studentRegister', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->get();  
    }
 
    /**
 * Get the counts for various student statuses.
 *
 * @param \Illuminate\Support\Collection $students
 * @return array
 */
    private function getStudentCounts($students)
    {
        return [
            'total_application' => $students->count(),
            'general_fail' => $students->whereHas('admissionProgress', function($q) {
                $q->where('is_passed_age', 0);
            })->count(), 

            'general_pass' => $students->whereHas('admissionProgress', function($q) {
                $q->where('is_passed_age', 1);
            })->count(), 

            'interview_fail' => $students->whereHas('admissionProgress', function($q) {
                $q->where('is_passed_age', 1)->where('is_passed_interview', 0);
            })->count(), 

            'interview_pass' => $students->whereHas('admissionProgress', function($q) {
                $q->where('is_passed_age', 1)->where('is_passed_interview', 1);
            })->count(), 

            'final_fail' => $students->whereHas('admissionProgress', function($q) {
                $q->where('is_passed_age', 1)
                ->where('is_passed_interview', 1)
                ->where('is_passed_trial', 0);
            })->count(), 

            'final_pass' => $students->whereHas('admissionProgress', function($q) {
                $q->where('is_passed_age', 1)
                ->where('is_passed_interview', 1)
                ->where('is_passed_trial', 1);
            })->count(), 
            
        ];
    }


}
