<?php

namespace App\Http\Controllers;

use App\Events\Messaging;
use App\Models\Beauty_center;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$id)
    {
        if($request->header('type') == 'customer')
        {
            try {
                $customer = auth('customer')->userOrFail();
                $customer = Customer::find($customer->id);
                $beautyCenter = Beauty_center::find($id);
                if(!$beautyCenter)
                {
                    return $this->returnError(201, 'Not beautyCenter id');
                }
                $messages = Message::where('customer_id',$customer->id)->where('beauty_center_id',$beautyCenter->id)->orderBy('created_at', 'DESC')
                    ->get();
                $counter = count($messages);
                $skippedNumbers = ($request->numOfPage - 1) * $request->numOfRows;
                $messages = Message::where('customer_id',$customer->id)->where('beauty_center_id',$beautyCenter->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($request->numOfRows)
                    ->get();
                $messages = collect($messages)->map(function($oneMessage)
                {
                    if($oneMessage->content_type != 'text')
                    {
                        $oneMessage->content = asset('/assets/messages/' . $oneMessage->content );
                    }
                    return
                        [
                            "content" => $oneMessage->content,
                            "content_type" => $oneMessage->content_type,
                            "sender_type" => $oneMessage->sender_type,
                            "created_at" => $oneMessage->created_at,
                        ];

                });
                $result = [
                    'messages' => $messages,
                    'length' =>$counter
                ];
                return $this->returnData(['response'], [$result],'Messages Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }else
        {
            try {
                $beautyCenter = auth('beautyCenter')->userOrFail();
                $beautyCenter = Beauty_center::find($beautyCenter->id);
                $customer = Customer::find($id);
                if(!$customer)
                {
                    return $this->returnError(201, 'Not customer id');
                }

                $messages = Message::where('customer_id',$customer->id)->where('beauty_center_id',$beautyCenter->id)->orderBy('created_at', 'DESC')
                    ->get();
                $counter = count($messages);
                $skippedNumbers = ($request->numOfPage - 1) * $request->numOfRows;
                $messages = Message::where('customer_id',$customer->id)->where('beauty_center_id',$beautyCenter->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($request->numOfRows)
                    ->get();
                $messages = collect($messages)->map(function($oneMessage)
                {
                    if($oneMessage->content_type != 'text')
                    {
                        $oneMessage->content = asset('/assets/messages/' . $oneMessage->content );
                    }
                    return
                        [
                            "content" => $oneMessage->content,
                            "content_type" => $oneMessage->content_type,
                            "sender_type" => $oneMessage->sender_type,
                            "created_at" => $oneMessage->created_at,
                        ];

                });
                $result = [
                    'messages' => $messages,
                    'length' =>$counter
                ];
                return $this->returnData(['response'], [$result],'Messages Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    public function store(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'contentMessage' => 'required',
            'type' => 'required|in:customer,beauty_center',
            'content_type' => 'required|string',
            'channel_name' =>'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->type == 'customer')
        {
            try {
                $customer = auth('customer')->userOrFail();
                $customer = Customer::find($customer->id);
                $beautyCenter = Beauty_center::find($id);
                if(!$beautyCenter)
                {
                    return $this->returnError(201, 'Not beautyCenter id');
                }
                if($request->content_type == 'text')
                {
                    $newMessage = new Message;
                    $newMessage->content_type = $request->content_type;
                    $newMessage->content = $request->contentMessage;
                    $newMessage->customer_id = $customer->id;
                    $newMessage->beauty_center_id = $beautyCenter->id;
                    $newMessage->sender_type = 'customer';
                    $newMessage->save();
                    event(new Messaging($newMessage));
                    $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'),env('PUSHER_APP_ID'),['cluster' =>'eu']);
                    $data = $pusher->getPresenceUsers($request->channel_name);
                    $size = count($data->users);
                    if($size <= 1)
                    {
                        $this->NotifyApi($beautyCenter->device_token,$customer->name,$newMessage->content);
                    }


                }else
                {
                    $newMessage = new Message;
                    $newMessage->content_type = $request->content_type;
                    $image = $this->uploadImage($request,'messages','contentMessage');
                    $newMessage->content = $image;
                    $newMessage->customer_id = $customer->id;
                    $newMessage->beauty_center_id = $beautyCenter->id;
                    $newMessage->sender_type = 'customer';
                    $newMessage->save();
                    event(new Messaging($newMessage));
                    $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'),env('PUSHER_APP_ID'),['cluster' =>'eu']);
                    $data = $pusher->getPresenceUsers($request->channel_name);
                    $size = count($data->users);
                    if($size <= 1)
                    {
                        $this->NotifyApi($beautyCenter->device_token,$customer->name,"image");
                    }
                }
                return $this->returnSuccessMessage('Added Successfully',200);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }else
        {
            try {
                $beautyCenter = auth('beautyCenter')->userOrFail();
                $beautyCenter = Beauty_center::find($beautyCenter->id);
                $customer = Customer::find($id);
                if(!$customer)
                {
                    return $this->returnError(201, 'Not customer id');
                }
                if($request->content_type == 'text')
                {
                    $newMessage = new Message;
                    $newMessage->content_type = $request->content_type;
                    $newMessage->content = $request->contentMessage;
                    $newMessage->customer_id = $id;
                    $newMessage->beauty_center_id = $beautyCenter->id;
                    $newMessage->sender_type = 'beauty_center';
                    $newMessage->save();
                    event(new Messaging($newMessage));
                    $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'),env('PUSHER_APP_ID'),['cluster' =>'eu']);
                    $data = $pusher->getPresenceUsers($request->channel_name);
                    $size = count($data->users);
                    if($size <= 1)
                    {
                        $this->NotifyApi($customer->device_token,$beautyCenter->name,$newMessage->content);
                    }
                }else
                {
                    $newMessage = new Message;
                    $newMessage->content_type = $request->content_type;
                    $image = $this->uploadImage($request,'messages','contentMessage');
                    $newMessage->content = $image;
                    $newMessage->customer_id = $id;
                    $newMessage->beauty_center_id = $beautyCenter->id;
                    $newMessage->sender_type = 'beauty_center';
                    $newMessage->save();
                    event(new Messaging($newMessage));
                    $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'),env('PUSHER_APP_ID'),['cluster' =>'eu']);
                    $data = $pusher->getPresenceUsers($request->channel_name);
                    $size = count($data->users);
                    if($size <= 1)
                    {
                        $this->NotifyApi($customer->device_token,$beautyCenter->name,"image");
                    }
                }
                return $this->returnSuccessMessage('Added Successfully',200);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

    }

    public function chatRoom(Request $request)
    {
        if($request->header('type') == 'customer')
        {
            try {
                $customer = auth('customer')->userOrFail();
                $customer = Customer::find($customer->id);
                $messages = Message::where('customer_id',$customer->id)
                    ->orderBy('created_at', 'DESC')
                    ->select('beauty_center_id')
                    ->get();
                $ids = [];
                foreach($messages as $key => $value){
                    $ids [] = $value['beauty_center_id'];
                }
                $messages = array_unique($ids);
                $messages = collect($messages)->map(function($oneRecord) use ($customer)
                {
                    $beauty_center= Beauty_center::find($oneRecord);
                    if($beauty_center->image)
                    {
                        $beauty_center->image = asset('/assets/beauty_centers/' . $beauty_center->image );
                    }

                    $message = Message::where('customer_id',$customer->id)->where('beauty_center_id',$beauty_center->id)->latest()->first();
                    if($message->content_type != 'text')
                    {
                        $message->content = 'image';
                    }
                    $notificationDate = $message->created_at->format('Y-m-d');
                    $time = $message->created_at->format('H:i');
                    $currentDate = Carbon::now();
                    $currentDate = $currentDate->toDateString();
                    if($currentDate == $notificationDate)
                    {
                        $date = 'Today';
                    }else
                    {
                        $date = $notificationDate;
                    }
                    return
                        [
                            "id" => $beauty_center->id,
                            "name" => $beauty_center->name,
                            "image" => $beauty_center->image,
                            "lastMessage" => $message->content,
                            "date" => $date,
                            'time' => $time
                        ];

                });
                $array = [];
                foreach ($messages as $k => $v)
                {
                    $array [] = $v;
                }
                return $this->returnData(['response'], [$array],'Chat Room Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }else
        {
            try {
                $beautyCenter = auth('beautyCenter')->userOrFail();
                $beautyCenter = Beauty_center::find($beautyCenter->id);
                $messages = Message::where('beauty_center_id',$beautyCenter->id)
                    ->orderBy('created_at', 'DESC')
                    ->select('customer_id')
                    ->get();
                $ids = [];
                foreach($messages as $key => $value){
                    $ids [] = $value['customer_id'];
                }
                $messages = array_unique($ids);
                $messages = collect($messages)->map(function($oneRecord) use ($beautyCenter)
                {
                    $customer= Customer::find($oneRecord);
                    $customer->image = asset('/assets/customers/' . $customer->image );
                    $message = Message::where('customer_id',$customer->id)->where('beauty_center_id',$beautyCenter->id)->latest()->first();
                    if($message->content_type != 'text')
                    {
                        $message->content = 'image';
                    }

                    $notificationDate = $message->created_at->format('Y-m-d');
                    $time = $message->created_at->format('H:i');
                    $currentDate = Carbon::now();
                    $currentDate = $currentDate->toDateString();
                    if($currentDate == $notificationDate)
                    {
                        $date = 'Today';
                    }else
                    {
                        $date = $notificationDate;
                    }


                    return
                        [
                            "id" => $customer->id,
                            "name" => $customer->name,
                            "image" => $customer->image,
                            "lastMessage" => $message->content,
                            "date" => $date,
                            'time' => $time
                        ];

                });
                return $this->returnData(['response'], [$messages],'Chat Room Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }
    public function sendNotification()
    {

    }
    public function NotifyApi($firebaseToken,$title,$message)
    {
        $SERVER_API_KEY = "AAAAIEskZSs:APA91bFasctHl5s-FyO0oq4KbnJJ5rV9sp5RJDQ9FpgS0y2XTqdHbswxElSpDmORgun1TUZu7x6gvtlwTloUKvng5t1wbRNwSK9HFwXD1YfM3Ym04aSWlSwoih5TTUMgE8_WPKKffQc6";

        $data = [
            "registration_ids" => [$firebaseToken],
            "notification" => [
                "title" => $title,
                "body" => $message,
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return 1;
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
