<?php

namespace App\Http\Controllers;


use App\Models\Beauty_center;
use App\Models\Book;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Employee_review;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
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

    public function show(Customer $customer)
    {
        //
    }

    public function edit(Customer $customer)
    {
        //
    }


    public function home(Request $request)
    {
        $beautyCenters = Beauty_center::all();
        $allFeedbacks = [];
        foreach ($beautyCenters as $beautyCenter)
        {
            $sum = 0;
            $counter = count($beautyCenter->reviews);
            foreach ($beautyCenter->reviews as $review)
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
                    'id' => $beautyCenter->id,
                    'average' => $average
                ];
            $allFeedbacks [] = $object;
        }
        $this->array_sort_by_column($allFeedbacks, 'average');
        $firstThreeElements = array_slice($allFeedbacks, 0, 3);
        $topRated = collect($firstThreeElements)->map(function($oneRecord)
        {
            $beauty_center = Beauty_center::find($oneRecord['id']);

            if($beauty_center->image)
            {
                $beauty_center->image = asset('/assets/beauty_centers/' . $beauty_center->image );
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
                    "id" => $beauty_center->id,
                    "name" => $beauty_center->name,
                    "image" => $beauty_center->image,
                    "rate" => $average,
                    "address" => $beauty_center->branches[0]->address
                ];
        });

        $offers = Service::where('is_offer','1')->get();
        $alloffers = [];
        foreach ($offers as $offer)
        {
            $newPrice = (int)$offer->new_price;
            $precent = ($newPrice / $offer->price) * 100;
            $object =
                [
                    'id' => $offer->id,
                    'price' => $precent
                ];
            $alloffers [] = $object;
        }
        $this->array_sort_by_column($alloffers, 'price');
        $firstThreeElements = array_slice($alloffers, 0, 3);
        $bestOffers = collect($firstThreeElements)->map(function($oneRecord)
        {
            $service = Service::find($oneRecord['id']);

            if($service->image)
            {
                $service->image = asset('/assets/services/' . $service->image );
            }
            return
                [
                    "id" => $service->beauty_center->id,
                    "name" => $service->name,
                    "image" => $service->image,
                    "percent" => $oneRecord['price'],
                ];
        });
        $result =
            [
                'topRated' => $topRated,
                'bestOffers' => $bestOffers,
            ];


        return $this->returnData(['response'], [$result],'Home Data');
    }


    function array_sort_by_column(&$array, $column, $direction = SORT_DESC) {
        $reference_array = array();

        foreach($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    public function update(Request $request)
    {
        try {
            $customer = auth('customer')->userOrFail();
            if($request->name && $request->name != "")
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $customer->name = $request->name;
                $customer->save();
            }
            if($request->address && $request->address != "")
            {
                $validator = Validator::make($request->all(), [
                    'address' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $customer->address = $request->address;
                $customer->save();
            }
            if($request->phone && $request->phone != "")
            {
                $validator = Validator::make($request->all(), [
                    'phone' => 'string|min:9|unique:customers|unique:beauty_centers',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $customer->phone = $request->phone;
                $customer->save();
            }
            return $this->returnSuccessMessage('Updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function destroy(Customer $customer)
    {
        //
    }

    function getBranches($beauty_center_id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $beautyCenter = Beauty_center::find($beauty_center_id);
            if(!$beautyCenter)
            {
                return $this->returnError(201, 'Not beautyCenter id');
            }
            $offDaysArray = explode(',',$beautyCenter->off_day);
            $beautyCenter->off_day = $offDaysArray;

            $offDatesArray = explode(',',$beautyCenter->offDates);
            $beautyCenter->offDates = $offDatesArray;
            $result =
                [
                    'time_from' => $beautyCenter->time_from,
                    'time_to' => $beautyCenter->time_to,
                    'off_day' => $beautyCenter->off_day,
                    'offDates' => $beautyCenter->offDates,
                    'scheduler_duration' => $beautyCenter->scheduler_duration,
                    'branches'=> $beautyCenter->branches,
            ];
            return $this->returnData(['response'], [$result],'Beauty Center Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    function getEmployees($branch_id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $branch = Branch::find($branch_id);
            if(!$branch)
            {
                return $this->returnError(201, 'Not branch id');
            }
            return $this->returnData(['response'], [$branch->employees],'Employees Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    function getReservations($date,$employee_id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $employee = Employee::find($employee_id);
            if(!$employee)
            {
                return $this->returnError(201, 'Not Employee id');
            }
            $reservations = Book::where('date',$date)->where('attendance_status','0')->where('employee_id',$employee->id)->get();
            $reservations = collect($reservations)->map(function($oneReservation) use ($employee)
            {
                $sum = 0;
                $time = date('H:i', strtotime($oneReservation->time));
                $timeCarbon = Carbon::parse($oneReservation->time);

                $reservedService = explode(",", $oneReservation->services);
                foreach ($reservedService as $Service)
                {
                    $Service = Service::find($Service);
                    $sum += $Service->duration;
                }


                $allTime = [];

                for($i = 1 ; $i <= $sum/$employee->branch->beauty_center->scheduler_duration;$i++)
                {
                    $timeCarbone = $timeCarbon;
                    $allTime [] = $timeCarbon->addMinutes($employee->branch->beauty_center->scheduler_duration)->format('H:i');
                    $timeCarbon = $timeCarbone;
                }
                return
                    [
                        "time" => $allTime,
                        "skipped_time" =>$sum/$employee->branch->beauty_center->scheduler_duration
                    ];

            });
            return $this->returnData(['response'], [$reservations],'Reservations Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    function getBooking(Request $request,$id)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $book = Book::find($id);
            if(!$book && $book->customer->id != $customer->id)
            {
                return $this->returnError(201, 'Not booking id');
            }
            $review = Review::where('customer_id',$customer->id)->where('beauty_center_id',$book->employee->branch->beauty_center->id)->first();
            $employeeReview = Employee_review::where('customer_id',$customer->id)->where('employee_id',$book->employee->id)->first();

            if($review && $employeeReview)
            {

                $newtime = strtotime($review->created_at);
                $newDate = date('M d, Y',$newtime);
                $newtime = date('g:i A',$newtime);
                $created_at = $newDate." ".$newtime;


                $newtime = strtotime($review->updated_at);
                $newDate = date('M d, Y',$newtime);
                $newtime = date('g:i A',$newtime);
                $updated_at = $newDate." ".$newtime;

                $review = [
                    "id"=> $review->id,
                    "rate"=> $review->rate,
                    "feedback"=> $review->feedback,
                    "customer_id"=> $review->customer_id,
                    "beauty_center_id"=> $review->beauty_center_id,
                    "created_at"=> $created_at,
                    "updated_at"=> $updated_at

                ];


                $newtime = strtotime($employeeReview->created_at);
                $newDate = date('M d, Y',$newtime);
                $newtime = date('g:i A',$newtime);
                $created_at = $newDate." ".$newtime;


                $newtime = strtotime($employeeReview->updated_at);
                $newDate = date('M d, Y',$newtime);
                $newtime = date('g:i A',$newtime);
                $updated_at = $newDate." ".$newtime;


                $employeeReview = [
                    "id"=> $employeeReview->id,
                    "rate"=> $employeeReview->rate,
                    "feedback"=> $employeeReview->feedback,
                    "customer_id"=> $employeeReview->customer_id,
                    "employee_id"=> $employeeReview->employee_id,
                    "created_at"=> $created_at,
                    "updated_at"=> $updated_at

                ];


                $isReviewed = 1;
                $reviewAll =
                    [
                        $review,
                        $employeeReview,
                    ];
            }else
            {
                $isReviewed = 0;
                $reviewAll = [];
            }

            $time = date('g:i a', strtotime($book->time));
            $reservations =
            [
                'book_id' => $book->id,
                'date' => $book->date,
                'time' => $time,
                'employee_name' => $book->employee->name,
                'beauty_center_name' => $book->employee->branch->beauty_center->name,
                'address' => $book->employee->branch->address,
                'isReviewed' => $isReviewed,
                'Review' => $reviewAll,
            ];
            return $this->returnData(['response'], [$reservations],'Reservations Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    function getAppointments($status)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $customer = Customer::find($customer->id);

            $currentData = [];
            $history = [];
            foreach ($customer->book_servies as $book)
            {
                $currentDate = Carbon::now();
                $book_date = Carbon::parse($book->date." ".$book->time);
                if($book_date->gt($currentDate))
                {
                    $currentData [] = $book->id;
                }else
                {
                    $history [] = $book->id;
                }
            }
            if($status == '0')
            {
                $reservations = collect($history)->map(function($oneRecord)
                {
                    $book = Book::find($oneRecord);
                    $branch = $book->employee->branch;
                    $beauty_center = $branch->beauty_center;
                    $employee = $book->employee;
                    $names = [];
                    $servicePackage = explode(",", $book->services);
                    $sum = 0;
                    if($beauty_center->image)
                    {
                        $beauty_center->image = asset('/assets/beauty_centers/' . $beauty_center->image );
                    }
                    $time = date('g:ia', strtotime($book->time));
                    foreach ($servicePackage as $packageService)
                    {
                        $Service = Service::find($packageService);
                        $names [] = $Service->name;
                        $sum += $Service->price;
                    }
                    return
                        [
                            "id" => $book->id,
                            "beauty_center_name" => $beauty_center->name,
                            "address" => $branch->address,
                            "date" => $book->date,
                            "time" => $time,
                            "employee_name" => $employee->name,
                            "services_name" => $names,
                            "price" => $sum,
                            "beauty_center_image" => $beauty_center->image,
                            "attendance_status" => $book->attendance_status,
                        ];

                });
            }elseif($status == '1')
            {
                $reservations = collect($currentData)->map(function($oneRecord)
                {
                    $book = Book::find($oneRecord);
                    $branch = $book->employee->branch;
                    $beauty_center = $branch->beauty_center;
                    $employee = $book->employee;
                    $names = [];
                    $servicePackage = explode(",", $book->services);
                    $sum = 0;
                    if($beauty_center->image)
                    {
                        $beauty_center->image = asset('/assets/beauty_centers/' . $beauty_center->image );
                    }
                    $time = date('g:ia', strtotime($book->time));
                    foreach ($servicePackage as $packageService)
                    {
                        $Service = Service::find($packageService);
                        $names [] = $Service->name;
                        $sum += $Service->price;
                    }
                    return
                        [
                            "id" => $book->id,
                            "beauty_center_name" => $beauty_center->name,
                            "address" => $branch->address,
                            "date" => $book->date,
                            "time" => $time,
                            "employee_name" => $employee->name,
                            "services_name" => $names,
                            "price" => $sum,
                            "beauty_center_image" => $beauty_center->image,
                            "attendance_status" => $book->attendance_status,
                        ];

                });
            }
            return $this->returnData(['response'], [$reservations],'Appointments Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    public function getOrderNumber(Request $request)
    {
        try {
            $customer = auth('customer')->userOrFail();

            $today = Carbon::now();
            $today_employees= [];
            foreach ($customer->book_servies as $book)
            {
                if($today->format('Y-m-d') == $book->date)
                {
                    $today_employees [] = $book->employee->id;
                }
            }

            $allAppointments = [];




            foreach ($today_employees as $employeeID)
            {
                $bookings = Book::where('date',$today->format('Y-m-d'))
                ->where('employee_id',$employeeID)->where('attendance_status','0')
                ->get();
                $newAllBooking = [];
                $isHera = false;
                foreach ($bookings as $oneBooking)
                {
                    if($oneBooking->customer->id == $customer->id)
                    {
                        $isHera = true;
                        $object =
                            [
                                'id' => $oneBooking->id,
                                'time' => $oneBooking->time,
                                'myTime'=> 1
                            ];
                        $newAllBooking [] = $object;
                    }else
                    {
                        $object =
                            [
                                'id' => $oneBooking->id,
                                'time' => $oneBooking->time,
                                'myTime'=> 0
                            ];
                        $newAllBooking [] = $object;
                    }

                }
                if($isHera == false)
                {
                    continue;
                }
                $this->array_sort_by_column($newAllBooking, 'time',SORT_ASC);

                $key = $this->search_exif($newAllBooking,'myTime',1);

                $allAppointments [] = $key;
            }
            $this->array_sort_by_column($allAppointments, 'time',SORT_ASC);
            $allAppointments = collect($allAppointments)->map(function($oneAppointment)
            {
                $book = Book::find($oneAppointment['id']);
                $currentDate = Carbon::now();
                $book_date = Carbon::parse($book->date." ".$book->time);
                $before_date = Carbon::parse($book->date." ".$book->time);
                $before_date->subMinute(15);
                if($currentDate->gte($before_date) && $currentDate->lt($book_date))
                {
                    $active = 1;
                }else
                {
                    $active = 0;
                }
                $time = date('H:i', strtotime($book->time));
                return
                    [
                        "id" => $book->id,
                        "time" => $time,
                        "waiting_number" => $oneAppointment['waiting_number'],
                        "active" => $active,
                    ];
            });

            return $this->returnData(['response'], [$allAppointments],'All appointments');

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    function search_exif($array, $key,$field)
    {

        for ($i = 0 ; $i < count($array) ; $i++)
        {
            if ($array[$i][$key] == $field)
            {
                $object =
                    [
                        'waiting_number' => $i,
                        'time'=>$array[$i]['time'],
                        'id' => $array[$i]['id']
                    ];
                return $object;
            }
        }
    }

    public function getOffers(Request $request)
    {
        $offers = Service::where('is_offer','1')
            ->get();
        $counter = count($offers);
        $skippedNumbers = ($request->numOfPage - 1) * $request->numOfRows;
        $offers = Service::where('is_offer','1')
            ->skip($skippedNumbers)
            ->take($request->numOfRows)
            ->get();

        $offers = collect($offers)->map(function($oneOffer)
        {
            if($oneOffer->image)
            {
                $oneOffer->image = asset('/assets/services/' . $oneOffer->image );
            }
            $percent = ($oneOffer->new_price / $oneOffer->price) * 100;
            return
                [
                    "id" => $oneOffer->beauty_center->id,
                    "name" => $oneOffer->name,
                    "image" => $oneOffer->image,
                    "price" => $oneOffer->price,
                    "new_price" => $oneOffer->new_price,
                    "durationOffer" => $oneOffer->durationOffer,
                    "percent" => $percent,
                ];
        });
        $result = [
            'offers' => $offers,
            'length' =>$counter
        ];
        return $this->returnData(['response'], [$result],'Notifications Data');
    }


    public function filterBeautyCenter (Request $request)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $ids = [];
            if ($request->name) {
                $Beauty_centers = Beauty_center::where('name', 'LIKE',$request->name.'%')->get();
                foreach ($Beauty_centers as $Beauty_center) {
                    $ids [] = $Beauty_center->id;
                }
            }
            if ($request->sell_products == 'true') {
                $Beauty_centers = Beauty_center::all();
                foreach ($Beauty_centers as $Beauty_center) {
                    if(count($Beauty_center->products) != 0)
                    {
                        $ids [] = $Beauty_center->id;
                    }
                }
            }
            if ($request->offers == 'true') {
                $Beauty_centers = Beauty_center::all();

                foreach ($Beauty_centers as $Beauty_center) {
                    foreach ($Beauty_center->services as $service)
                        if($service->is_offer == '1')
                        {
                            $ids [] = $Beauty_center->id;
                        }
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
            foreach ($ids as $id) {
                $cnt = count(array_filter($ids, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);


            $results = [];
            foreach ($result as $id)
            {
                $Beauty_center = Beauty_center::find($id);
                array_push($results,$Beauty_center);
            }
            return $this->returnData(['response'], [$results],'Beauty Center Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function filterProduct(Request $request)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $ids = [];
            $beautyCenters = Beauty_center::all();
            if ($request->name) {
                $products = Product::where('name', 'LIKE',$request->name.'%')->get();
                foreach ($products as $product) {;
                    $ids [] = $product->id;
                }
            }
            if ($request->category && $request->category != '[]') {
                $categories = json_decode($request->category);
                foreach ($beautyCenters as $beautyCenter)
                {
                    foreach ($categories as $category)
                    {
                        if($category == 1)
                        {
                            foreach ($beautyCenter->products as $product)
                            {
                                $ids [] = $product->id;
                            }
                            break;
                        }else
                        {
                            foreach ($beautyCenter->products as $product)
                            {
                                if($product->category_id == $category)
                                {
                                    $ids [] = $product->id;
                                }
                            }
                        }

                    }
                }
            }

            if ($request->price && $request->price[0] != null && $request->price[1] != null ) {
                $products = Product::where('price', '>=', $request->price[0])->where('price', '<=', $request->price[1])->select('id')->get();
                foreach ($products as $record) {
                    $ids [] = $record->id;
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
                if($value == null || $value == ""  || $value == '[]')
                {
                    continue;
                }
                $counter++;
            }
            foreach ($ids as $id) {
                $cnt = count(array_filter($ids, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);

            $results = [];
            foreach ($result as $id)
            {
                $newProduct = Product::find($id);
                array_push($results,$newProduct);
            }
            return $this->returnData(['response'], [$results],'Product Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function filterService(Request $request)
    {
        try {
            $customer = auth('customer')->userOrFail();
            $ids = [];
            $beautyCenters = Beauty_center::all();
            if ($request->name) {
                $services = Service::where('name', 'LIKE',$request->name.'%')->get();
                foreach ($services as $service) {;
                    $ids [] = $service->id;
                }
            }
            if ($request->category && $request->category != '[]' ) {
                $categories = json_decode($request->category);
                foreach ($beautyCenters as $beautyCenter)
                {
                    foreach ($categories as $category)
                    {
                        if($category == 1)
                        {
                            foreach ($beautyCenter->services as $service)
                            {
                                $ids [] = $service->id;
                            }
                            break;
                        }else
                        {
                            foreach ($beautyCenter->services as $service)
                            {
                                if($service->category_id == $category)
                                {
                                    $ids [] = $service->id;
                                }
                            }
                        }

                    }
                }
            }

            if ($request->price && $request->price[0] != null && $request->price[1] != null ) {
                $services = Service::where('price', '>=', $request->price[0])->where('price', '<=', $request->price[1])->select('id')->get();
                foreach ($services as $record) {
                    $ids [] = $record->id;
                }
            }

            if ($request->offers == 'true') {
                $Beauty_centers = Beauty_center::all();

                foreach ($Beauty_centers as $Beauty_center) {
                    foreach ($Beauty_center->services as $service)
                        if($service->is_offer == '1')
                        {
                            $ids [] = $Beauty_center->id;
                        }
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
                if($value == null || $value == "" || $value == 'false' || $value == '[]')
                {
                    continue;
                }
                $counter++;
            }
            foreach ($ids as $id) {
                $cnt = count(array_filter($ids, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);

            $results = [];
            foreach ($result as $id)
            {
                $newService = Service::find($id);
                array_push($results,$newService);
            }
            return $this->returnData(['response'], [$results],'Product Data');
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
