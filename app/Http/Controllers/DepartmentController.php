<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Department;
use App\Http\Requests;
use Validator;
use View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dept = DB::table('departments')
            ->where('departments.deleted_at','=',NULL)
            ->get();
        return view('department.department',['departments' => $dept]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('department.adddepartment');
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|min:4',       
            'dept_type' => 'required',   
        ]);

        if ($validator->fails()) {
            return redirect('department/create')
                        ->withErrors($validator)
                        ->withInput();
        }


        $dept = Department::create([
            'name'  =>  $request->input('name'),
            'dept_type' => $request->input('dept_type'),
        ]);

        

        return redirect('department');
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
        $dept = DB::table('employees')
            ->join('informations','employees.information_id','=','informations.id')
            ->where('department_id','=',$id)
            ->get();
        $dept_name=Department::find($id);        
        $d=$dept_name->name;
        return view('department.viewdepartment',['employees' => $dept,'dept_name'=>$d]);
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
        $dept = DB::table('departments')
            ->where('id','=',$id)
            ->get();

        return View::make('department.editdepartment')
            ->with('department', $dept);
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
            'name' => 'required|max:255|min:4',
            'dept_type'=>'required',
        ]);

        if ($validator->fails()) {
            return redirect('department/'.$id.'/edit')
                        ->withErrors($validator)
                        ->withInput();
        }
        $dept = Department::find($id);
        $dept->name = $request->input('name');
        $dept->dept_type = $request->input('dept_type');
        $dept->save();               

        return redirect('department');
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
        $dept = Department::find($id);
        $dept->delete();
        

        return redirect('department');
    }
}
