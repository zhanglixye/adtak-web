<?php

namespace App\Console;

use App\Services\UploadFileManager\Uploader;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // backup event.log
        $schedule->call(function () {
            $env = \Config::get('app.env');
            $yesterday = Carbon::yesterday('Asia/Tokyo')->format('Y-m-d');
            $yesterday_month = Carbon::yesterday('Asia/Tokyo')->format('Y-m');
            exec('curl http://169.254.169.254/latest/meta-data/public-ipv4', $result, $var);
            $serve_ip = $var == 0 ? array_shift($result) : 'localhost';

            $attachments = [
                "title" => "[${env}][${serve_ip}][schedule] Backup event.log",
                "content" => "success!",
                "color" => "good",
            ];

            try {
                $event_log = storage_path('logs/event.log');
                if (!\File::exists($event_log)) {
                    $attachments["content"] = $event_log . ' is not found.';
                    return;
                }

                // rotate log
                $yesterday_log = storage_path("logs/event-${yesterday}.log");
                if (\File::exists($yesterday_log)) {
                    throw new \Exception($yesterday_log . ' is already exists.');
                }
                \File::move($event_log, $yesterday_log);

                // backup to file storage
                $backup_log_path = "logs/admonster_web/event/${yesterday_month}/event-${yesterday}_${serve_ip}.log";
                Uploader::uploadToS3(file_get_contents($yesterday_log), $backup_log_path);
            } catch (\Exception $e) {
                $attachments["color"] = "danger";
                $attachments["content"] = $e->getMessage();
            } finally {
                \Slack::send(null, $attachments);
            }
        })->timezone('Asia/Tokyo')->dailyAt('00:00');

        // backup laravel.log
        $schedule->call(function () {
            $env = \Config::get('app.env');
            $last_month = Carbon::today('Asia/Tokyo')->subMonths(1)->format('Y-m');
            $two_months_before = Carbon::today('Asia/Tokyo')->subMonths(2)->format('Y-m');
            exec('curl http://169.254.169.254/latest/meta-data/public-ipv4', $result, $var);
            $serve_ip = $var == 0 ? array_shift($result) : 'localhost';

            $attachments = [
                "title" => "[${env}][${serve_ip}][schedule] Backup laravel.log",
                "content" => "success!",
                "color" => "good",
            ];

            try {
                $laravel_log = storage_path('logs/laravel.log');
                if (!\File::exists($laravel_log)) {
                    $attachments["content"] = $laravel_log . ' is not found.';
                    return;
                }

                // rotate log
                $last_month_log = storage_path("logs/laravel-${last_month}.log");
                if (\File::exists($last_month_log)) {
                    throw new \Exception($last_month_log . ' is already exists.');
                }
                \File::move($laravel_log, $last_month_log);

                // backup to file storage
                $backup_log_path = "logs/admonster_web/laravel/${last_month}/laravel-${last_month}_${serve_ip}.log";
                Uploader::uploadToS3(file_get_contents($last_month_log), $backup_log_path);

                // delete laravel.log
                \File::delete(storage_path("logs/laravel-${two_months_before}.log"));

                // delete event.log
                \File::delete(\File::glob(storage_path("logs/event-${two_months_before}*.log")));
            } catch (\Exception $e) {
                $attachments["color"] = "danger";
                $attachments["content"] = $e->getMessage();
            } finally {
                \Slack::send(null, $attachments);
            }
        })->timezone('Asia/Tokyo')->monthlyOn(1, '00:05');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
