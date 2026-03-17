<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserImportRequest;
use App\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function userImportFile(UserImportRequest $request)
    {
        $request->validated();
        Excel::import(new UserImport, $request->file('file'));

        return response()->json(['message' => 'File imported successfully'], 200);
    }
}
