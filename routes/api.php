<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FavoirteController;
use App\Http\Controllers\BeautyCenterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\crmController;
use App\Http\Controllers\MangerController;
use App\Http\Controllers\beautyCrm;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class,'login']);
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('refresh', [AuthController::class,'refresh']);
    Route::post('me', [AuthController::class,'me']);
    Route::post('registers', [AuthController::class,'register']);
    Route::post('uploadImage', [AuthController::class,'uploadProfileImage']);
    Route::get('test', [AuthController::class,'test']);
    Route::get('profile/{type}/{id}', [AuthController::class,'profile']);
    Route::post('forgetPassword', [AuthController::class,'forgetPassword']);
    Route::post('verifyCode', [AuthController::class,'verifyCode']);
    Route::post('updatePassword', [AuthController::class,'updatePassword']);

    //Chat
    Route::post('chat/{id}', [MessageController::class,'store']);
    Route::post('getChat/{id}', [MessageController::class,'index']);
    Route::get('ll/test', [MessageController::class,'NotifyApi']);
    Route::get('chatRoom', [MessageController::class,'chatRoom']);

    //notification
    Route::post('getNotification', [AuthController::class,'getNotification']);

    Route::get('getBaniAdam', [AuthController::class,'getBaniAdam']);


    Route::get('getNewToken/{type}', [AuthController::class,'getNewToken']);






});
Route::group([
    'middleware' => ['api','checkBeautyCenterMiddleware'],
    'prefix' => 'beauty_center'

], function ($router) {
    Route::get('testAuth', [AuthController::class,'testAuth']);

    //update
    Route::patch('update', [BeautyCenterController::class,'update']);

    Route::post('offDates', [BeautyCenterController::class,'offDates']);

    //Media
    Route::post('addMedia', [BeautyCenterController::class,'addToMedia']);
    Route::delete('removeMedia/{id}', [BeautyCenterController::class,'removeMedia']);

    // Products
    Route::post('product', [ProductController::class,'store']);
    Route::patch('product/{id}', [ProductController::class,'update']);
    Route::delete('product/{id}', [ProductController::class,'destroy']);
    Route::post('{id}/product', [ProductController::class,'index']);

    //Branches
    Route::post('branch', [BranchController::class,'store']);
    Route::get('{id}/branch', [BranchController::class,'index']);

    //Employees
    Route::post('employee/{branch_id}', [EmployeeController::class,'store']);
    Route::patch('employee/convert/{id}', [EmployeeController::class,'update']);
    Route::delete('employee/{id}', [EmployeeController::class,'destroy']);
    Route::get('employee/{id}', [EmployeeController::class,'index']);

    //Services
    Route::post('service', [ServiceController::class,'store']);
    Route::patch('service/{id}', [ServiceController::class,'update']);
    Route::delete('service/{id}', [ServiceController::class,'destroy']);
    Route::post('{id}/service', [ServiceController::class,'index']);

    //Packages
    Route::post('package', [PackageController::class,'store']);
    Route::post('{id}/package', [PackageController::class,'index']);
    Route::patch('package/{id}', [PackageController::class,'update']);
    Route::delete('package/{id}', [PackageController::class,'destroy']);


    //Offers
    Route::post('service/offer/{id}', [ServiceController::class,'makeOffer']);
    Route::post('service/offer/end/{id}', [ServiceController::class,'endOffer']);
    Route::post('package/offer/{id}', [PackageController::class,'makeOffer']);
    Route::post('package/offer/end/{id}', [PackageController::class,'endOffer']);

    Route::post('service/offer', [ServiceController::class,'getOffer']);
    Route::post('package/offer', [PackageController::class,'getOffer']);

    //Reservation
    Route::post('getReservation/{branch_id}/{status}', [BeautyCenterController::class,'getReservation']);
    Route::post('book/customerPresence/{booking_id}', [BookController::class,'customerPresence']);
    Route::post('book/customerAbsence/{booking_id}', [BookController::class,'customerAbsence']);
    Route::get('book/{booking_id}', [BeautyCenterController::class,'getBooking']);
    Route::get('makeFlag/{booking_id}', [BeautyCenterController::class,'makeFlag']);


    //Home
    Route::get('home', [BeautyCenterController::class,'home']);



    //CRM
    Route::post('addAttendance/{id}', [beautyCrm::class,'addAttendance']);
    Route::post('addFinance/{id}', [beautyCrm::class,'addFinance']);
    Route::get('getAttendance/{id}/{monthName}', [beautyCrm::class,'getAttendance']);
    Route::get('getVacations/{id}', [beautyCrm::class,'getVacations']);
    Route::get('getFinances/{id}', [beautyCrm::class,'getFinances']);

    Route::get('getAllAttendance/{monthName}', [beautyCrm::class,'getAllAttendance']);
    Route::get('getAllVacations', [beautyCrm::class,'getAllVacations']);
    Route::get('getAllFinances', [beautyCrm::class,'getAllFinances']);
    Route::get('bookingCRM/{monthName}', [beautyCrm::class,'bookingCRM']);
    Route::post('filterBookingCRM/{monthName}', [beautyCrm::class,'filterBookingCRM']);



});

