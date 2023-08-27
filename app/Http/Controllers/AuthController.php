<?php

namespace App\Http\Controllers;

use App\Mail\sendingEmail;
use App\Models\Admin;
use App\Models\Beauty_center;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Manger;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:"customer","beauty_center","manger"'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->type == 'customer')
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
                'phone' => 'required|string|min:9|unique:customers|unique:beauty_centers',
                'email' => 'required|string|email|min:5|max:255|unique:customers|unique:beauty_centers',
                'password' => 'required|string|min:8',
                'address'=>'required|string',
                'device_token'=>'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }
            $newCustomer = new Customer;
            $newCustomer->name = $request->name;
            $newCustomer->phone = $request->phone;
            $newCustomer->email = $request->email;
            $newCustomer->password = Hash::make($request->password);
            $newCustomer->address = $request->address;
            $newCustomer->device_token = $request->device_token;
            $newCustomer->flag = 0;
            $newCustomer->save();
            $credentials = request(['email', 'password']);
            $token = auth('customer')->setTTL(5)->attempt($credentials);
            $tokenRefresh = auth('customer')->setTTL(1440)->attempt($credentials);
            $newCustomer->refresh_token = $tokenRefresh;
            $newCustomer->save();
            return $this->respondWithToken($token,$tokenRefresh,$newCustomer);

        }elseif($request->type == 'beauty_center')
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
                'phone' => 'required|string|min:9|unique:beauty_centers|unique:customers',
                'email' => 'required|string|email|min:5|max:255|unique:beauty_centers|unique:customers',
                'password' => 'required|string|min:8',
                'address'=>'required|string',
                'device_token'=>'required|string',
                'location'=>'required|array',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }
            $beautyCenter = new Beauty_center;
            $beautyCenter->name = $request->name;
            $beautyCenter->phone = $request->phone;
            $beautyCenter->email = $request->email;
            $beautyCenter->password = Hash::make($request->password);
            $beautyCenter->device_token = $request->device_token;
            $beautyCenter->save();
            $newBranch = new Branch;
            $newBranch->name = "main";
            $newBranch->address = $request->address;
            $location = implode(",", $request->location);
            $newBranch->location = $location;

            $newBranch->beauty_center_id = $beautyCenter->id;
            $newBranch->save();
            $credentials = request(['email', 'password']);
            $token = auth('beautyCenter')->setTTL(5)->attempt($credentials);
            $tokenRefresh = auth('beautyCenter')->setTTL(1440)->attempt($credentials);
            $beautyCenter->refresh_token = $tokenRefresh;
            $beautyCenter->save();
            return $this->respondWithToken($token,$tokenRefresh,$beautyCenter);
        }

    }

    public function test()
    {
        $basic  = new \Nexmo\Client\Credentials\Basic('d5a7b4e9', 'HVAXftnCYUCM94qJ');
        $client = new \Nexmo\Client($basic);

        $message = $client->message()->send([
            'to' => '201203650266',
            'from' => 'hera',
            'text' => 'Test from hera app verification code is 9857'
        ]);

        dd('SMS message has been delivered.');

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:"customer","beauty_center","manger"',
            'device_token'=>'required|string',
            'isRemembered'=>'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->type == 'customer')
        {
            $credentials = request(['email', 'password']);
            $user = null;
            if (! $token = auth('customer')->setTTL(5)->attempt($credentials)) {
                if(! $token = auth('customer')->setTTL(5)->attempt(['phone' => $request->email, 'password' => $request->password]))
                {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                $user = Customer::where('phone',$request->email)->first();
            }
            if(! $user)
            {
                $user = Customer::where('email',$request->email)->first();
            }
            if ($request->isRemembered)
            {
                $tokenRefresh = auth('customer')->setTTL(259200)->attempt($credentials);
            }else
            {
                $tokenRefresh = auth('customer')->setTTL(1440)->attempt($credentials);
            }

        }elseif($request->type == 'beauty_center')
        {
            $credentials = request(['email', 'password']);
            $user = null;

            if ( $token = auth('admin')->setTTL(259200)->attempt($credentials)) {
                $user = Admin::where('email',$request->email)->first();
                $user->device_token = $request->device_token;
                $user->save();
                $user->isAdmin = 1;
                return $this->respondWithToken($token,"",$user);
            }
            if (! $token = auth('beautyCenter')->setTTL(5)->attempt($credentials)) {
                if(! $token = auth('beautyCenter')->setTTL(5)->attempt(['phone' => $request->email, 'password' => $request->password]))
                {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                $user = Beauty_center::where('phone',$request->email)->first();
            }
            if(! $user)
            {
                $user = Beauty_center::where('email',$request->email)->first();
            }
            if ($request->isRemembered)
            {
                $tokenRefresh = auth('beautyCenter')->setTTL(259200)->attempt($credentials);
            }else
            {
                $tokenRefresh = auth('beautyCenter')->setTTL(1440)->attempt($credentials);
            }
        }
        if($user->ban_times != 0)
        {
            return $this->returnError(201, 'you have been banned for '.$user->ban_times);
        }
        if($user->isBlocked != 0)
        {
            return $this->returnError(201, 'you have been blocked');
        }
        $user->device_token = $request->device_token;
        $user->refresh_token = $tokenRefresh;
        $user->save();
        $user->isAdmin = 0;
        return $this->respondWithToken($token,$tokenRefresh,$user);
    }

    public function getNewToken(Request $request,$type)
    {
        if ($type == "customer")
        {
            try {
                $customer = auth('customer')->userOrFail();

                $token = auth('customer')->setTTL(5)->login($customer);


                return $this->returnData(['response'], [$token],'Token');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif ($type == "beauty_center")
        {

            try {
                $beautyCenter = auth('beautyCenter')->userOrFail();

                $token = auth('beautyCenter')->setTTL(5)->login($beautyCenter);


                return $this->returnData(['response'], [$token],'Token');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

        }
    }

    public function profile(Request $request,$type,$id)
    {
        if($type == 'customer')
        {
            try {
                $user = Customer::find($id);
                if(!$user)
                {
                    return $this->returnError(201, 'Not a user');
                }
                if($user->image)
                {
                    $user->image = asset('/assets/customers/' . $user->image );
                }
                return $this->returnData(['response'], [$user],'Customer Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }else
        {
            try {
                $user = Beauty_center::find($id);
                if(!$user)
                {
                    return $this->returnError(201, 'Not a user');
                }
                if($user->image)
                {
                    $user->image = asset('/assets/beauty_centers/' . $user->image );
                }
                $products = Product::where('beauty_center_id',$user->id)->latest()->take(4)->get();
                $products = collect($products)->map(function($oneProduct)
                {
                    $oneProduct->image = asset('/assets/products/' . $oneProduct->image );

                    return
                        [
                            "id" => $oneProduct->id,
                            "name" => $oneProduct->name,
                            "image" => $oneProduct->image,
                            "price" => $oneProduct->price,
                        ];

                });
                $media = collect($user->media)->map(function($oneMedia)
                {
                    $oneMedia->file = asset('/assets/media/' . $oneMedia->file );

                    return
                        [
                            'id' => $oneMedia->id,
                            "image" => $oneMedia->file,
                        ];

                });
                $user->beautyCenterMedia = $media;
                $user->products = $products;
                $user->address = $user->branches[0]->address;
                $array =  explode(',',$user->branches[0]->location);
                $user->lat = $array[0];
                $user->long = $array[1];
                $BeatyCenterBranches = collect($user->branches)->map(function($oneProduct)
                {

                    return
                        [
                            "id" => $oneProduct->id,
                            "name" => $oneProduct->name,
                            "address" => $oneProduct->address,
                        ];
                });
                try {
                    $customer = auth('customer')->userOrFail();
                    $user->isFavorite = false;
                    foreach ($customer->favoirtes as $favoirte)
                    {
                        if($favoirte->beauty_center->id == $user->id)
                        {
                            $user->isFavorite = true;
                            break;
                        }
                    }
                } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                    $user->isFavorite = false;
                }
                $user->branchesName = $BeatyCenterBranches;

                $sum = 0;
                $counter = count($user->reviews);
                foreach ($user->reviews as $review)
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
                $user->average = $average;
                $offDaysArray = explode(',',$user->off_day);
                $user->off_day = $offDaysArray;

                $offDatesArray = explode(',',$user->offDates);
                $user->offDates = $offDatesArray;
                unset(
                    $user->branches,
                    $user->media,
                    $user->reviews
                );
                return $this->returnData(['response'], [$user],'Beauty Center Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    public function getNotification(Request $request)
    {
        if($request->header('type') == 'customer')
        {
            try {
                $user = auth('customer')->userOrFail();

                $notifications = Notification::where('user_type','customer')->where('user_id',$user->id)->orderBy('created_at', 'DESC')
                    ->get();
                $counter = count($notifications);
                $skippedNumbers = ($request->numOfPage - 1) * $request->numOfRows;
                $notifications = Notification::where('user_type','customer')->where('user_id',$user->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($request->numOfRows)
                    ->get();
                Notification::where('user_type','customer')->where('user_id',$user->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($request->numOfRows)
                    ->update(['seen' => 1]);
                $notifications = collect($notifications)->map(function($oneNotification)
                {
                    return
                        [
                            "content_type" => $oneNotification->content_type,
                            "notification" => $oneNotification->notification,
                            "content_id" => $oneNotification->content_id,
                            "seen" => $oneNotification->seen,
                            "created_at" => date('g:i A', strtotime($oneNotification->created_at)),

                        ];

                });
                $result = [
                    'notification' => $notifications,
                    'length' =>$counter
                ];
                return $this->returnData(['response'], [$result],'Notifications Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }else
        {
            try {
                $user = auth('beautyCenter')->userOrFail();
                $notifications = Notification::where('user_type','beauty_center')->where('user_id',$user->id)->orderBy('created_at', 'DESC')
                    ->get();
                $counter = count($notifications);
                $skippedNumbers = ($request->numOfPage - 1) * $request->numOfRows;
                $notifications = Notification::where('user_type','beauty_center')->where('user_id',$user->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($request->numOfRows)
                    ->get();
                Notification::where('user_type','beauty_center')->where('user_id',$user->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($request->numOfRows)
                    ->update(['seen' => true]);
                $notifications = collect($notifications)->map(function($oneNotification)
                {
                    return
                        [
                            "content_type" => $oneNotification->content_type,
                            "notification" => $oneNotification->notification,
                            "content_id" => $oneNotification->content_id,
                            "seen" => $oneNotification->seen,
                            "created_at" => date('g:i A', strtotime($oneNotification->created_at)),
                        ];

                });
                $result = [
                    'notification' => $notifications,
                    'length' =>$counter
                ];
                return $this->returnData(['response'], [$result],'Notifications Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {

        if($request->header('type') == 'customer')
        {
            try {
                $user = auth('customer')->userOrFail();
                if($request->device_token == $user->device_token)
                {
                    $user->device_token = "";
                    $user->save();
                }
                auth('customer')->logout();
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif($request->header('type') == 'beauty_center')
        {
            try {
                $user = auth('beautyCenter')->userOrFail();
                if($request->device_token == $user->device_token)
                {
                    $user->device_token = "";
                    $user->save();
                }
                auth('beautyCenter')->logout();
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif($request->header('type') == 'admin')
        {
            try {
                $user = auth('admin')->userOrFail();
                if($request->device_token == $user->device_token)
                {
                    $user->device_token = "";
                    $user->save();
                }
                auth('beautyCenter')->logout();
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function testAuth()
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            return $beautyCenter;
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"customer","beauty_center"',
            'email' => 'required|string|email',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        $type = $request->type;
        if($type == 'customer')
        {
            $user = Customer::where('email', $request->email)->get();
        }elseif ($type == 'beauty_center')
        {
            $user = Beauty_center::where('email', $request->email)->get();
        }
        if (count($user) > 0) {
            $rand = mt_rand(10000, 99999);
            $objDemo = 'Hello There , Your Activation code is '. $rand;
            Mail::to($user[0]->email)->send(new sendingEmail($objDemo));
            $user[0]->update([
                'verification_code' => $rand
            ]);
            return $this->returnSuccessMessage(
                [
                    'msg' => 'Check Your Email And Enter the code'
                ], 200);

        } else {
            return $this->returnError(201, 'Email Not Found');
        }
    }


    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"customer","beauty_center"',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        $type = $request->type;
        if($type == 'customer')
        {
            $user = Customer::where('email', $request->email)->get();
        }elseif ($type == 'beauty_center')
        {
            $user = Beauty_center::where('email', $request->email)->get();
        }

        if (count($user) > 0) {
            $rand = mt_rand(10000, 99999);
            $user[0]->verification_code = $rand;
            $user[0]->password = Hash::make($request->password);
            $user[0]->save();
            return $this->returnSuccessMessage('Updated Successfully',200);
        } else {
            return $this->returnError(201, 'Email Not Found');
        }
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"customer","beauty_center"',
            'email' => 'required|string|email',
            'verification_code'=>'required',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        $type = $request->type;
        if($type == 'customer')
        {
            $user = Customer::where('email', $request->email)->get();
        }elseif ($type == 'beauty_center')
        {
            $user = Beauty_center::where('email', $request->email)->get();
        }

        if (count($user) > 0) {
            if($request->verification_code == $user[0]->verification_code)
            {
                return $this->returnSuccessMessage('Go to Next Step',200);
            }else
            {
                return $this->returnError(201, 'verification code is wrong');
            }
        } else {
            return $this->returnError(201, 'Email Not Found');
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token,$tokenRefresh,$user)
    {

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $tokenRefresh,
            'token_type' => 'Bearer',
            'users'=>$user,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }



    public function getBaniAdam()
    {
        try {
            $customer = auth('customer')->userOrFail();
            if($customer->isBlocked != 0)
            {
                $result = 2;
            }elseif ($customer->ban_times != 0)
            {
                $result = 3;
            }else
            {
                $result = 1;
            }
            return $this->returnData(['response'], [$result],'BaniAdam data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            try {
                $beautyCenter = auth('beautyCenter')->userOrFail();
                if($beautyCenter->isBlocked != 0)
                {
                    $result = 2;
                }elseif ($beautyCenter->ban_times != 0)
                {
                    $result = 3;
                }else
                {
                    $result = 1;
                }
                return $this->returnData(['response'], [$result],'BaniAdam data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
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

    function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->header('type') == 'customer')
        {
            try {
                $user = auth('customer')->userOrFail();
                if($user->image)
                {
                    $path =  public_path('/assets/customers/'.$user->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'customers','image');
                $user->image = $image;
                $user->save();
                return response()->json(['message' => 'Successfully Uploaded']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }else
        {
            try {
                $user = auth('beautyCenter')->userOrFail();
                if($user->image)
                {
                    $path =  public_path('/assets/beauty_centers/'.$user->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'beauty_centers','image');
                $user->image = $image;
                $user->save();
                return response()->json(['message' => 'Successfully Uploaded']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
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
}



