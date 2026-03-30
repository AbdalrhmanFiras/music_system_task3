<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserImportRequest;
use App\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:import-file', ['only' => ['userImportFile']]);
    }

    public function userImportFile(UserImportRequest $request) // admin
    {
        $request->validated();
        Excel::queueImport(new UserImport, $request->file('file'));

        return response()->json(['message' => 'File imported successfully'], 200);
    }
}