Route::group([
    'middleware' => ['api','checkMiddleware'],
    'prefix' => 'customer'

], function ($router) {

    //products
    Route::post('{id}/product', [ProductController::class,'index']);
    Route::post('{id}/service', [ServiceController::class,'index']);
    Route::post('{id}/package', [PackageController::class,'index']);

    //Favorite
    Route::post('favorite/{id}', [FavoirteController::class,'store']);
    Route::get('favorites', [FavoirteController::class,'index']);
    Route::delete('favorite/{id}', [FavoirteController::class,'destroy']);

    //Filter
    Route::post('filterBeautyCenter', [CustomerController::class,'filterBeautyCenter']);
    Route::post('filterProduct', [CustomerController::class,'filterProduct']);
    Route::post('filterService', [CustomerController::class,'filterService']);

    //Update
    Route::patch('update', [CustomerController::class,'update']);

    //Booking
    Route::get('getBranches/{beauty_center_id}', [CustomerController::class,'getBranches']);
    Route::get('getEmployees/{branch_id}', [CustomerController::class,'getEmployees']);
    Route::get('getReservations/{date}/{employee_id}', [CustomerController::class,'getReservations']);
    Route::post('book', [BookController::class,'store']);
    Route::get('getAppointments/{status}', [CustomerController::class,'getAppointments']);
    Route::get('book/{booking_id}', [CustomerController::class,'getBooking']);
    Route::post('book/cancelBooking/{booking_id}', [BookController::class,'cancelBooking']);


    //Review
    Route::post('reviews/{book_id}', [ReviewController::class,'store']);
    Route::patch('reviews/{book_id}', [ReviewController::class,'update']);


    //Home
    Route::get('home', [CustomerController::class,'home']);
    Route::post('getOffers', [CustomerController::class,'getOffers']);
    Route::get('getOrderNumber', [CustomerController::class,'getOrderNumber']);


});
Route::post('/custom/broadcasting/auth/a', function (Request $request) {


    if($request->header('type') == 'customer')
    {
        try {
            $user = auth('customer')->userOrFail();

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }else
    {
        try {
            $user = auth('beautyCenter')->userOrFail();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'),env('PUSHER_APP_ID'),['cluster' =>'eu']);
    $data = $pusher->presenceAuth($request->channel_name,$request->socket_id,$user->id,$user);
    return $data;
});

Route::post('/custom/broadcasting/auth/a/a', function (Request $request) {
    $customer = \App\Models\Customer::find(1);
    $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'),env('PUSHER_APP_ID'),['cluster' =>'eu']);
    $data = $pusher->getPresenceUsers('presence-21');
    return $data;
});




Route::group([
    'middleware' => 'api',
    'prefix' => 'admin'

], function ($router) {

    Route::get('getCustomers/{numOfPage}/{numOfRows}', [AdminController::class,'getCustomers']);
    Route::get('search/getCustomers/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getCustomersSearch']);
    Route::get('getBeautyCenters/{numOfPage}/{numOfRows}', [AdminController::class,'getBeautyCenters']);
    Route::get('search/getBeautyCenters/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getBeautyCentersSearch']);
    Route::get('getEmployees/{numOfPage}/{numOfRows}', [AdminController::class,'getEmployees']);
    Route::get('search/getEmployees/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getEmployeesSearch']);
    Route::get('getProducts/{numOfPage}/{numOfRows}', [AdminController::class,'getProducts']);
    Route::get('search/getProducts/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getProductsSearch']);
    Route::get('getProductDetails/{id}', [AdminController::class,'getProductDetails']);
    Route::get('getBooking/{numOfPage}/{numOfRows}', [AdminController::class,'getBooking']);
    Route::get('search/getBooking/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getBookingSearch']);
    Route::get('getBookingDetails/{id}', [AdminController::class,'getBookingDetails']);
    Route::get('getService/{numOfPage}/{numOfRows}', [AdminController::class,'getService']);
    Route::get('getServiceDetails/{id}', [AdminController::class,'getServiceDetails']);
    Route::get('search/getService/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getServiceSearch']);
    Route::get('getOffers/{numOfPage}/{numOfRows}', [AdminController::class,'getOffers']);
    Route::get('search/getOffers/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getOffersSearch']);

    Route::delete('delete/customers/{id}', [AdminController::class,'deleteCustomer']);
    Route::delete('delete/beautyCenters/{id}', [AdminController::class,'deleteBeautyCenter']);
    Route::delete('delete/products/{id}', [AdminController::class,'deleteProduct']);
    Route::delete('delete/employees/{id}', [AdminController::class,'deleteEmployee']);
    Route::delete('delete/booking/{id}', [AdminController::class,'deleteBooking']);
    Route::delete('delete/services/{id}', [AdminController::class,'deleteService']);

    Route::post('changePassword', [AdminController::class,'changePassword']);
    Route::post('update/customers/{id}', [AdminController::class,'updateCustomer']);
    Route::post('update/beautyCenters/{id}', [AdminController::class,'updateBeautyCenter']);
    Route::post('update/employees/{id}', [AdminController::class,'updateEmployee']);
    Route::post('update/products/{id}', [AdminController::class,'updateProduct']);
    Route::post('update/booking/{id}', [AdminController::class,'updateBooking']);
    Route::post('update/services/{id}', [AdminController::class,'updateService']);

    //Features
    Route::post('convertEmployee/{id}', [AdminController::class,'convertEmployee']);
    Route::post('block/customers/{id}', [AdminController::class,'blockCustomer']);
    Route::post('block/beautyCenter/{id}', [AdminController::class,'blockBeautyCenter']);
    Route::post('unblock/customers/{id}', [AdminController::class,'unblockCustomer']);
    Route::post('unblock/beautyCenter/{id}', [AdminController::class,'unblockBeautyCenter']);
    Route::post('ban/customers/{id}', [AdminController::class,'banCustomer']);
    Route::post('ban/beautyCenter/{id}', [AdminController::class,'banBeautyCenter']);


    //CRM
    Route::post('addAttendance/{id}', [crmController::class,'addAttendance']);
    Route::post('addFinance/{id}', [crmController::class,'addFinance']);
    Route::get('getAttendance/{id}/{monthName}', [crmController::class,'getAttendance']);
    Route::get('getVacations/{id}', [crmController::class,'getVacations']);
    Route::get('getFinances/{id}', [crmController::class,'getFinances']);

    Route::get('getAllAttendance/{monthName}', [crmController::class,'getAllAttendance']);
    Route::get('getAllVacations', [crmController::class,'getAllVacations']);
    Route::get('getAllFinances', [crmController::class,'getAllFinances']);
    Route::get('bookingCRM/{monthName}', [crmController::class,'bookingCRM']);
    Route::post('filterBookingCRM/{monthName}', [crmController::class,'filterBookingCRM']);

});



Route::group([
    'middleware' => ['api'],
    'prefix' => 'manger'

], function ($router) {

    Route::get('getEmployees', [MangerController::class,'index']);
    Route::post('addAttendance/{id}', [MangerController::class,'addAttendance']);
    Route::post('addFinance/{id}', [MangerController::class,'addFinance']);

});

