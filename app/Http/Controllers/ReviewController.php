<?php

namespace App\Http\Controllers;

use App\Models\Beauty_center;
use App\Models\Book;
use App\Models\Employee_review;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
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
    public function store(Request $request,$book_id)
    {

        $validator = Validator::make($request->all(), [
            'beauty_center_rate' => 'required|in:1,2,3,4,5',
            'employee_rate' => 'required|in:1,2,3,4,5',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        try {
            $customer = auth('customer')->userOrFail();
            $book = Book::find($book_id);

            if(!$book)
            {
                return $this->returnError(201, 'Not booking id');
            }
            $reviewsBeautyCenter = Review::where('customer_id',$customer->id)
                ->where('beauty_center_id',$book->employee->branch->beauty_center->id)->get();

            if(sizeof($reviewsBeautyCenter) != 0)
            {
                return $this->returnError(201, 'you already reviewed this beauty center');
            }
            $today = Carbon::now();
            $book_date = Carbon::parse($book->date);
            if($today->lt($book_date))
            {
                return $this->returnError(201, 'Not available');
            }
            $beautyCenterFeedback = new Review;
            $beautyCenterFeedback->rate = $request->beauty_center_rate;
            $beautyCenterFeedback->customer_id = $customer->id;
            $beautyCenterFeedback->beauty_center_id = $book->employee->branch->beauty_center->id;
            if($request->beauty_center_feedback && $request->beauty_center_feedback != "" && $request->beauty_center_feedback != "null")
            {
                $beautyCenterFeedback->feedback = $request->beauty_center_feedback;
            }else
            {
                $beautyCenterFeedback->feedback = "";
            }
            $beautyCenterFeedback->save();


            $employeeReviewFeedback = new Employee_review;
            $employeeReviewFeedback->rate = $request->employee_rate;
            $employeeReviewFeedback->customer_id = $customer->id;
            $employeeReviewFeedback->employee_id = $book->employee->id;
            if($request->employee_feedback && $request->employee_feedback != "" && $request->employee_feedback != "null")
            {
                $employeeReviewFeedback->feedback = $request->employee_feedback;
            }else
            {
                $employeeReviewFeedback->feedback = "";
            }
            $employeeReviewFeedback->save();
            return $this->returnSuccessMessage('Feedback Added Successfully',200);

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'beauty_center_rate' => 'required|in:1,2,3,4,5',
            'employee_rate' => 'required|in:1,2,3,4,5',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        try {
            $customer = auth('customer')->userOrFail();
            $book = Book::find($id);
            if(!$book || $book->customer->id != $customer->id)
            {
                return $this->returnError(201, 'Not booking id');
            }

            $reviewsBeautyCenter = Review::where('customer_id',$customer->id)
                ->where('beauty_center_id',$book->employee->branch->beauty_center->id)->first();

            $reviewsBeautyCenter->rate = $request->beauty_center_rate;
            if($request->beauty_center_feedback && $request->beauty_center_feedback != "" && $request->beauty_center_feedback != "null")
            {
                $reviewsBeautyCenter->feedback = $request->beauty_center_feedback;
            }else
            {
                $reviewsBeautyCenter->feedback = "";
            }
            $reviewsBeautyCenter->save();

            $reviewEmployee = Employee_review::where('customer_id',$customer->id)
                ->where('employee_id',$book->employee->id)->first();

            $reviewEmployee->rate = $request->employee_rate;
            if($request->employee_feedback && $request->employee_feedback != "" && $request->employee_feedback != "null")
            {
                $reviewEmployee->feedback = $request->employee_feedback;
            }else
            {
                $reviewEmployee->feedback = "";
            }
            $reviewEmployee->save();
            return $this->returnSuccessMessage('Feedback Updated Successfully',200);

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
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
