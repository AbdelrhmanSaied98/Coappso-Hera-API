<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        try {
            $beautyCenterID = auth('beautyCenter')->userOrFail()->id;
            $beautyCenter = Beauty_center::find($beautyCenterID);
            $newBranch =  Branch::find($id);
            if(! $newBranch || $newBranch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Invalid add employees');
            }
            $data = collect($newBranch->employees)->map(function($oneEmployee)
            {
                $oneEmployee->image = asset('/assets/employees/' . $oneEmployee->image );
                $sum = 0;
                $counter = count($oneEmployee->employee_reviews);
                foreach ($oneEmployee->employee_reviews as $review)
                {
                    $sum += $review->rate;
                }
                if($counter == 0)
                {
                    $average = "0";
                }else
                {
                    $average = $sum / $counter;
                    $average = sprintf("%.1f", $average);
                }
                return
                    [
                        "id" => $oneEmployee->id,
                        "name" => $oneEmployee->name,
                        "image" => $oneEmployee->image,
                        "title" => $oneEmployee->title,
                        "salary" => $oneEmployee->salary,
                        "average" => $average
                    ];
            });
            return $this->returnData(['response'], [$data],'Employees Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required|string',
            'title'=>'required|string',
            'image'=>'required|file',
            'salary' => 'required|numeric',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenterID = auth('beautyCenter')->userOrFail()->id;
            $beautyCenter = Beauty_center::find($beautyCenterID);
            $newBranch =  Branch::find($id);
            if(! $newBranch || $newBranch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Invalid add employees');
            }
            $newEmployee = new Employee;
            $image = $this->uploadImage($request,'employees','image');
            $newEmployee->image = $image;
            $newEmployee->name = $request->name;
            $newEmployee->title = $request->title;
            $newEmployee->branch_id = $newBranch->id;
            $newEmployee->salary = $request->salary;
            $newEmployee->time_from = $request->time_from;
            $newEmployee->time_to = $request->time_to;
            $newEmployee->save();
            return $this->returnSuccessMessage('New Employees Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $validator = Validator::make($request->all(), [
            'new_branch_id'=>'required|exists:branches,id',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenterID = auth('beautyCenter')->userOrFail()->id;
            $beautyCenter = Beauty_center::find($beautyCenterID);
            $employee =  Employee::find($id);
            if(! $employee || $employee->branch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Invalid employee id');
            }
            $newBranch = Branch::find($request->new_branch_id);
            if($newBranch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not Your Branch');
            }
            $employee->branch_id = $request->new_branch_id;
            $employee->save();
            return $this->returnSuccessMessage('Employee moved to new branch Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $beautyCenterID = auth('beautyCenter')->userOrFail()->id;
            $beautyCenter = Beauty_center::find($beautyCenterID);
            $employee =  Employee::find($id);
            if(! $employee || $employee->branch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Invalid employee id');
            }
            $path =  public_path('/assets/employees/'.$employee->image);
            $image_path = $path;
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            $employee->delete();
            return $this->returnSuccessMessage('Employee has deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
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
}
