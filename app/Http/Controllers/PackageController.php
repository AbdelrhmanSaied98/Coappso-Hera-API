<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Package;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        $categories = json_decode($request->category);
        try {
            if(auth('beautyCenter')->user())
            {
                $beautyCenterID = auth('beautyCenter')->user()->id;
            }else
            {
                $beautyCenterID = $id;
            }
            $beautyCenter = Beauty_center::find($beautyCenterID);
            $services = [];
            foreach ($categories as $category)
            {
                if($category == 1)
                {
                    foreach ($beautyCenter->services as $service)
                    {
                        if($service->package)
                        {
                            $services [] = $service->id;
                        }
                    }
                    break;
                }else
                {
                    foreach ($beautyCenter->services as $service)
                    {
                        if($service->category_id == $category)
                        {
                            if($service->package)
                            {
                                $services [] = $service->id;
                            }
                        }
                    }
                }
            }
            if ($request->name && $request->name != "") {
                $servicesAll = Service::where('name', 'LIKE',$request->name.'%')->get();
                foreach ($servicesAll as $service) {;
                    $services [] = $service->id;
                }
            }

            $result = [];
            $counter = 0;
            $data = $request->all();
            foreach ($data as $key => $value) {
                if(is_array($value))
                {
                    if($value[0] == null || $value[1] == null)
                    {
                        continue;
                    }
                }
                if($value == null || $value == "" || $value == 'false')
                {
                    continue;
                }
                $counter++;
            }
            foreach ($services as $id) {
                $cnt = count(array_filter($services, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);
            $services = collect($result)->map(function($oneRecord)
            {
                $service= Service::find($oneRecord);
                $service->image = asset('/assets/services/' . $service->image );
                $names = [];
                $servicePackage = explode(",", $service->package->services);
//                dd($servicePackage);
                foreach ($servicePackage as $packageService)
                {
                    $Service = Service::find($packageService);
                    $names [] = $Service->name;
                }
                return
                    [
                        "id" => $service->id,
                        "name" => $service->name,
                        "image" => $service->image,
                        "price" => $service->price,
                        "description" => $service->description,
                        "is_offer" => $service->is_offer,
                        "duration" => $service->duration,
                        "beauty_center_id" => $service->beauty_center_id,
                        "category_id" => $service->category_id,
                        'Services' => $names,
                        'new_price' => $service->new_price
                    ];

            });
            return $this->returnData(['response'], [$services],'Package Data');
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255|unique:services',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'image' => 'required|file',
            'services' => 'required|string',
            'duration'=> 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $services = json_decode($request->services);
            $beautyCenter = auth('beautyCenter')->userOrFail();
            if($beautyCenter->scheduler_duration == null)
            {
                return $this->returnError(201, 'should enter scheduler duration');
            }
            $beautyCenter = Beauty_center::find($beautyCenter->id);
            $beautyCenter = Beauty_center::find($beautyCenter->id);
            $validator = Validator::make($request->all(), [
                'name'=> Rule::unique('services')->where(function ($query) use($beautyCenter){
                    return $query->where('beauty_center_id', $beautyCenter->id);
                })
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }
            $oldService = Service::find($services[0]);
            $category = $oldService->category_id;
            $newService = new Service;
            foreach ($services as $service)
            {
                $oldService = Service::find($service);
                if(!$oldService || $oldService->beauty_center->id != $beautyCenter->id)
                {
                    return $this->returnError(201, 'Not available Services');
                }
            }
            foreach ($services as $service)
            {
                $oldService = Service::find($service);
                if($category != $oldService->category_id)
                {
                    $category = 1;
                    break;
                }
                if(!$oldService || $oldService->beauty_center->id != $beautyCenter->id)
                {
                    return $this->returnError(201, 'Not available Services');
                }
            }
            $image = $this->uploadImage($request,'services','image');
            $newService->image = $image;
            $newService->name = $request->name;
            $newService->price = $request->price;
            $newService->beauty_center_id = $beautyCenter->id;
            $newService->description = $request->description;
            $newService->category_id = $category;
            $newService->is_offer = '0';
            $newService->duration = $request->duration;
            $newService->durationOffer = '0';
            $newService->new_price = '0';
            $newService->save();
            $newPackage = new Package;
            $newPackage->service_id = $newService->id;
            $serviceString = implode(",", $services);
            $newPackage->services = $serviceString;
            $newPackage->image = "";
            $newPackage->save();
            return $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $newSevice = Service::find($id);
            if($newSevice->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not your Package');
            }
            $newSevice->price = $request->price;
            $newSevice->save();
            return $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $newSevice = Service::find($id);
            if($newSevice->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not your Package');
            }
            $path =  public_path('/assets/services/'.$newSevice->image);
            $image_path = $path;
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            $newSevice->delete();
            return $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function makeOffer(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'new_price' => 'required|numeric',
            'durationOffer' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $newSevice = Service::find($id);
            if(!$newSevice || $newSevice->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not your Service');
            }
            $newSevice->is_offer = '1';
            $newSevice->new_price = $request->new_price;
            $newSevice->durationOffer = $request->durationOffer;
            $newSevice->save();
            return $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function endOffer(Request $request,$id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $newSevice = Service::find($id);
            if(!$newSevice || $newSevice->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not your Service');
            }
            $newSevice->is_offer = '0';
            $newSevice->new_price = '0';
            $newSevice->durationOffer = '0';
            $newSevice->save();
            return $this->returnSuccessMessage('ended Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getOffer(Request $request)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $beautyCenter = Beauty_center::find($beautyCenter->id);
            $services = [];
            foreach ($beautyCenter->services as $service)
            {
                if($service->package)
                {
                    if($service->is_offer == '1')
                    {
                        $services [] = $service->id;
                    }
                }

            }

            if ($request->name && $request->name != "") {
                $servicesAll = Service::where('name', 'LIKE',$request->name.'%')->get();
                foreach ($servicesAll as $service) {
                    $services [] = $service->id;
                }
            }
            $result = [];
            $counter = 1;
            $data = $request->all();
            foreach ($data as $key => $value) {
                if(is_array($value))
                {
                    if($value[0] == null || $value[1] == null)
                    {
                        continue;
                    }
                }
                if($value == null || $value == "" || $value == 'false')
                {
                    continue;
                }
                $counter++;
            }
            foreach ($services as $id) {
                $cnt = count(array_filter($services, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);
            $services = collect($result)->map(function($oneRecord)
            {
                $service= Service::find($oneRecord);
                $service->image = asset('/assets/services/' . $service->image );
                $names = [];
                $servicePackage = explode(",", $service->package->services);
                foreach ($servicePackage as $packageService)
                {
                    $Service = Service::find($packageService);
                    $names [] = $Service->name;
                }
                return
                    [
                        "id" => $service->id,
                        "name" => $service->name,
                        "image" => $service->image,
                        "price" => $service->price,
                        "description" => $service->description,
                        "is_offer" => $service->is_offer,
                        "duration" => $service->duration,
                        "beauty_center_id" => $service->beauty_center_id,
                        "category_id" => $service->category_id,
                        'Services' => $names,
                        'new_price' => $service->new_price
                    ];

            });
            return $this->returnData(['response'], [$services],'Package Data');
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
