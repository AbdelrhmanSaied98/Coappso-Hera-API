<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Finance;
use App\Models\Manger;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MangerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $manger = auth('manger')->userOrFail();
            $employees = collect($manger->branch->employees)->map(function($oneRecord)
            {

                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/employees/' . $oneRecord->image);
                }

                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "title" => $oneRecord->title,
                        "salary" => $oneRecord->salary,
                        "time_from" => $oneRecord->time_from,
                        "time_to" => $oneRecord->time_to,
                    ];
            });

            return $this->returnData(['response'], [$employees],'Employees Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addAttendance(Request $request,$id)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"presence","absent"',
            'date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        try {
            $manger = auth('manger')->userOrFail();
            $employee= Employee::find($id);
            if(!$employee || $employee->branch->id != $manger->branch->id)
            {
                return $this->returnError(201, 'Invalid id');
            }
            $oldAttendance = Attendance::where('employee_id',$employee->id)
                ->where('date',$request->date)
                ->first();
            if($oldAttendance)
            {
                return $this->returnError(201, 'already add in this day');
            }

            $newAttendance = new Attendance;
            $newAttendance->type = $request->type;
            $newAttendance->date = $request->date;
            $newAttendance->employee_id = $employee->id;

            if($request->type == 'presence')
            {
                $validator = Validator::make($request->all(), [
                    'time_attendance' => 'required|date_format:H:i',
                    'time_checkout' => 'required|date_format:H:i',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($request->time_attendance > $request->time_checkout)
                {
                    return $this->returnError(201, 'Invalid times');
                }
                $newAttendance->time_attendance = $request->time_attendance;
                $newAttendance->time_checkout = $request->time_checkout;
                $newAttendance->save();

            }else
            {
                $validator = Validator::make($request->all(), [
                    'absent_type' => 'required|in:"paid","unpaid"',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $newAttendance->absent_type = $request->absent_type;
                $newAttendance->save();

                $newVacation = new Vacation;
                $newVacation->date = $request->date;
                $newVacation->type = $request->absent_type;
                $newVacation->employee_id = $employee->id;
                $newVacation->save();
            }
            return $this->returnSuccessMessage('Attendance Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addFinance(Request $request,$id)
    {

        $validator = Validator::make($request->all(), [
            'pay_cut' => 'required|numeric',
            'month' => 'required|in:"January","February","March","April","May","June","July","August","September","October","November","December"',
            'year' => 'required|max:'.date("2030"),
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        try {
            $manger = auth('manger')->userOrFail();
            $employee= Employee::find($id);
            if(!$employee || $employee->branch->id != $manger->branch->id)
            {
                return $this->returnError(201, 'Invalid id');
            }

            $oldFinance = Finance::where('employee_id',$employee->id)
                ->where('month',$request->month)
                ->where('year',$request->year)
                ->first();
            if($oldFinance)
            {
                return $this->returnError(201, 'already add in this month');
            }
            $finalSalary = $employee->salary - $request->pay_cut;
            if($finalSalary <= 0)
            {
                return $this->returnError(201, 'invalid pay-cut');
            }
            $newFinance = new Finance;
            $newFinance->month = $request->month;
            $newFinance->year = $request->year;
            $newFinance->base_salary = $employee->salary;
            $newFinance->pay_cut = $request->pay_cut;
            $newFinance->final_salary = $finalSalary;
            $newFinance->employee_id = $employee->id;
            $newFinance->save();
            return $this->returnSuccessMessage('Salary Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function array_sort_by_column(&$array, $column, $direction = SORT_DESC) {
        $reference_array = array();

        foreach($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    public function uploadImage(Request $request, $folderName,$filename)
    {

        $filename = strval($filename);
        if ($request->hasFile($filename)) {
            $extension = $request->file($filename)->extension();
            $image = time() . '.' . $request->file($filename)->getClientOriginalExtension();
            $request->file($filename)->move(public_path('/assets/'.$folderName), $image);
            return $image;

        }
    }

    public function returnSuccessMessage($msg = '', $errNum = 'S000')
    {
        return [
            'status' => true,
            'msg' => $msg
        ];
    }

    public function returnData($keys, $values, $msg = '')
    {
        $data = [];
        for ($i = 0; $i < count($keys); $i++) {
            $data[$keys[$i]] = $values[$i];
        }

        return response()->json([
            'status' => true,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    public function returnValidationError($code , $validator)
    {
        return $this->returnError($code, $validator->errors()->first());
    }

    public function returnError($errNum, $msg)
    {
        return response([
            'status' => false,
            'code' => $errNum,
            'msg' => $msg
        ], $errNum)
            ->header('Content-Type', 'text/json');
    }

    public function uploadImages(Request $request)
    {
        if ($request->hasFile('image')) {

            $files = $request->file('image');
            foreach ($files as $file) {

                $fileextension = $file->getClientOriginalExtension();


                $filename = $file->getClientOriginalName();
                $file_to_store = time() . '_' . explode('.', $filename)[0] . '_.' . $fileextension;

                $test = $file->move(public_path('assets/media'), $file_to_store);
                if ($test) {
                    $images [] = $file_to_store;
                }
            }
            $images = implode('|', $images);
            return $images;
        }

    }
}
