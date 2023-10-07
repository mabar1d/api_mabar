<?php

namespace App\Console\Commands;

use App\Helpers\FcmFirebase;
use App\Models\JobNotifFirebaseModel;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendingFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notifFirebase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $count = 0;
            $getListNotif = JobNotifFirebaseModel::getList(array(
                "status" => 0,
                "limit" => 1000
            ));
            foreach ($getListNotif as $rowListNotif) {
                $keyClient = $rowListNotif["client_key"];
                $titleFirebase = $rowListNotif["notif_title"];
                $bodyFirebase = $rowListNotif["notif_body"];
                $imgFirebase = $rowListNotif["notif_img_url"];
                $urlFirebase = $rowListNotif["notif_url"];
                $send = FcmFirebase::send($keyClient, $titleFirebase, $bodyFirebase, $imgFirebase, $urlFirebase);
                if ($send->success == 1) {
                    JobNotifFirebaseModel::find($rowListNotif["id"])->update([
                        "status" => 1
                    ]);
                    $count++;
                } else {
                    JobNotifFirebaseModel::find($rowListNotif["id"])->update([
                        "status" => 2
                    ]);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }
}
