<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use App\Kriteria;
use App\Nilai;

class WPGenerator
{
    public static function weight_product(){

        //menjumlahkan bobot nilai kriteria
        $wj = DB::table('kriteria')->sum('bobot');
        //query mengambil nilai data-data kriteria 
        $kriteria = Kriteria::all();
        //inisialisasi array dari kriteria 'W'
        $weight = [];
        //membagi masing-masing bobot dengan total jumlah bobot
        foreach ($kriteria as $k){
            $weight[$k->id] = $k->bobot/$wj;
        }

        //query mengurutkan penerima 
        $nilai = Nilai::orderBy('cpenerima_id')->get();
        //inisialisasi null penerima
        $penerima = null;
        //inisialisasi array 'S'
        $s = [];

        //inisialisasi 1 tmp_s
        $tmp_s = 1;
        //inisialisasi 0 hitungan
        $hit = 0;
        //inisialisasi length dengan menghitung banyaknya nilai 
        $len = count($nilai);
        //menghitung nilai preferensi S untuk tiap-tiap alternatif
        foreach ($nilai as $n) {
            //mengecek masing2 penerima
            if($penerima != $n->cpenerima_id){
                if($penerima != null){
                    $tmp = [];
                    $tmp['s'] = $tmp_s;
                    $tmp['penerima'] = $penerima;
                    array_push($s, $tmp);
                }
                //mengambil nilai dari id calon penerima 
                $penerima = $n->cpenerima_id;
                //inisialisasi 1 kembali ke tmp_s
                $tmp_s = 1;
            }
            //mengecek kriteria biaya/cost, apabila iya maka akan dikalikan dengan pangkat negatif
            if($n->kriteria->atribut == Kriteria::COST){
                if(array_key_exists($n->kriteria_id, $weight)){
                    $weight[$n->kriteria_id] = ($weight[$n->kriteria_id] > 0) ? $weight[$n->kriteria_id] * -1 : $weight[$n->kriteria_id];
                }
            }
            //mengalikan masing2 nilai dari kriteria
            $tmp_s *= pow($n->nilai,$weight[$n->kriteria_id]);
            //mengecek hitungan jika sama dengan banyaknya nilai dikurang 1
            if($hit == $len-1){
                $tmp = [];
                $tmp['s'] = $tmp_s;
                $tmp['penerima'] = $penerima;
                array_push($s,$tmp);
            }
            $hit++;
        }
        //menginisialisasi 0 vj
        $vj=0;
        //menjumlahkan nilai preferensi S
        foreach ($s as $single_s){
            $vj += $single_s['s'];
        }
        //inisialisasi vektor V
        $v = [];
        //membagi masing-masing bobot dengan total jumlah nilai preferensi S
        foreach ($s as $single_s){
            $v[$single_s['penerima']] = $single_s['s']/$vj;
        }
        //mengembalikan nilai w ke variabel weight, nilai s ke variabel s, nilai v ke variabel v
        return [
            'weight' => $weight,
            's' => $s,
            'v' => $v
        ];
    }
}