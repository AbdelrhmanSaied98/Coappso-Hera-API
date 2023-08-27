<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Favoirte;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoirteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $customer = auth('customer')->userOrFail();
            $beauty_centers = collect($customer->favoirtes)->map(function($oneFavorite)
            {
                if($oneFavorite->beauty_center->image)
                {
                    $image = asset('/assets/beauty_centers/' . $oneFavorite->beauty_center->image );
                }else
                {
                    $image = $oneFavorite->beauty_center->image;
                }
                return
                    [
                        "id" => $oneFavorite->beauty_center->id,
                        "name" => $oneFavorite->beauty_center->name,
                        "image" => $image,
                    ];

            });
            return $this->returnData(['response'], [$beauty_centers],'Favorite Beauty Center Data');
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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $beautyCenter = Beauty_center::find($id);
            $favorite = Favoirte::where('customer_id',$customer->id)
                ->where('beauty_center_id',$beautyCenter->id)
                ->first();
            if(!$beautyCenter || $favorite)
            {
                return $this->returnError(201, 'invalid !');
            }
            $newFavorite = new Favoirte;
            $newFavorite->customer_id = $customer->id;
            $newFavorite->beauty_center_id = $beautyCenter->id;
            $newFavorite->save();
            return $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Favoirte  $favoirte
     * @return \Illuminate\Http\Response
     */
    public function show(Favoirte $favoirte)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Favoirte  $favoirte
     * @return \Illuminate\Http\Response
     */
    public function edit(Favoirte $favoirte)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Favoirte  $favoirte
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Favoirte $favoirte)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Favoirte  $favoirte
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $beautyCenter = Beauty_center::find($id);
            if(!$beautyCenter)
            {
                return $this->returnError(201, 'Not exists !');
            }
            $newFavorite = Favoirte::where('customer_id',$customer->id)->where('beauty_center_id',$beautyCenter->id)->first();
            $newFavorite->delete();
            return $this->returnSuccessMessage('Deleted Successfully',200);
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
