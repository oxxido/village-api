<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //$books = Books::all();
        return response()->json(['hello'=>'world']);
    }

    /**
     * Get the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $book = Books::where('id', $id)->get();
        if(!empty($book['items'])){
            return response()->json($book);
        }
        else{
            return response()->json(['status' => 'fail']);
        }
    }
}
