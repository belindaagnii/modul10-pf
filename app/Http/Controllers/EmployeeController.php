<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator, input, redirect;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\support\str;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesExport;
use PDF;

class EmployeeController extends Controller

{    public function index()
    {
        $pageTitle = 'Employee List';

        confirmDelete();

        // ELOQUENT
        $employees = Employee::all();

        return view('employee.index', [
            'pageTitle' => $pageTitle,
            'employees' => $employees
        ]);
    }
    public function create()
    {
        $pageTitle = 'Create Employee';

        // ELOQUENT
        $positions = Position::all();
        return view('employee.create', compact('pageTitle', 'positions'));
    }
    public function store(Request $request)
    {

    $messages = [
        'required' => ':Attribute harus diisi.',
        'email' => 'Isi :attribute dengan format yang benar',
        'numeric' => 'Isi :attribute dengan angka'
    ];

    $validator = Validator::make($request->all(), [
        'firstName' => 'required',
        'lastName' => 'required',
        'email' => 'required|email',
        'age' => 'required|numeric',
    ], $messages);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }
     // Get File
     $file = $request->file('cv');

     if ($file != null) {
         $originalFilename = $file->getClientOriginalName();
         $encryptedFilename = $file->hashName();

         // Store File
         $file->store('public/files');
    }


    // ELOQUENT
    $employee = New Employee;
    $employee->firstname = $request->firstName;
    $employee->lastname = $request->lastName;
    $employee->email = $request->email;
    $employee->age = $request->age;
    $employee->position_id = $request->position;

    if ($file != null) {
        $employee->original_filename = $originalFilename;
        $employee->encrypted_filename = $encryptedFilename;
    }

    $employee->save();
    Alert::success('Added Successfully', 'Employee Data Added Successfully.');

    return redirect()->route('employees.index');
}
    public function show(string $id)
    {
        // RAW SQL QUERY
            // $employee = collect(DB::select('
            //     select *, employees.id as employee_id, positions.name as position_name
            //     from employees
            //     left join positions on employees.position_id = positions.id
            //     where employees.id = ?
            // ', [$id]))->first();

            // return view('employee.show', compact('pageTitle', 'employee'));
        // $pageTitle = 'Employee Detail';
        // $employee = DB::table('employees')
        // ->select('employees.*', 'positions.name as position_name', 'positions.id as position_id')
        // ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
        // ->where('employees.id', $id)
        // ->first();
        // return view('employee.show', compact('pageTitle', 'employee'));

        $pageTitle = 'Employee Detail';
        // ELOQUENT
        $employee = Employee::find($id);
        return view('employee.show', compact('pageTitle', 'employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $pageTitle = 'Edit Employee';
        // $positions = DB::table('positions')->get();
        // $employee = DB::table('employees')
        //     ->select('*', 'employees.id as employee_id', 'positions.name as position_name')
        //     ->leftJoin('positions', 'employees.position_id', 'positions.id')
        //     ->where('employees.id', $id)
        //     ->first();
        // return view('employee.edit', compact('pageTitle', 'positions', 'employee'));

        $pageTitle = 'Edit Employee';
        // ELOQUENT
        $positions = Position::all();
        $employee = Employee::find($id);
        return view('employee.edit', compact('pageTitle', 'positions', 'employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
            // Get File
            $file = $request->file('cv');

            if ($file != null) {
                $originalFilename = $file->getClientOriginalName();
                $encryptedFilename = $file->hashName();

                // Store File
                $file->store('public/files');
            }

        // ELOQUENT
        $employee = Employee::find($id);
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }
        $employee->save();

        Alert::success('Changed Successfully', 'Employee Data Changed Successfully.');
        return redirect()->route('employees.index');

    }

    public function destroy(string $id)
    {
        // ELOQUENT
        $employee = Employee::find($id);

        if($employee->encrypt_filename){
            Storage::delete('public/files/' . $employee->encrypt_filename);
        }

        $employee->delete();
        Alert::success('Deleted Successfully', 'Employee Data Deleted Successfully.');
        return redirect()->route('employees.index');
    }
    public function downloadFile($employeeId)
    {
        $employee = Employee::find($employeeId);
        $encryptedFilename = 'public/files/'.$employee->encrypted_filename;
        $downloadFilename = Str::lower($employee->firstname.'_'.$employee->lastname.'_cv.pdf');

        if(Storage::exists($encryptedFilename)) {
            return Storage::download($encryptedFilename, $downloadFilename);
        }
    }
    public function getData(Request $request)
    {
        $employees = Employee::with('position');

        if ($request->ajax()) {
            return datatables()->of($employees)
                ->addIndexColumn()
                ->addColumn('actions', function($employee) {
                    return view('employee.actions', compact('employee'));
                })
                ->toJson();
        }
    }
    public function exportExcel()
    {
    return Excel::download(new EmployeesExport, 'employees.xlsx');
    }

    public function exportPdf()
    {
    $employees = Employee::all();

    $pdf = PDF::loadView('employee.export_pdf', compact('employees'));

    return $pdf->download('employees.pdf');
    }

}
