<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{

    public function dashboard()
    {
        $kendaraan = Car::get();
        $lokasi = Location::all();

        // Proses dekripsi untuk setiap lokasi
        foreach ($lokasi as $location) {
            $location->latitude = $this->decrypt($location->latitude);
            $location->longitude = $this->decrypt($location->longitude);
        }

        // Kirim data ke view 'dashboard'
        return view('dashboard', compact('kendaraan', 'lokasi'));
    }

    private function decrypt($encryptedText)
    {
        // 1. Dekripsi XOR
        $decryptedXor = $this->xorDecrypt($encryptedText, 'O');
        // 2. Dekripsi biner
        $decryptedBinary = $this->fromBinary($decryptedXor);
        // 3. Dekripsi Substitusi Peta
        $decryptedMap = $this->substituteMapDecrypt($decryptedBinary);
        // 4. Dekripsi Caesar Cipher
        $decryptedCaesar = $this->caesarCipherDecrypt($decryptedMap);
        return $decryptedCaesar;
    }

    private function xorDecrypt($binaryText, $key)
    {
        $result = "";
        for ($i = 0; $i < strlen($binaryText); $i++) {
            $result .= ($binaryText[$i] == '1') ^ ((ord($key) >> ($i % 8)) & 1) ? '1' : '0';
        }
    return $result;
    }

    private function fromBinary($binaryText)
    {
        $text = "";
        for ($i = 0; $i < strlen($binaryText); $i += 8) {
            $char = 0;
            for ($j = 0; $j < 8; $j++) {
                $char |= ($binaryText[$i + $j] == '1' ? 1 : 0) << (7 - $j);
            }
            $text .= chr($char);
        }
        return $text;
    }

    private function substituteMapDecrypt($text)
    {
        $result = "";

        for ($i = 0; $i < strlen($text); $i++) {
            $c = $text[$i];
            if ($c == '-') {
                $result .= '-'; // Tambahkan tanda negatif tanpa enkripsi
            } else if ($c == '.') {
                $result .= '.'; // Tambahkan titik desimal tanpa enkripsi
            } else if (ctype_alpha($c)) {
                $digit = ord($c) - ord('A');
                $result .= strval($digit); // Dekripsi angka A-J menjadi 0-9
            } else {
                $result .= $c; // Tambahkan karakter lain tanpa enkripsi
            }
        }
        return $result;
    }

    private function caesarCipherDecrypt($text)
    {
        $result = "";
        for ($i = 0; $i < strlen($text); $i++) {
            $c = $text[$i];
            if (ctype_digit($c)) {
                // Geser karakter numerik dan tetap dalam angka 0-9
                $c = (ord($c) - ord('0') - 3 + 10) % 10 + ord('0'); 
                $result .= chr($c);
            } else {
                // Tambahkan karakter lain tanpa enkripsi
                $result .= $c; 
            }
        }
        return $result;
    }

    public function create(){
        return view('kendaraan/create_kendaraan');
    }

    public function store(Request $request){
        // Validasi input
        $validator = Validator::make($request->all(),[
            'nopol' => 'required',
            'jenisKendaraan' => 'required',
            'tahun' => 'required',
        ]);

        // Jika validasi gagal, kembali ke halaman sebelumnya dengan pesan kesalahan
        if($validator->fails()) return redirect()->back()->withInput()->withErrors($validator);

        // Buat objek kendaraan baru
        $kendaraan['nopol'] = $request->nopol;
        $kendaraan['jenisKendaraan'] = $request->jenisKendaraan;
        $kendaraan['tahun'] = $request->tahun;

        // Simpan objek kendaraan baru
        Car::create($kendaraan);

        // Redirect kembali ke dashboard dengan pesan sukses
        return redirect()->route('admin.dashboard');
    }


    public function edit(Request $request,$nopol){
        $kendaraan = Car::find($nopol);

        return view('kendaraan/edit_kendaraan',compact('kendaraan'));
    }

    public function update(Request $request,$nopol){
        $validator = Validator::make($request->all(), [
            'nopol' => 'required',
            'jenisKendaraan' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // Temukan data_driver berdasarkan ID
        $kendaraan = Car::find($nopol);

        // Perbarui nilai kolom dengan data yang diterima dari form
        $kendaraan->nopol = $request->nopol;
        $kendaraan->jenisKendaraan = $request->jenisKendaraan;
        $kendaraan->tahun = $request->tahun;


        // Simpan perubahan
        $kendaraan->save();
        return redirect()->route('admin.dashboard');

    }

    public function delete(Request $request,$nopol){
        $kendaraan = Car::find($nopol);

        if($kendaraan){
             $kendaraan->delete();
        }

        return redirect()->route('admin.dashboard');
    }

}


