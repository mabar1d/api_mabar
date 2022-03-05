<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterGame;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;


class ImageController extends Controller
{
    public function __construct()
    {
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
