<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Employee;
use App\Login;
use App\Information;
use App\Http\Requests;
use Validator;
use Hash;
use View;
use Carbon\Carbon;
use Log;
class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //$emp = DB::table('employees')->get();
        $emp = DB::table('logins')
            ->join('informations','logins.id','=','informations.login_id')
            ->join('employees','logins.id','=','employees.login_id')
            ->select('logins.username','informations.*','employees.*')
            ->where('employees.deleted_at','=',NULL)
            ->get();
        return view('employee.employee',['employees' => $emp]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $dept = DB::table('departments')
            ->get();
        
        return view('employee.addemployee',['departments' => $dept]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $current_time = Carbon::now()->toDayDateTimeString();

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:logins|max:10|min:4',
            'password' => 'required|max:30',
            'repeat_password' => 'required|same:password',
            'employee_type' => 'required|in:teaching,non-teaching',
            'fname' => 'required|max:30',
            'mname' => 'required|max:30',
            'lname' => 'required|max:30',
            'address' => 'required|max:100',
            'photo' => 'mimes:jpeg,bmp,png',
        ]);

        if ($validator->fails()) {
            return redirect('employee/create')
                        ->withErrors($validator)
                        ->withInput();
        }

        $login = Login::create([
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('username')),
            'role' => 'employee',
        ]);

        // upload path        
        if($request->file('photo')==NULL)
        {
            $photoFileName='anon.png';
        }   
        else
        {
            $destinationPath = 'uploads'; 
            $photoExtension = $request->file('photo')->getClientOriginalExtension(); 
            $photoFileName = 'photo'.rand(11111,99999).'.'.$photoExtension;
            $request->file('photo')->move($destinationPath, $photoFileName);    
        }    
        

       
        $inf = Information::create([
            'fname' => $request->input('fname'),
            'mname' => $request->input('mname'),
            'lname' => $request->input('lname'),
            'address' => $request->input('address'),
            'login_id' => $login->id,
            'photo' => $photoFileName,
        ]);
        
        $emp = Employee::create([
            'login_id'  =>  $login->id,
            'information_id'    =>  $inf->id,
            'employee_type' => $request->input('employee_type'),
            'department_id' => $request->input('department_id'),
        ]);

        

        return redirect('employee');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
         $emp = DB::table('logins')
            ->join('informations','logins.id','=','informations.login_id')
            ->join('employees','logins.id','=','employees.login_id')
            ->select('logins.username','informations.*','employees.*')
            ->where('employees.id','=',$id)
            ->get();
        
        return View::make('employee.editemployee')
            ->with('employee', $emp)
            ->with('department', DB::table('departments')->lists('name','id'));;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $validator = Validator::make($request->all(), [           
            'fname' => 'required|max:30',
            'mname' => 'required|max:30',
            'lname' => 'required|max:30',
            'address' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return redirect('employee/'.$id.'/edit')
                        ->withErrors($validator)
                        ->withInput();
        }
        $inf = Information::find($id);
        $inf->fname = $request->input('fname');
        $inf->mname = $request->input('mname');
        $inf->lname = $request->input('lname');
        $inf->address = $request->input('address');        
        $empid = $inf->id;
        $inf->save();

        if(!is_null($request->input('dept_id')))
        {
           DB::table('employees')
            ->where('information_id', $empid)
            ->update(['department_id' => $request->input('dept_id')]);
        }
        
               

        return redirect('employee');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $emp = Employee::find($id);
        $emp->delete();

        $login = DB::table('logins')
            ->join('informations','logins.id','=','informations.login_id')
            ->join('employees','logins.id','=','employees.login_id')
            ->select('logins.id')
            ->where('employees.id','=',$id)
            ->delete();


        return redirect('employee');
    }

    public function viewTeaching()
    {
        $emp = DB::table('logins')
            ->join('informations','logins.id','=','informations.login_id')
            ->join('employees','logins.id','=','employees.login_id')
            ->select('logins.username','informations.*','employees.*')
            ->where('employees.deleted_at','=',NULL)
            ->where('employees.employee_type','=','teaching')
            ->get();
        return view('employee.employee',['employees' => $emp]);

    }
    public function viewNonTeaching()
    {
        $emp = DB::table('logins')
            ->join('informations','logins.id','=','informations.login_id')
            ->join('employees','logins.id','=','employees.login_id')
            ->select('logins.username','informations.*','employees.*')
            ->where('employees.deleted_at','=',NULL)
            ->where('employees.employee_type','=','non-teaching')
            ->get();
        return view('employee.employee',['employees' => $emp]);
    }
}
