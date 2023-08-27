<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Media;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Notification;
use App\Models\Customer;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'services' => 'required|string',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'payment_status' => 'required|in:"1","2","3"',
            'employee_id'=>'required|exists:employees,id'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        $validator = Validator::make($request->all(), [
            "time"=>Rule::unique('book')->where(function ($query){
                global $request;
                return $query->where('date', $request->date)
                    ->where('employee_id',$request->employee_id);
            })
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        $servicesArray = json_decode($request->services);
        $services = implode(',',$servicesArray);
        try {
            $customer = auth('customer')->userOrFail();
            $serviceCounter = 0;
            $packageCounter = 0;
            foreach ($servicesArray as $service)
            {
                $newService = Service::find($service);
                if($newService->package)
                {
                    $packageCounter++;
                }else
                {
                    $serviceCounter++;
                }
            }
            if($serviceCounter > 3)
            {
                return $this->returnError(201, 'Service more than 3');
            }
            if($packageCounter > 1)
            {
                return $this->returnError(201, 'Package more than 1');
            }
            if($packageCounter == 1 && $serviceCounter != 0)
            {
                return $this->returnError(201, 'You have 1 Package already');
            }
            $booking = Book::where('date', $request->date)
                ->where('customer_id',$customer->id)
                ->where('attendance_status','0')
                ->get();
            if(count($booking) == 3)
            {
                return $this->returnError(201, 'You have reached to a the limit');
            }
            $employee = Employee::find($request->employee_id);
            $beautyCenter = $employee->branch->beauty_center;
            $booking = Book::where('date', $request->date)
                ->where('customer_id',$customer->id)
                ->where('attendance_status','0')
                ->get();
            foreach ($booking as $book)
            {
                if($book->employee->branch->beauty_center->id == $beautyCenter->id)
                {
                    return $this->returnError(201, 'You have already booked with this beauty center');
                }
            }
            $booking = Book::where('date', $request->date)
                ->where('customer_id',$customer->id)
                ->where('attendance_status','0')
                ->get();
            $timeCarbon = Carbon::parse($request->time);
            $timeCarbon2 = Carbon::parse($request->time);
            $allTime = [];
            $downTime = [];

            $allTime [] = $timeCarbon->format('H:i');
            $downTime [] = $timeCarbon->format('H:i');

            for($i = 1 ; $i <= 2;$i++)
            {
                $timeCarbone = $timeCarbon;
                $timeCarbone2 = $timeCarbon2;
                $allTime [] = $timeCarbon->addMinutes(30)->format('H:i');
                $downTime [] = $timeCarbon2->subMinutes(30)->format('H:i');
                $timeCarbon = $timeCarbone;
                $timeCarbon2 = $timeCarbone2;
            }
            foreach ($booking as $book)
            {
                $time = Carbon::parse($book->time)->format('H:i');
                for ($i = 0;$i < count($allTime);$i++)
                {
                    if($allTime[$i] == $time)
                    {
                        return $this->returnError(201, 'You have an appointment in this time');
                    }
                    if($downTime[$i] == $time)
                    {
                        return $this->returnError(201, 'You have an appointment in this time');
                    }
                }
            }
            $currentDate = Carbon::now();
            $book_date = Carbon::parse($request->date." ".$request->time);

            if($book_date->lte($currentDate))
            {
                return $this->returnError(201, 'time is gone');
            }

            if($beautyCenter->scheduler_duration == null)
            {
                return $this->returnError(201, 'should enter scheduler duration');
            }



            $book = new Book;
            $book->payment_status = $request->payment_status;
            $book->services = $services;
            $book->date = $request->date;
            $book->time = $request->time;
            $book->employee_id = $request->employee_id;
            $book->customer_id = $customer->id;
            $book->attendance_status = "0";
            $book->make_flag = 0;
            $book->save();

            $newNotification = new Notification;
            $newNotification->user_type = 'beauty_center';
            $newNotification->user_id = $book->employee->branch->beauty_center->id;
            $newNotification->content_type = 'book';
            $newNotification->content_id = $book->id;
            $newNotification->seen = 0;
            $time = date('g:i a', strtotime($book->time));
            $newNotification->notification = $customer->name.' booked an appointment on '.$book->date." at ".$time;
            $newNotification->save();


            (new MessageController())->NotifyApi(
                $book->employee->branch->beauty_center->device_token,
                "New Reservation",
                $customer->name.' booked an appointment on '.$book->date." at ".$time
            );

            return $this->returnSuccessMessage('Reservation Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        //
    }
    public function customerPresence(Request $request, $id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $book = Book::find($id);
            if(!$book || $book->employee->branch->beauty_center->id != $beautyCenter->id || $book->attendance_status != '0')
            {
                return $this->returnError(201, 'reservation id invalid');
            }
            $today = Carbon::now();
            $book_date = Carbon::parse($book->date." ".$book->time);
            if($book_date->gt($today))
            {
                return $this->returnError(201, 'booking in not today');
            }
            $book->attendance_status = '1';
            $book->save();
            $customer = Customer::find($book->customer_id);

            $newNotification = new Notification;
            $newNotification->user_type = 'customer';
            $newNotification->user_id = $customer->id;
            $newNotification->content_type = 'book';
            $newNotification->content_id = $book->id;
            $newNotification->seen = 0;
            $newNotification->notification = $beautyCenter->name.' waiting for your feedback and '.$book->employee->name.' too';
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $customer->device_token,
                "Feedback Time",
                $beautyCenter->name.' waiting for your feedback and '.$book->employee->name.' too'
            );

            return $this->returnSuccessMessage('Updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function customerAbsence(Request $request, $id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $book = Book::find($id);
            if(!$book || $book->employee->branch->beauty_center->id != $beautyCenter->id || $book->attendance_status != '0')
            {
                return $this->returnError(201, 'reservation id invalid');
            }

            $today = Carbon::now();
            $book_date = Carbon::parse($book->date." ".$book->time);
            if($book_date->gt($today))
            {
                return $this->returnError(201, 'booking in not today');
            }
            $book->attendance_status = '2';
            $book->save();

            return $this->returnSuccessMessage('Updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function cancelBooking(Request $request, $id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $book = Book::find($id);
            if(!$book || $book->customer->id != $customer->id || $book->attendance_status != '0')
            {
                return $this->returnError(201, 'booking id invalid');
            }
            $today = Carbon::now();
            $book_date = Carbon::parse($book->date." ".$book->time);
            $today->addMinute(60);
            if($book_date->lt($today))
            {
                return $this->returnError(201, 'can cancel now');
            }
            $book->attendance_status = '3';
            $book->save();

            $time = date('g:i a', strtotime($book->time));
            $newNotification = new Notification;
            $newNotification->user_type = 'beauty_center';
            $newNotification->user_id = $book->employee->branch->beauty_center->id;
            $newNotification->content_type = 'book';
            $newNotification->content_id = $book->id;
            $newNotification->seen = 0;
            $newNotification->notification = $customer->name.' has canceled a reservation on '.$book->date." at ".$time;
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $book->employee->branch->beauty_center->device_token,
                "Cancel Reservation",
                $customer->name.' has canceled a reservation on '.$book->date." at ".$time
            );
            return $this->returnSuccessMessage('Canceled Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
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
}
