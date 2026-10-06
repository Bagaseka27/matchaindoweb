<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\Shift;

class CabangController extends Controller
{

    public function index()
    {
        $cabangs = Cabang::all();

        return view(
            'cabang.index',
            compact('cabangs')
        );
    }



    public function create()
    {
        return view('cabang.create');
    }




    public function store(Request $request)
    {

        $data = $request->validate([

            'nama_cabang' => 'required',

            'alamat' => 'required',

            'is_aktif' => 'required'

        ]);


        Cabang::create($data);



        return redirect()
            ->route('cabang.index')
            ->with(
                'success',
                'Cabang berhasil ditambahkan'
            );

    }






    public function edit($id)
    {

        $cabang = Cabang::findOrFail($id);


        return view(
            'cabang.edit',
            compact('cabang')
        );

    }






    public function update(Request $request, $id)
    {

        $cabang = Cabang::findOrFail($id);



        $data = $request->validate([

            'nama_cabang' => 'required',

            'alamat' => 'required',

            'is_aktif' => 'required'

        ]);



        $cabang->update($data);



        return redirect()
            ->route('cabang.index')
            ->with(
                'success',
                'Cabang berhasil diperbarui'
            );

    }








    public function shift($id)
    {

        $cabang = Cabang::findOrFail($id);



        $shift = Shift::where(
            'id_cabang',
            $id
        )->get();



        return view(
            'cabang.shift',
            compact(
                'cabang',
                'shift'
            )
        );

    }









    public function storeShift(Request $request, $id)
    {


        $request->validate([

            'nama_shift' => 'required',

            'jam_mulai' => 'required',

            'jam_selesai' => 'required'

        ]);




        Shift::create([


            'id_cabang' => $id,


            'nama_shift' => $request->nama_shift,


            'jam_mulai' => $request->jam_mulai,


            'jam_selesai' => $request->jam_selesai


        ]);




        return redirect()
            ->route(
                'cabang.shift',
                $id
            )
            ->with(
                'success',
                'Shift berhasil ditambahkan'
            );


    }









    public function updateShift(Request $request, $id)
    {


        $shift = Shift::findOrFail($id);



        $shift->update([


            'nama_shift' => $request->nama_shift,


            'jam_mulai' => $request->jam_mulai,


            'jam_selesai' => $request->jam_selesai


        ]);



        return back();

    }








    public function deleteShift($id)
    {

        Shift::findOrFail($id)->delete();


        return back();

    }



}