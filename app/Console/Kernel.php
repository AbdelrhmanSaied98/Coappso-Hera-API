<?php

namespace App\Console;

use App\Http\Controllers\MessageController;
use App\Models\Book;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            $allServices = Service::where('is_offer','1')->get();
            if(sizeof($allServices) != 0)
            {
                foreach ($allServices as $service)
                {
                    $newDuration = $service->duration - 1;

                    if($newDuration == 0)
                    {
                        $service->durationOffer = '0';
                        $service->new_price = '0';
                        $service->is_offer = '0';
                        $service->save();
                    }else
                    {
                        $service->durationOffer = $newDuration;
                        $service->save();
                    }
                }
            }


            $customers = Customer::where('ban_times','!=',0)->get();
            if(sizeof($customers) != 0)
            {
                foreach ($customers as $customer)
                {
                    $newDuration = $customer->ban_times - 1;
                    $customer->ban_times = $newDuration;
                    $customer->save();
                }
            }
        })->at('12:00')->timezone('Africa/Cairo');



        $schedule->call(function () {
            $bookings = Book::all();
            foreach ($bookings as $booking)
            {
                $currentDate = Carbon::now();
                $book_date = Carbon::parse($booking->date." ".$booking->time);
                if($currentDate->format('Y-m-d') == $booking->date)
                {
                    if($booking->payment_status != '1')
                    {
                        $currentDate->addMinute(10);
                        if($currentDate->format('H:i') == $book_date->format('H:i'))
                        {
                            $newNotification = new Notification;
                            $newNotification->user_type = 'customer';
                            $newNotification->user_id = $booking->customer->id;
                            $newNotification->content_type = 'reminder';
                            $newNotification->content_id = 0;
                            $newNotification->seen = 0;
                            $newNotification->notification = $booking->customer->name.' ,  we are aboute 10 minute to start ';
                            $newNotification->save();

                            (new MessageController())->NotifyApi(
                                $booking->customer->device_token,
                                "Booking Alarm",
                                $booking->customer->name.' ,  we are aboute 10 minute to start '
                            );
                        }
                    }
                }
            }
        })->everyMinute()->timezone('Africa/Cairo');





    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
