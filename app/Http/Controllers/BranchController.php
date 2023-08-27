<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$id)
    {
        $beauty_center = Beauty_center::find($id);
        if(!$beauty_center)
        {
            return $this->returnError(201, 'Not a user');
        }
        $branches = collect($beauty_center->branches)->map(function($oneBranch)
        {
            $array =  explode(',',$oneBranch->location);
            return
                [
                    "id" => $oneBranch->id,
                    "name" => $oneBranch->name,
                    "address" => $oneBranch->address,
                    "lat" => $array[0],
                    "long" => $array[1],
                ];

        });
        return $this->returnData(['response'], [$branches],'Branches Data');
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address'=>'required|string',
            'location'=>'required|array',
            'nameEmployee'=>'required|string',
            'name'=>'required|string',
            'title'=>'required|string',
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

            $validator = Validator::make($request->all(), [
                "name"=>Rule::unique('branches')->where(function ($query) use($beautyCenter){
                    return $query->where('beauty_center_id',$beautyCenter->id);
                })
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            if(sizeof($beautyCenter->branches[0]->employees) == 0)
            {
                return $this->returnError(201, 'Must the finish main branch employees');
            }
            $newBranch = new Branch;
            $newBranch->name = $request->name;
            $newBranch->beauty_center_id = $beautyCenter->id;
            $location = implode(",", $request->location);
            $newBranch->location = $location;
            $newBranch->address = $request->address;
            $newBranch->save();

            $newEmployee = new Employee;
            $image = $this->uploadImage($request,'employees','image');
            $newEmployee->image = $image;
            $newEmployee->name = $request->nameEmployee;
            $newEmployee->title = $request->title;
            $newEmployee->salary = $request->salary;
            $newEmployee->time_from = $request->time_from;
            $newEmployee->time_to = $request->time_to;
            $newEmployee->branch_id = $newBranch->id;
            $newEmployee->save();
            return $this->returnSuccessMessage('New Branch Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit(Branch $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Branch $branch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch $branch)
    {
        //
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
