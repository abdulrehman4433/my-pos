<?php

function format_uang ($angka) {
    return number_format($angka, 0, ',', '.');
}

function terbilang($angka)
{
    $angka = abs($angka);
    $baca = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
    ];

    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    if ($angka < 20) {
        $terbilang = ' ' . $baca[$angka];
    } elseif ($angka < 100) {
        $terbilang = ' ' . $tens[(int)($angka / 10)];
        if ($angka % 10 !== 0) {
            $terbilang .= ' ' . $baca[$angka % 10];
        }
    } elseif ($angka < 1000) {
        $terbilang = ' ' . $baca[(int)($angka / 100)] . ' Hundred';
        if ($angka % 100 !== 0) {
            $terbilang .= ' and' . terbilang($angka % 100);
        }
    } elseif ($angka < 1000000) {
        $terbilang = terbilang((int)($angka / 1000)) . ' Thousand';
        if ($angka % 1000 !== 0) {
            $terbilang .= terbilang($angka % 1000);
        }
    } elseif ($angka < 1000000000) {
        $terbilang = terbilang((int)($angka / 1000000)) . ' Million';
        if ($angka % 1000000 !== 0) {
            $terbilang .= terbilang($angka % 1000000);
        }
    } elseif ($angka < 1000000000000) {
        $terbilang = terbilang((int)($angka / 1000000000)) . ' Billion';
        if ($angka % 1000000000 !== 0) {
            $terbilang .= terbilang($angka % 1000000000);
        }
    } else {
        $terbilang = 'Number is too large to convert.';
    }

    return $terbilang;
}
// visit "codeastro" for more projects!
function tanggal_indonesia($tgl, $tampil_hari = true)
{
    $nama_hari  = array(
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
    );
    $nama_bulan = array(1 =>
        'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
    );

    $tahun   = substr($tgl, 0, 4);
    $bulan   = $nama_bulan[(int) substr($tgl, 5, 2)];
    $tanggal = substr($tgl, 8, 2);
    $text    = '';

    if ($tampil_hari) {
        $urutan_hari = date('w', mktime(0,0,0, substr($tgl, 5, 2), $tanggal, $tahun));
        $hari        = $nama_hari[$urutan_hari];
        $text       .= "$hari, $tanggal $bulan $tahun";
    } else {
        $text       .= "$tanggal $bulan $tahun";
    }
    
    return $text; 
}
// visit "codeastro" for more projects!
function tambah_nol_didepan($value, $threshold = null)
{
    return sprintf("%0". $threshold . "s", $value);
}