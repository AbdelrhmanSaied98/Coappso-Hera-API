<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Beauty_center;
use App\Models\Book;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function getCustomers($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $customers = Customer::all();
            $counter = count($customers);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $customers = Customer::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $customers = collect($customers)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/customers/' . $oneRecord->image);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "phone" => $oneRecord->phone,
                        "email" => $oneRecord->email,
                        "isBlocked" => $oneRecord->isBlocked,
                        "ban_times" => $oneRecord->ban_times,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'customers'=>$customers
            ];
            return $this->returnData(['response'], [$result],'Customers Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCustomersSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $customers = Customer::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%')
                    ->orWhere('phone', $name)
                    ->orWhere('email', 'LIKE',$name.'%');
            })->get();
            $counter = count($customers);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $customers = Customer::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%')
                    ->orWhere('phone', $name)
                    ->orWhere('email', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $customers = collect($customers)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/customers/' . $oneRecord->image);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "phone" => $oneRecord->phone,
                        "email" => $oneRecord->email,
                        "isBlocked" => $oneRecord->isBlocked,
                        "ban_times" => $oneRecord->ban_times,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'customers'=>$customers
            ];
            return $this->returnData(['response'], [$result],'Customers Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getBeautyCenters($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $beauty_centers = Beauty_center::all();
            $counter = count($beauty_centers);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $beauty_centers = Beauty_center::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $beauty_centers = collect($beauty_centers)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/beauty_centers/' . $oneRecord->image);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "phone" => $oneRecord->phone,
                        "email" => $oneRecord->email,
                        "isBlocked" => $oneRecord->isBlocked,
                        "ban_times" => $oneRecord->ban_times,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'beauty_centers'=>$beauty_centers
            ];
            return $this->returnData(['response'], [$result],'beauty centers Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getBeautyCentersSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $beauty_centers = Beauty_center::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%')
                    ->orWhere('phone', $name)
                    ->orWhere('email', 'LIKE',$name.'%');
            })->get();
            $counter = count($beauty_centers);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $beauty_centers = Beauty_center::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%')
                    ->orWhere('phone', $name)
                    ->orWhere('email', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $beauty_centers = collect($beauty_centers)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/beauty_centers/' . $oneRecord->image);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "phone" => $oneRecord->phone,
                        "email" => $oneRecord->email,
                        "isBlocked" => $oneRecord->isBlocked,
                        "ban_times" => $oneRecord->ban_times,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'beauty_centers'=>$beauty_centers
            ];
            return $this->returnData(['response'], [$result],'beauty centers Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getEmployees($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $employees= Employee::all();
            $counter = count($employees);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $employees = Employee::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $employees = collect($employees)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/employees/' . $oneRecord->image);
                }
                $branches = [];
                foreach ($oneRecord->branch->beauty_center->branches as $branch)
                {
                    $object =
                        [
                            'id' => $branch->id,
                            'name' => $branch->name,
                            'address' => $branch->address,
                        ];
                    $branches [] = $object;
                }


                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "title" => $oneRecord->title,
                        "salary" => $oneRecord->salary,
                        "time_from" => $oneRecord->time_from,
                        "time_to" => $oneRecord->time_to,
                        "branch_name" => $oneRecord->branch->name,
                        "beauty_center_name" => $oneRecord->branch->beauty_center->name,
                        "branches" => $branches,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'employees'=>$employees
            ];
            return $this->returnData(['response'], [$result],'Employees Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getEmployeesSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $employees= Employee::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->get();
            $counter = count($employees);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $employees = Employee::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $employees = collect($employees)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/employees/' . $oneRecord->image);
                }
                $branches = [];
                foreach ($oneRecord->branch->beauty_center->branches as $branch)
                {
                    $object =
                        [
                            'id' => $branch->id,
                            'name' => $branch->name,
                            'address' => $branch->address,
                        ];
                    $branches [] = $object;
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "title" => $oneRecord->title,
                        "salary" => $oneRecord->salary,
                        "time_from" => $oneRecord->time_from,
                        "time_to" => $oneRecord->time_to,
                        "branch_name" => $oneRecord->branch->name,
                        "beauty_center_name" => $oneRecord->branch->beauty_center->name,
                        "branches" => $branches,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'employees'=>$employees
            ];
            return $this->returnData(['response'], [$result],'Employees Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getProducts($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $products= Product::all();
            $counter = count($products);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $products = Product::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $products = collect($products)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/products/' . $oneRecord->image);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "price" => $oneRecord->price,
                        "beauty_center_name" => $oneRecord->beauty_center->name,
                        "category_name" => $oneRecord->category->name,
                        "description" => $oneRecord->description,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'products'=>$products
            ];
            return $this->returnData(['response'], [$result],'Products Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getProductsSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $products= Product::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->get();
            $counter = count($products);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $products = Product::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $products = collect($products)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/products/' . $oneRecord->image);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "name" => $oneRecord->name,
                        "image" => $oneRecord->image,
                        "price" => $oneRecord->price,
                        "beauty_center_name" => $oneRecord->beauty_center->name,
                        "category_name" => $oneRecord->category->name,
                        "description" => $oneRecord->description,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'products'=>$products
            ];
            return $this->returnData(['response'], [$result],'Products Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getProductDetails($id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $Product= Product::find($id);
            if(!$Product)
            {
                return $this->returnError(201, 'Invalid id');
            }

            if($Product->image)
            {
                $Product->image = asset('/assets/products/' . $Product->image);
            }


            $result =
                [
                    "id" => $Product->id,
                    "name" => $Product->name,
                    "image" => $Product->image,
                    "price" => $Product->price,
                    "beauty_center_name" => $Product->beauty_center->name,
                    "category_id" => $Product->category->id,
                    "description" => $Product->description,
                ];


            return $this->returnData(['response'], [$result],'Product Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getBooking($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $bookings= Book::all();
            $counter = count($bookings);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $bookings = Book::orderBy('date', 'DESC')
                ->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $bookings = collect($bookings)->map(function($oneRecord)
            {
                return
                    [
                        "id" => $oneRecord->id,
                        "customer_name" => $oneRecord->customer->name,
                        "date" => $oneRecord->date,
                        "beauty_center_name" => $oneRecord->employee->branch->beauty_center->name,
                        "attendance_status" => $oneRecord->attendance_status,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'booking'=>$bookings
            ];
            return $this->returnData(['response'], [$result],'Booking Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getBookingSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();


            $customer = Customer::where('phone',$name)->first();
            if(! $customer)
            {
                $counter = 0;
                $bookings = [];
            }else
            {
                $bookings= Book::where(function($query) use ($customer) {
                    $query->where('customer_id',$customer->id);
                })->get();
                $counter = count($bookings);
                $skippedNumbers = ($numOfPage - 1) * $numOfRows;

                $bookings = Book::where(function($query) use ($customer) {
                    $query->where('customer_id',$customer->id);
                })->orderBy('date', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($numOfRows)
                    ->get();
                $bookings = collect($bookings)->map(function($oneRecord)
                {
                    return
                        [
                            "id" => $oneRecord->id,
                            "customer_name" => $oneRecord->customer->name,
                            "date" => $oneRecord->date,
                            "beauty_center_name" => $oneRecord->employee->branch->beauty_center->name,
                            "attendance_status" => $oneRecord->attendance_status,
                        ];
                });
            }

            $result = [
                'counter'=>$counter,
                'booking'=>$bookings
            ];
            return $this->returnData(['response'], [$result],'Booking Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getBookingDetails($id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $bookings= Book::find($id);


            $time = date('H:i ', strtotime($bookings->time));
            return
                [
                    "id" => $bookings->id,
                    "customer_name" => $bookings->customer->name,
                    "employee_name" => $bookings->employee->name,
                    "date" => $bookings->date,
                    "time" => $time,
                    "beauty_center_name" => $bookings->employee->branch->beauty_center->name,
                    "branch_name" => $bookings->employee->branch->name,
                    "attendance_status" => $bookings->attendance_status,
                    "payment_status" => $bookings->payment_status,
                ];
            return $this->returnData(['response'], [$bookings],'Booking Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getService($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $services = Service::all();
            $counter = count($services);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $services = Service::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $services = collect($services)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/services/' . $oneRecord->image);
                }
                if($oneRecord->package)
                {
                    $type = "package";
                }else
                {
                    $type = "service";
                }

                if($oneRecord->is_offer == "1")
                {
                    $price = $oneRecord->new_price;
                }else
                {
                    $price = $oneRecord->price;
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "beauty_center_name" => $oneRecord->beauty_center->name,
                        "type" => $type,
                        "price" => $price,
                        "image" => $oneRecord->image,
                        "duration" => $oneRecord->duration,
                        "name" => $oneRecord->name,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'services'=>$services
            ];
            return $this->returnData(['response'], [$result],'Service Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getServiceSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $services = Service::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->get();
            $counter = count($services);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $services = Service::where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $services = collect($services)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/services/' . $oneRecord->image);
                }
                if($oneRecord->package)
                {
                    $type = "package";
                }else
                {
                    $type = "service";
                }

                if($oneRecord->is_offer == "1")
                {
                    $price = $oneRecord->new_price;
                }else
                {
                    $price = $oneRecord->price;
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "beauty_center_name" => $oneRecord->beauty_center->name,
                        "type" => $type,
                        "price" => $price,
                        "image" => $oneRecord->image,
                        "duration" => $oneRecord->duration,
                        "name" => $oneRecord->name,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'services'=>$services
            ];
            return $this->returnData(['response'], [$result],'Service Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getServiceDetails($id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $Service= Service::find($id);
            if(!$Service)
            {
                return $this->returnError(201, 'Invalid id');
            }

            if($Service->image)
            {
                $Service->image = asset('/assets/services/' . $Service->image);
            }
            if($Service->package)
            {
                $type = "package";
            }else
            {
                $type = "service";
            }

            if($Service->is_offer == "1")
            {
                $price = $Service->new_price;
            }else
            {
                $price = $Service->price;
            }
            $result =
                [
                    "id" => $Service->id,
                    "beauty_center_name" => $Service->beauty_center->name,
                    "type" => $type,
                    "price" => $price,
                    "image" => $Service->image,
                    "duration" => $Service->duration,
                    "name" => $Service->name,
                    "category_id" => $Service->category_id,
                    "description" => $Service->description,
                ];


            return $this->returnData(['response'], [$result],'Service Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getOffers($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $services = Service::where('is_offer',"1")->get();
            $counter = count($services);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $services = Service::where('is_offer',"1")
                ->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $services = collect($services)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/services/' . $oneRecord->image);
                }
                if($oneRecord->package)
                {
                    $type = "package";
                }else
                {
                    $type = "service";
                }

                if($oneRecord->is_offer == "1")
                {
                    $price = $oneRecord->new_price;
                }else
                {
                    $price = $oneRecord->price;
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "beauty_center_name" => $oneRecord->beauty_center->name,
                        "type" => $type,
                        "price" => $price,
                        "image" => $oneRecord->image,
                        "duration" => $oneRecord->duration,
                        "name" => $oneRecord->name,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'services'=>$services
            ];
            return $this->returnData(['response'], [$result],'Offers Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getOffersSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $services = Service::where('is_offer',"1")->where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->get();
            $counter = count($services);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $services = Service::where('is_offer',"1")->where(function($query) use ($name) {
                $query->where('name', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $services = collect($services)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/services/' . $oneRecord->image);
                }
                if($oneRecord->package)
                {
                    $type = "package";
                }else
                {
                    $type = "service";
                }

                if($oneRecord->is_offer == "1")
                {
                    $price = $oneRecord->new_price;
                }else
                {
                    $price = $oneRecord->price;
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "beauty_center_name" => $oneRecord->beauty_center->name,
                        "type" => $type,
                        "price" => $price,
                        "image" => $oneRecord->image,
                        "duration" => $oneRecord->duration,
                        "name" => $oneRecord->name,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'services'=>$services
            ];
            return $this->returnData(['response'], [$result],'Service Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteCustomer($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $customer = Customer::find($id);
            if(!$customer)
            {
                return $this->returnError(201, 'Not available Customer');
            }
            $customer->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteBeautyCenter($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $beauty_center = Beauty_center::find($id);
            if(!$beauty_center)
            {
                return $this->returnError(201, 'Not available Beauty Center');
            }
            $beauty_center->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteProduct($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $product = Product::find($id);
            if(!$product)
            {
                return $this->returnError(201, 'Not available Product');
            }
            $product->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteEmployee($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $employee = Employee::find($id);
            if(!$employee)
            {
                return $this->returnError(201, 'Not available Employee');
            }
            $employee->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteBooking($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $booking = Book::find($id);
            if(!$booking)
            {
                return $this->returnError(201, 'Not available booking');
            }
            $booking->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteService($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $newSevice = Service::find($id);
            if(! $newSevice )
            {
                return $this->returnError(201, 'Not Service id');
            }
            $beautyCenter = Beauty_center::find($newSevice->beauty_center->id);
            $packages = [];
            foreach ($beautyCenter->services as $service)
            {
                if($service->package)
                {
                    $packages [] = $service->id;
                }
            }
            foreach ($packages as $package)
            {
                $packageService = Service::find($package);
                $servicePackage = explode(",", $packageService->package->services);
                foreach ($servicePackage as $item)
                {
                    if($item == $newSevice->id)
                    {
                        return $this->returnError(201, 'Service in Package');
                    }
                }
            }


            $bookings = Book::all();
            foreach ($bookings as $booking)
            {
                $services = explode(",", $booking->services);
                $newSerices = [];
                foreach ($services as $service)
                {

                    if($service != $newSevice->id)
                    {
                        $newSerices [] = $service;
                    }
                }
                if($newSerices == [])
                {
                    $booking->delete();
                }else
                {
                    $allNewServices = implode(',',$newSerices);
                    $booking->services = $allNewServices;
                    $booking->save();
                }
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

    public function changePassword(Request $request)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|min:8',
                'password' => 'required|confirmed|min:8',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }
            if (! Hash::check($request->old_password, $admin->password)) {

                return $this->returnError(201, 'Wrong Password');
            }
            $admin->password = Hash::make($request->password);
            $admin->save();
            return  $this->returnSuccessMessage('password have been changed',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateCustomer(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $customer = Customer::find($id);
            if(!$customer)
            {
                return $this->returnError(201, 'Not available customer');
            }
            if($request->email && $request->email != "")
            {
                $validator = Validator::make($request->all(), [
                    'email' => 'string|email|min:5|max:255|unique:customers',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $customer->email = $request->email;
                $customer->save();
            }

            if($request->name && $request->name != "")
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|min:3|max:255',
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
                    'address'=>'required|string',
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
                    'phone' => 'string|min:9|unique:customers',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $customer->phone = $request->phone;
                $customer->save();
            }

            if($request->image && $request->image != "")
            {
                $validator = Validator::make($request->all(), [
                    'image' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($customer->image)
                {
                    $path =  public_path('/assets/customers/'.$customer->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'customers','image');
                $customer->image = $image;
                $customer->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateBeautyCenter(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $beauty_center = Beauty_center::find($id);
            if(!$beauty_center)
            {
                return $this->returnError(201, 'Not available beauty center');
            }
            if($request->email && $request->email != "")
            {
                $validator = Validator::make($request->all(), [
                    'email' => 'string|email|min:5|max:255|unique:beauty_centers',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beauty_center->email = $request->email;
                $beauty_center->save();
            }

            if($request->name && $request->name != "")
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beauty_center->name = $request->name;
                $beauty_center->save();
            }

            if($request->phone && $request->phone != "")
            {
                $validator = Validator::make($request->all(), [
                    'phone' => 'string|min:9|unique:beauty_centers',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beauty_center->phone = $request->phone;
                $beauty_center->save();
            }

            if($request->image && $request->image != "")
            {
                $validator = Validator::make($request->all(), [
                    'image' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($beauty_center->image)
                {
                    $path =  public_path('/assets/beauty_centers/'.$beauty_center->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'beauty_centers','image');
                $beauty_center->image = $image;
                $beauty_center->save();
            }

            if($request->time_to && $request->time_to != "")
            {
                $validator = Validator::make($request->all(), [
                    'time_to' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beauty_center->time_to = $request->time_to;
                $beauty_center->save();
            }

            if($request->time_from && $request->time_from != "")
            {
                $validator = Validator::make($request->all(), [
                    'time_from' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beauty_center->time_from = $request->time_from;
                $beauty_center->save();
            }

            if($request->off_day && $request->off_day != "")
            {
                $validator = Validator::make($request->all(), [
                    'off_day' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $off_dayArray = json_decode($request->off_day);
                $off_days = implode(',',$off_dayArray);
                $beauty_center->off_day = $off_days;
                $beauty_center->save();
            }

            if($request->scheduler_duration && $request->scheduler_duration != "")
            {
                $validator = Validator::make($request->all(), [
                    'scheduler_duration' => 'numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $beauty_center->scheduler_duration = $request->scheduler_duration;
                $beauty_center->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateEmployee(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $employee = Employee::find($id);
            if(!$employee)
            {
                return $this->returnError(201, 'Not available employee');
            }

            if($request->name && $request->name != "")
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $employee->name = $request->name;
                $employee->save();
            }

            if($request->image && $request->image != "")
            {
                $validator = Validator::make($request->all(), [
                    'image' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($employee->image)
                {
                    $path =  public_path('/assets/employees/'.$employee->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'employees','image');
                $employee->image = $image;
                $employee->save();
            }

            if($request->title && $request->title != "")
            {
                $validator = Validator::make($request->all(), [
                    'title' => 'string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $employee->title = $request->title;
                $employee->save();
            }

            if($request->salary && $request->salary != "")
            {
                $validator = Validator::make($request->all(), [
                    'salary' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $employee->salary = $request->salary;
                $employee->save();
            }

            if($request->time_to && $request->time_to != "")
            {
                $validator = Validator::make($request->all(), [
                    'time_to' => 'required|date_format:H:i',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $employee->time_to = $request->time_to;
                $employee->save();
            }

            if($request->time_from && $request->time_from != "")
            {
                $validator = Validator::make($request->all(), [
                    'time_from' => 'required|date_format:H:i',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $employee->time_from = $request->time_from;
                $employee->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateProduct(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $product = Product::find($id);
            if(!$product)
            {
                return $this->returnError(201, 'Not available Product');
            }

            if($request->name && $request->name != "")
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $product->name = $request->name;
                $product->save();
            }

            if($request->image && $request->image != "")
            {
                $validator = Validator::make($request->all(), [
                    'image' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($product->image)
                {
                    $path =  public_path('/assets/products/'.$product->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'products','image');
                $product->image = $image;
                $product->save();
            }

            if($request->price && $request->price != "")
            {
                $validator = Validator::make($request->all(), [
                    'price' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $product->price = $request->price;
                $product->save();
            }

            if($request->category_id && $request->category_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'category_id' => 'required|exists:categories,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $product->category_id = $request->category_id;
                $product->save();
            }

            if($request->description && $request->description != "")
            {
                $validator = Validator::make($request->all(), [
                    'description' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $product->description = $request->description;
                $product->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateBooking(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $booking = Book::find($id);
            if(!$booking)
            {
                return $this->returnError(201, 'Not available booking');
            }

            if($request->date && $request->date != "")
            {
                $validator = Validator::make($request->all(), [
                    'date' => 'required|date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $booking->date = $request->date;
                $booking->save();
            }


            if($request->time && $request->time != "")
            {
                $validator = Validator::make($request->all(), [
                    'time' => 'required|date_format:H:i',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $booking->time = $request->time;
                $booking->save();
            }

            if($request->payment_status && $request->payment_status != "")
            {
                $validator = Validator::make($request->all(), [
                    'payment_status' => 'required|in:"1","2","3"',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $booking->payment_status = $request->payment_status;
                $booking->save();
            }

            if($request->attendance_status && $request->attendance_status != "")
            {
                $validator = Validator::make($request->all(), [
                    'attendance_status' => 'required|in:"0","1","2"',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $booking->attendance_status = $request->attendance_status;
                $booking->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateService(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $service = Service::find($id);
            if(!$service)
            {
                return $this->returnError(201, 'Not available Service');
            }

            if($request->name && $request->name != "")
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $service->name = $request->name;
                $service->save();
            }

            if($request->image && $request->image != "")
            {
                $validator = Validator::make($request->all(), [
                    'image' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($service->image)
                {
                    $path =  public_path('/assets/services/'.$service->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'services','image');
                $service->image = $image;
                $service->save();
            }

            if($request->price && $request->price != "")
            {
                $validator = Validator::make($request->all(), [
                    'price' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $service->price = $request->price;
                $service->save();
            }

            if($request->category_id && $request->category_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'category_id' => 'required|exists:categories,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $service->category_id = $request->category_id;
                $service->save();
            }

            if($request->duration && $request->duration != "")
            {
                $validator = Validator::make($request->all(), [
                    'duration' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $service->duration = $request->duration;
                $service->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function convertEmployee(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'new_branch_id'=>'required|exists:branches,id',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $admin = auth('admin')->userOrFail();
            $employee =  Employee::find($id);
            if(! $employee )
            {
                return $this->returnError(201, 'Invalid employee id');
            }
            $beautyCenter = Beauty_center::find($employee->branch->beauty_center->id);

            $newBranch = Branch::find($request->new_branch_id);
            if($newBranch->beauty_center->id != $beautyCenter->id)
            {
                return $this->returnError(201, 'Not Beauty Center Branch');
            }
            $employee->branch_id = $request->new_branch_id;
            $employee->save();
            return $this->returnSuccessMessage('Employee moved to new branch Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function banCustomer(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'ban_times' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $admin = auth('admin')->userOrFail();
            $customer = Customer::find($id);
            if (!$customer) {
                return $this->returnError(201, 'invalid id');
            }
            $customer->ban_times = $request->ban_times;
            $customer->save();
            return  $this->returnSuccessMessage('Banned Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function blockCustomer(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $customer = Customer::find($id);
            if (!$customer) {
                return $this->returnError(201, 'invalid id');
            }
            $customer->isBlocked = 1;
            $customer->save();
            return  $this->returnSuccessMessage('Blocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unblockCustomer(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $customer = Customer::find($id);
            if (!$customer) {
                return $this->returnError(201, 'invalid id');
            }
            $customer->isBlocked = 0;
            $customer->save();
            return  $this->returnSuccessMessage('unblocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function banBeautyCenter(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'ban_times' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $admin = auth('admin')->userOrFail();
            $beauty_center = Beauty_center::find($id);
            if (!$beauty_center) {
                return $this->returnError(201, 'invalid id');
            }
            $beauty_center->ban_times = $request->ban_times;
            $beauty_center->save();
            return  $this->returnSuccessMessage('Banned Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function blockBeautyCenter(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $beauty_center = Beauty_center::find($id);
            if (!$beauty_center) {
                return $this->returnError(201, 'invalid id');
            }
            $beauty_center->isBlocked = 1;
            $beauty_center->save();
            return  $this->returnSuccessMessage('Blocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unblockBeautyCenter(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $beauty_center = Beauty_center::find($id);
            if (!$beauty_center) {
                return $this->returnError(201, 'invalid id');
            }
            $beauty_center->isBlocked = 0;
            $beauty_center->save();
            return  $this->returnSuccessMessage('unblocked Successfully',200);
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
