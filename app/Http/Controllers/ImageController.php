<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;

class ImageController extends Controller
{
    public function __construct()
    {
    }

    public function showImagePersonnel($id, $image_id)
    {
        $destinationPath = 'app/public/upload/personnel/' . $id . '/' . $image_id;
        if (file_exists(storage_path($destinationPath))) {
            $type = pathinfo($destinationPath, PATHINFO_EXTENSION);
            header("Content-Type: " . $type);
            readfile(storage_path($destinationPath));
        } else {
            echo "Image Not Found";
        }
    }

    public function showImageTeam($id, $image_id)
    {
        $destinationPath = 'app/public/upload/team/' . $id . '/' . $image_id;
        if (file_exists(storage_path($destinationPath))) {
            $type = pathinfo($destinationPath, PATHINFO_EXTENSION);
            header("Content-Type: " . $type);
            readfile(storage_path($destinationPath));
        } else {
            echo "Image Not Found";
        }
    }

    public function showImageTournament($id, $image_id)
    {
        $destinationPath = 'app/public/upload/tournament/' . $id . '/' . $image_id;
        if (file_exists(storage_path($destinationPath))) {
            $type = pathinfo($destinationPath, PATHINFO_EXTENSION);
            header("Content-Type: " . $type);
            // storage_path($destinationPath));
            readfile(URL::to('/storage_api_mabar/upload/tournament/15/image_tournament_15.jpg'));
            // readfile(storage_path($destinationPath));
        } else {
            echo "Image Not Found";
        }
    }

    public function showImageGame($id, $image_id)
    {
        $destinationPath = 'app/public/upload/master_game/' . $id . '/' . $image_id;
        if (file_exists(storage_path($destinationPath))) {
            $type = pathinfo($destinationPath, PATHINFO_EXTENSION);
            header("Content-Type: " . $type);
            readfile(storage_path($destinationPath));
        } else {
            echo "Image Not Found";
        }
    }
}
