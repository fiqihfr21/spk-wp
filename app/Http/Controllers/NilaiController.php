<?php

namespace App\Http\Controllers;

use App\CPenerima;
use App\Kriteria;
use App\Nilai;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(['auth','admin']);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($penerima)
    {
        $penerimaObj = CPenerima::findOrFail($penerima);
        $kriteria = Kriteria::all();
        $data = [];
        foreach ($penerimaObj->nilai as $item) {
            $data[$item->kriteria_id] = $item->nilai;
        }
        return view('nilai.create', [
            'penerima' => $penerimaObj,
            'kriteria' => $kriteria,
            'menu' => 'cpenerima' ,
            'data' => $data,
            'title' => 'Kriteria nilai ' . $penerimaObj->nama]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $penerima = $request->input('penerima');
        $nilai = $request->input('kriteria');
        $kriteria = Kriteria::all();

        foreach ($kriteria as $k){
            if($nilai[$k->id]!=null){
                if(!$this->chekRecordIsExist($penerima, $k->id)){
                    $in_nilai = new Nilai();
                    $in_nilai->cpenerima_id = $penerima;
                    $in_nilai->kriteria_id = $k->id;
                    $in_nilai->nilai = $nilai[$k->id];
                    if(!$in_nilai->save()){
                        break;
                        $request->session()->flash('error', "Failed to save nilai");
                    }
                }
            }
        }
        return redirect('cpenerima');
    }

    private function chekRecordIsExist($cpenerima, $kriteria){
        return Nilai::where('cpenerima_id', '=', $cpenerima)
                        ->where('kriteria_id','=', $kriteria)
                        ->exists();
    }

    
}
