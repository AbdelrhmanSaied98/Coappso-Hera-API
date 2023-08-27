<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
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
            $products = [];
            foreach ($categories as $category)
            {
                if($category == 1)
                {
                    foreach ($beautyCenter->products as $product)
                    {
                        $products [] = $product->id;
                    }
                    break;
                }else
                {
                    foreach ($beautyCenter->products as $product)
                    {
                        if($product->category_id == $category)
                        {
                            $products [] = $product->id;
                        }
                    }
                }
            }
            if ($request->name && $request->name != "") {
                $productAll = Product::where('name', 'LIKE',$request->name.'%')->get();
                foreach ($productAll as $product) {;
                    $products [] = $product->id;
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
            foreach ($products as $id) {
                $cnt = count(array_filter($products, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);
            $products = collect($result)->map(function($oneRecord)
            {
                $product= Product::find($oneRecord);
                $product->image = asset('/assets/products/' . $product->image );

                return
                    [
                        "id" => $product->id,
                        "name" => $product->name,
                        "image" => $product->image,
                        "price" => $product->price,
                        "description" => $product->description,
                        "beauty_center_id" => $product->beauty_center_id,
                        "category_id" => $product->category_id,
                    ];

            });

            return $this->returnData(['response'], [$products],'Products Data');
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
            'name' => 'required|string|min:3|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|file',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $beautyCenter = Beauty_center::find($beautyCenter->id);
            $newProduct = new Product;
            $image = $this->uploadImage($request,'products','image');
            $newProduct->image = $image;
            $newProduct->name = $request->name;
            $newProduct->price = $request->price;
            $newProduct->beauty_center_id = $beautyCenter->id;
            $newProduct->description = $request->description;
            $newProduct->category_id = $request->category_id;
            $newProduct->save();
            return $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
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
            $newProduct = Product::find($id);
            if($newProduct->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not your product');
            }
            $newProduct->price = $request->price;
            $newProduct->save();
            return $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $newProduct = Product::find($id);
            if($newProduct->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not your product');
            }
            $path =  public_path('/assets/products/'.$newProduct->image);
            $image_path = $path;
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            $newProduct->delete();
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

