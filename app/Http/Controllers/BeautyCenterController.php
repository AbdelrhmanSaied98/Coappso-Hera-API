<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Book;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Employee_review;
use App\Models\Media;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BeautyCenterController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }

    function addToMedia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $beautyCenter = Beauty_center::find($beautyCenter->id);

            $images = $this->uploadImages($request);
            $arr = explode('|',$images);
            foreach ($arr as $ar){
                Media::create([
                    'beauty_center_id'=>$beautyCenter->id,
                    'file'=>$ar,
                ]);
            }
            return $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    function removeMedia(Request $request,$id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $newMedia = Media::find($id);
            if(!$newMedia || $newMedia->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'invalid');
            }
            $path =  public_path('/assets/media/'.$newMedia->file);
            $image_path = $path;
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            $newMedia->delete();
            return $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function update(Request $request)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $beautyCenter = Beauty_center::find($beautyCenter->id);
            if($request->time_to)
            {
                $validator = Validator::make($request->all(), [
                    'time_to' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beautyCenter->time_to = $request->time_to;
                $beautyCenter->save();
            }
            if($request->time_from)
            {
                $validator = Validator::make($request->all(), [
                    'time_from' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beautyCenter->time_from = $request->time_from;
                $beautyCenter->save();
            }
            if($request->off_day)
            {
                $validator = Validator::make($request->all(), [
                    'off_day' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $off_dayArray = json_decode($request->off_day);
                $off_days = implode(',',$off_dayArray);
                $beautyCenter->off_day = $off_days;
                $beautyCenter->save();
            }
            if($request->scheduler_duration)
            {
                $validator = Validator::make($request->all(), [
                    'scheduler_duration' => 'numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beautyCenter->scheduler_duration = $request->scheduler_duration;
                $beautyCenter->save();
            }
            if($request->phone)
            {
                $validator = Validator::make($request->all(), [
                    'phone' => 'string|min:9|unique:beauty_centers',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beautyCenter->phone = $request->phone;
                $beautyCenter->save();
            }
            return $this->returnSuccessMessage('Updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    public function offDates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'off_dates' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $off_datesArray = json_decode($request->off_dates);
            $off_dates = implode(',',$off_datesArray);

            foreach ($off_datesArray as $oneDate)
            {
                foreach ($beautyCenter->branches as $branch)
                {
                    foreach ($branch->employees as $employee)
                    {
                        foreach ($employee->book_servies as $booking)
                        {
                            if($booking->date == $oneDate)
                            {
                                $booking->attendance_status = '4';
                                $booking->save();
                                $time = date('g:i a', strtotime($booking->time));
                                $newNotification = new Notification;
                                $newNotification->user_type = 'customer';
                                $newNotification->user_id = $booking->customer->id;
                                $newNotification->content_type = 'book';
                                $newNotification->content_id = $booking->id;
                                $newNotification->seen = 0;
                                $newNotification->notification = $beautyCenter->name.' has canceled a reservation on '.$booking->date." at ".$time;
                                $newNotification->save();

                                (new MessageController())->NotifyApi(
                                    $booking->customer->device_token,
                                    "Cancel Reservation",
                                    $beautyCenter->name.' has canceled a reservation on '.$booking->date." at ".$time
                                );
                            }
                        }
                    }
                }
            }
            $beautyCenter->offDates = $off_dates;
            $beautyCenter->save();
            return $this->returnSuccessMessage('Updated Successfully',200);

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    function getReservation(Request $request,$branch_id,$status)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $beautyCenter = Beauty_center::find($beautyCenter->id);

            $branch = Branch::find($branch_id);
            if(!$branch)
            {
                return $this->returnError(201, 'Not branch id');
            }
            $currentData = [];
            $history = [];
            $today = [];
            foreach ($branch->employees as $employee)
            {
                foreach ($employee->book_servies as $book_service)
                {
                    $notificationDate = $book_service->date;
                    $currentDate = Carbon::now();
                    $currentDate = $currentDate->toDateString();
                    if($currentDate == $notificationDate)
                    {
                        $today [] = $book_service->id;
                    }elseif($currentDate <= $notificationDate)
                    {
                        $currentData [] = $book_service->id;
                    }else
                    {
                        $history [] = $book_service->id;
                    }

                }
            }

            if($status == '1')
            {
                $isWeek = false;
                $isMonth = false;
                $month = [];
                $week = [];
                if($request->book_id && $request->book_id !="")
                {
                    $isExists = false;
                    foreach ($history as $item)
                    {
                        if($item == $request->book_id)
                        {
                            $isExists = true;
                            break;
                        }
                    }

                    if ($isExists)
                    {
                        $history = [$request->book_id];
                    }else
                    {
                        $customer = Customer::where('phone',$request->book_id)->first();
                        if(!$customer)
                        {
                            $history = [];
                        }else
                        {
                            $allBookWithPhone = [];
                            $booking = Book::where('customer_id',$customer->id)->select('id')->get();
                            foreach ($booking as $book)
                            {
                                foreach ($history as $item)
                                {
                                    if($book->id == $item)
                                    {
                                        $allBookWithPhone [] = $book->id;
                                        break;
                                    }
                                }
                            }
                            $history = $allBookWithPhone;
                        }

                    }
                }
                if($request->this_week && $request->this_week !="")
                {
                    $isWeek = true;
                    foreach ($history as $item)
                    {
                        $book = Book::find($item);
                        $date = Carbon::parse($book->date);
                        $startWeek = Carbon::now();
                        $startWeek = $startWeek->startOfWeek(Carbon::SATURDAY);
                        if($date->gte($startWeek))
                        {
                            $week [] = $book->id;
                        }
                    }
                }
                if($request->this_month && $request->this_month !="")
                {
                    $isMonth = true;
                    foreach ($history as $item)
                    {
                        $book = Book::find($item);
                        $date = Carbon::parse($book->date);
                        $startMonth = Carbon::now();
                        $startMonth = $startMonth->startOfMonth();
                        if($date->gte($startMonth))
                        {
                            $month [] = $book->id;
                        }
                    }
                }
                if($isWeek)
                {
                    $history = $week;
                }elseif ($isMonth)
                {
                    $history = $month;
                }
                $reservations = collect($history)->map(function($oneRecord)
                {
                    $book = Book::find($oneRecord);
                    $names = [];
                    $servicePackage = explode(",", $book->services);
                    foreach ($servicePackage as $packageService)
                    {
                        $Service = Service::find($packageService);
                        $names [] = $Service->name;
                    }
//                    $startWeek = Carbon::parse($book->date);
//                    $startWeek = $startWeek->startOfWeek(Carbon::SATURDAY);
//
//                    $endWeek = Carbon::parse($book->date);
//                    $endWeek = $endWeek->endOfWeek(Carbon::FRIDAY);
                    return
                        [
                            "id" => $book->id,
                            "customer_name" => $book->customer->name,
                            "time" => $book->time,
                            "date" => $book->date,
                            "employee_name" => $book->employee->name,
                            "services_name" => $names,
                            "attendance_status" => $book->attendance_status,
                            "make_flag" => $book->make_flag,
                            "phone" => $book->customer->phone,
                        ];

                });
            }elseif($status == '2')
            {
                $isWeek = false;
                $isMonth = false;
                $month = [];
                $week = [];

                if($request->book_id && $request->book_id !="")
                {
                    $isExists = false;
                    foreach ($currentData as $item)
                    {
                        if($item == $request->book_id)
                        {
                            $isExists = true;
                            break;
                        }
                    }

                    if ($isExists)
                    {
                        $currentData = [$request->book_id];
                    }else
                    {
                        $customer = Customer::where('phone',$request->book_id)->first();
                        if(!$customer)
                        {
                            $currentData = [];
                        }else
                        {
                            $allBookWithPhone = [];
                            $booking = Book::where('customer_id',$customer->id)->select('id')->get();
                            foreach ($booking as $book)
                            {
                                foreach ($currentData as $item)
                                {
                                    if($book->id == $item)
                                    {
                                        $allBookWithPhone [] = $book->id;
                                        break;
                                    }
                                }
                            }
                            $currentData = $allBookWithPhone;
                        }

                    }
                }

                if($request->this_week && $request->this_week !="")
                {
                    $isWeek = true;
                    foreach ($currentData as $item)
                    {
                        $book = Book::find($item);
                        $date = Carbon::parse($book->date);
                        $endWeek = Carbon::now();
                        $endWeek = $endWeek->endOfWeek(Carbon::FRIDAY);
                        if($date->lte($endWeek))
                        {
                            $week [] = $book->id;
                        }
                    }
                }
                if($request->this_month && $request->this_month !="")
                {
                    $isMonth = true;
                    foreach ($currentData as $item)
                    {
                        $book = Book::find($item);
                        $date = Carbon::parse($book->date);
                        $endMonth = Carbon::now();
                        $endMonth = $endMonth->endOfMonth();
                        if($date->lte($endMonth))
                        {
                            $month [] = $book->id;
                        }
                    }
                }
                if($isWeek)
                {
                    $currentData = $week;
                }elseif ($isMonth)
                {
                    $currentData = $month;
                }
                $reservations = collect($currentData)->map(function($oneRecord)
                {
                    $book = Book::find($oneRecord);
                    $names = [];
                    $servicePackage = explode(",", $book->services);
                    foreach ($servicePackage as $packageService)
                    {
                        $Service = Service::find($packageService);
                        $names [] = $Service->name;
                    }
                    return
                        [
                            "id" => $book->id,
                            "customer_name" => $book->customer->name,
                            "time" => $book->time,
                            "date" => $book->date,
                            "employee_name" => $book->employee->name,
                            "services_name" => $names,
                            "attendance_status" => $book->attendance_status,
                            "make_flag" => $book->make_flag,
                            "phone" => $book->customer->phone,
                        ];

                });
            }else
            {
                $reservations = collect($today)->map(function($oneRecord)
                {
                    $book = Book::find($oneRecord);
                    $names = [];
                    $servicePackage = explode(",", $book->services);
                    foreach ($servicePackage as $packageService)
                    {
                        $Service = Service::find($packageService);
                        $names [] = $Service->name;
                    }
                    return
                        [
                            "id" => $book->id,
                            "customer_name" => $book->customer->name,
                            "time" => $book->time,
                            "date" => $book->date,
                            "employee_name" => $book->employee->name,
                            "services_name" => $names,
                            "attendance_status" => $book->attendance_status,
                            "make_flag" => $book->make_flag,
                            "phone" => $book->customer->phone,
                        ];

                });
            }
            return $this->returnData(['response'], [$reservations],'Reservations Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function makeFlag(Request $request,$id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $book = Book::find($id);

            if (!$book || $book->employee->branch->beauty_center->id != $beautyCenter->id) {
                return $this->returnError(201, 'invalid id');
            }

            $today = Carbon::now();
            if($today->format('Y-m-d') != $book->date || $book->attendance_status != '2')
            {
                return $this->returnError(201, 'booking is not today');
            }
            foreach ($beautyCenter->branches as $branch)
            {
                foreach ($branch->employees as $employee)
                {
                    foreach ($employee->book_servies as $booking)
                    {
                        $today = Carbon::now();
                        if($today->format('Y-m-d') == $booking->date && $booking->make_flag == 1)
                        {
                            return $this->returnError(201, 'already make a flag today');
                        }
                    }
                }
            }

            $book->make_flag = 1;
            $book->save();
            $customer = Customer::find($book->customer->id);
            $customer->flag = $customer->flag + 1;
            if($customer->flag == 5)
            {
                $customer->delete();
            }else
            {
                $customer->save();
            }
            return $this->returnSuccessMessage('Updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function destroy(Beauty_center $beauty_center)
    {
        //
    }




    function getBooking(Request $request,$id)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();
            $book = Book::find($id);
            if(!$book || $book->employee->branch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not booking id');
            }
            $names = [];
            $services = explode(",", $book->services);
            foreach ($services as $service)
            {
                $Service = Service::find($service);
                $names [] = $Service->name;
            }
            $result =
                [
                    'customer_name'=>$book->customer->name,
                    'date'=>$book->date,
                    'time'=>$book->time,
                    'employee_name'=>$book->employee->name,
                    'services'=>$names,
                    'attendance_status'=>$book->attendance_status,
                    'branch_name'=>$book->employee->branch->name,
                ];
            return $this->returnData(['response'], [$result],'Booking Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    public function home(Request $request)
    {
        try {
            $beautyCenter = auth('beautyCenter')->userOrFail();

            $allFeedbacks = [];
            foreach ($beautyCenter->branches as $branch)
            {
                foreach ($branch->employees as $employee)
                {
                    $sum = 0;
                    $counter = count($employee->employee_reviews);
                    foreach ($employee->employee_reviews as $review)
                    {
                        $sum += $review->rate;
                    }
                    if($counter == 0)
                    {
                        $average = 0;
                    }else
                    {
                        $average = $sum / $counter;
                    }
                    $object =
                        [
                            'id' => $employee->id,
                            'average' => $average
                        ];
                    $allFeedbacks [] = $object;
                }
            }
            $this->array_sort_by_column($allFeedbacks, 'average');
            $firstThreeElements = array_slice($allFeedbacks, 0, 5);
            $topRated = collect($firstThreeElements)->map(function($oneRecord)
            {
                $employee = Employee::find($oneRecord['id']);

                if($employee->image)
                {
                    $employee->image = asset('/assets/employees/' . $employee->image );
                }
                if($oneRecord['average'] == 0)
                {
                    $average = '0';
                }else
                {
                    $average = sprintf("%.1f", $oneRecord['average']);
                }

                return
                    [
                        "id" => $employee->id,
                        "name" => $employee->name,
                        "rate" => $average,
                        "image" => $employee->image,
                    ];
            });

            $allbooking = [];
            foreach ($beautyCenter->branches as $branch)
            {
                foreach ($branch->employees as $employee)
                {
                    foreach ($employee->book_servies as $booking)
                    {
                        $today = Carbon::now();
                        if($today->format('Y-m-d') == $booking->date && $booking->attendance_status == '0')
                        {
                            $object =
                                [
                                    'id' => $booking->id,
                                    'time' => $booking->time
                                ];
                            $allbooking [] = $object;
                        }
                    }
                }
            }
            $this->array_sort_by_column($allbooking, 'time',SORT_ASC);
            $firstThreeElements = array_slice($allbooking, 0, 5);
            $todayBooking = collect($firstThreeElements)->map(function($oneRecord)
            {
                $book = Book::find($oneRecord['id']);

                $names = [];
                $services = explode(",", $book->services);
                foreach ($services as $service)
                {
                    $Service = Service::find($service);
                    $names [] = $Service->name;
                }
                return
                    [
                        "id" => $book->id,
                        "customer_name" => $book->customer->name,
                        "time" => $book->time,
                        "date" => $book->date,
                        "services_names" => $names,
                        "employee_name" => $book->employee->name,
                        "branch_name" => $book->employee->branch->name,
                    ];
            });
            $allService = [];
            foreach ($beautyCenter->services as $service)
            {
                $bookings = Book::all();
                $reserved = 0;
                foreach ($bookings as $booking)
                {
                    $services = explode(",", $booking->services);
                    foreach ($services as $bookingService)
                    {
                        $BookingService = Service::find($bookingService);
                        if($BookingService->id == $service->id)
                        {
                            $reserved ++;
                        }
                    }
                }

                $object =
                    [
                        'id' => $service->id,
                        'reserved' => $reserved
                    ];
                $allService [] = $object;
            }
            $this->array_sort_by_column($allService, 'reserved');
            $firstThreeElements = array_slice($allService, 0, 5);
            $topServices = collect($firstThreeElements)->map(function($oneRecord)
            {
                $service = Service::find($oneRecord['id']);
                return
                    [
                        "id" => $service->id,
                        "name" => $service->name,
                        "reserved_times" => $oneRecord['reserved']
                    ];
            });
            unset(
                $beautyCenter->branches
            );
            $extraData =
                [
                    'main_branch_id' => $beautyCenter->branches[0]->id,
                    'scheduler_duration' => $beautyCenter->scheduler_duration,
                ];
            $result =
                [
                    'today_reservation' => $todayBooking,
                    'topRated' => $topRated,
                    'topServices' => $topServices,
                    'extraData' => $extraData
                ];
            return $this->returnData(['response'], [$result],'Home Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    function array_sort_by_column(&$array, $column, $direction = SORT_DESC) {
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
