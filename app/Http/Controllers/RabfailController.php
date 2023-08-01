<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RabfailController extends Controller
{
    public function index(Request $request)
    {
        $dt = $request->all();

        $mode     = $dt['mode'];
        $str      = $dt['start'];
        $stp      = $dt['stop'];

        if (!empty($str) && !empty($stp)) {
            $tanggalstart = date('Y-m-d', strtotime($str));
            $tanggalstop2 = date('Y-m-d', strtotime($stp));

            $tanggalstop = date('Y-m-d H:i:s', strtotime($stp));

            $condition = " and mydate >= ? and mydate <= ? ";
        } else {
            $tanggalstart = date('Y-m-d H:i');

            $condition = " and DATE(mydate) between DATE(DATE_SUB(NOW(), INTERVAL 2 DAY)) AND CURDATE() order by mydate";
        }

        $md = Carbon::now()->subDays(1);

        $minus_oneday = $md->toDateString();

        switch ($mode) {
            case "line-chart":

                if (!empty($str) && !empty($stp)) {
                    $doquery = \DB::connection("mysql")->select("SELECT * from minutes_4g_rabfail where satuan = 'jabar' $condition order by mydate ASC", ["$tanggalstart 00:00:00", "$tanggalstop2  23:59:00"]);
                } else {
                    $doquery = \DB::connection("mysql")->select("SELECT * from minutes_4g_rabfail where satuan = 'jabar' $condition order by mydate");
                }

                break;
            case "top-10";

                if (!empty($str) && !empty($stp)) {

                    $doquery = \DB::connection("mysql")->select("select * from minutes_4g_rabfail where satuan = 'jabar' $condition order by tot_fail desc limit 10", ["$tanggalstart 00:00:00", "$tanggalstop2  23:59:00"]);

                } else {

                    $doquery = \DB::connection("mysql")->select("select * from minutes_4g_rabfail where satuan = 'jabar' $condition order by tot_fail desc limit 10");

                }

                break;
            case "pie-chart":

                $doquery = \DB::connection("mysql")->select("select * from minutes_4g_rabfail where satuan = 'jabar' and mydate = ?", ["$tanggalstop"]);

                break;

            case "based-on-rtp":

                $category = $dt['category'];

                if ($category != '') {

                    $doquery = \DB::connection("mysql")->select("select * from minutes_4g_rabfail where satuan != 'jabar' and mydate = ? order by $category DESC", [$tanggalstop]);

                } else {

                    $doquery = \DB::connection("mysql")->select("select * from minutes_4g_rabfail where satuan != 'jabar' and mydate = ?", [$tanggalstop]);

                }
                break;
        }

        $value      = array();
        $counter    = 0;
        $str        = array();

        foreach ($doquery as $doquery => $key) {

            $timestamp = strtotime($key->mydate);
            $awalstr = $key->mydate;
            $output['mydate'][] = $key->mydate;
            $output['result'][] = $key;

            $output['tot_fail_with_date'][] = array($key->mydate, $key->tot_fail);
            $tot_rab_abs = $key->tot_rab_abs_cong + $key->tot_rab_abs_hofailure + $key->tot_rab_abs_mme + $key->tot_rab_abs_radio + $key->tot_rab_abs_tnl;

            $tot_rab_fail = $key->tot_rab_fail_est_mme + $key->tot_rab_fail_est_rnl + $key->tot_rab_fail_est_tnl;

            $str['tot_rab_fail'][] = array($awalstr, $tot_rab_fail);
            $str['tot_rab_fail_est_mme'][] = array($awalstr, $key->tot_rab_fail_est_mme);
            $str['tot_rab_fail_est_rnl'][] = array($awalstr, $key->tot_rab_fail_est_rnl);
            $str['tot_rab_fail_est_tnl'][] = array($awalstr, $key->tot_rab_fail_est_tnl);
        }

        $array_kolom2 = array('tot_rab_abs', 'tot_rab_abs_cong', 'tot_rab_abs_hofailure', 'tot_rab_abs_mme', 'tot_rab_abs_radio', 'tot_rab_abs_tnl');
        $array_kolom = array('tot_rab_fail', 'tot_rab_fail_est_mme', 'tot_rab_fail_est_rnl', 'tot_rab_fail_est_tnl');

        for ($i = 0; $i < count($array_kolom); $i++) {
            $series['name'] = $array_kolom[$i];
            $series['data'] = $str[$array_kolom[$i]];
            if ($array_kolom[$i] != 'tot_rab_fail' && $mode == 'pie-chart') {
                $series_pie['name'] = $array_kolom[$i];
                $series_pie['y'] = $str[$array_kolom[$i]][0][1];
                $output['series_pie'][] = $series_pie;
            } else {
                $output['series'][] = $series;
            }
        }

        return Core::setResponse("success",$mode,$output);
    }

    public function triDay(Request $request)
    {
        $rq = $request->all();

        $bl     = $rq['bulan'];
        $mode   = $rq['mode'];

        $month_t    = $bl . "-01";
        $month      = $bl;

        $date_akhir = date("t", strtotime($month_t));

        for ($i = 1; $i <= $date_akhir; $i += 3) {

            if (strlen($i) == 1) {
                $a = "0" . $i;
            } else {
                $a = $i;
            }

            $dte = $month . "-" . $a;

            $bb2 = date('Y-m-d', strtotime($dte. ' - 2 days'));

            $ee = $bb2;

            $cc = \DB::connection("mysql")->table('minutes_4g_rabfail')
                        ->select(\DB::connection("mysql")->raw('SUM(tot_rab_fail_est_mme) as tot_rab_fail_est_mme, SUM(tot_rab_fail_est_rnl) as tot_rab_fail_est_rnl, SUM(tot_rab_fail_est_tnl) as tot_rab_fail_est_tnl, SUM(tot_rab_fail_est_mme) + SUM(tot_rab_fail_est_rnl) + SUM(tot_rab_fail_est_tnl) AS tot_rab_fail, SUM(tot_fail) as tot_fail'))
                        ->whereBetween(\DB::connection("mysql")->raw("(DATE_FORMAT(mydate, '%Y-%m-%d'))"), [$ee, $dte])
                        ->get();

            $dd = array_values(get_object_vars($cc[0]));

            $awalstr = $dte;
            $str2['tot_rab_fail'][] = $dd[3];
            $str2['tot_rab_fail_est_mme'][] = $dd[0];
            $str2['tot_rab_fail_est_rnl'][] = $dd[1];
            $str2['tot_rab_fail_est_tnl'][] = $dd[2];
            $str2['tanggal'][] = date('d-m-Y', strtotime($awalstr));

            $output['tot_fail_with_date'][] = array("tgl" => $awalstr, "tot_fail" => $dd[4]);
        }

        usort($output['tot_fail_with_date'], function ($a, $b) {
            return $b['tot_fail'] <=> $a['tot_fail'];
        });

        $slic = array_slice($output['tot_fail_with_date'], 0, 10);

        $array_kolom2 = array('tot_rab_abs', 'tot_rab_abs_cong', 'tot_rab_abs_hofailure', 'tot_rab_abs_mme', 'tot_rab_abs_radio', 'tot_rab_abs_tnl');
        $array_kolom = array('tot_rab_fail', 'tot_rab_fail_est_mme', 'tot_rab_fail_est_rnl', 'tot_rab_fail_est_tnl');

        for ($i = 0; $i < count($array_kolom); $i++) {
            $series['name'] = $array_kolom[$i];
            $series['data'] = $str2[$array_kolom[$i]];
            $tanggal = $str2['tanggal'];
            if ($array_kolom[$i] != 'tot_rab_fail' && $mode == 'pie-chart') {
                $series_pie['name'] = $array_kolom[$i];
                $series_pie['y'] = $str[$array_kolom[$i]][0][1];
                $output['series_pie'][] = $series_pie;
            } else {
                $output['series'][] = $series;
                $output['tanggal'] = $tanggal;
                $output['tot_fail'] = $slic;
            }
        }

        return Core::setResponse("success",$mode,$output);

    }

    public function listRtp(Request $request)
    {
        $r_tgl = $request->tanggal;

        $tgl = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($r_tgl));
        $tanggal = date("Y-m-d", strtotime($tgl));

        $bln = date("Ym", strtotime($tgl));

        $query_tgl = \DB::connection("mysql")->table("monitoring_4g_rabfail_15mnit_$bln")
                        ->select('mydate')
                        ->where(\DB::connection("mysql")->raw("(DATE_FORMAT(mydate, '%Y-%m-%d'))"), "$tanggal")
                        ->orderBy("mydate", "asc")
                        ->limit(1)
                        ->get();

        $hasil = array_values(get_object_vars($query_tgl[0]));

        $query_rtpo = \DB::connection("mysql")->table("monitoring_4g_rabfail_15mnit_$bln AS a")
                            ->select('b.RTPO')
                            ->join("dapot_transport_new AS b","a.siteid", '=', 'b.Site_ID')
                            ->where('a.mydate',$hasil[0])
                            ->groupBy('b.RTPO')
                            ->get();

        $option = "<option value=''>Pilih TO</option>";
        foreach ($query_rtpo as $key => $d) {
            $option .= "<option value='".$d->RTPO."'>".$d->RTPO."</option>";
        }

        return Core::setResponse("success","List RTPO", $option);

    }

    public function list50(Request $request)
    {
        $r_tgl = $request->tanggal;

        $tgl = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($r_tgl));
        $tanggal = date("Y-m-d", strtotime($tgl));

        $bln = date("Ym", strtotime($tgl));

        $query_tnl = \DB::connection("mysql")->table("monitoring_4g_rabfail_15mnit_$bln")
                        ->select('siteid','sitename', \DB::connection("mysql")->raw('SUM(rab_fail_est_tnl) AS tnl, COUNT(siteid) AS jml_muncul'))
                        ->where(\DB::connection("mysql")->raw("(DATE_FORMAT(mydate, '%Y-%m-%d'))"), $tanggal)
                        ->groupBy('siteid', 'sitename')
                        ->orderBy("tnl", "desc")
                        ->limit(50)
                        ->get();

        $option_tnl = "";

        $no = 0;
        foreach ($query_tnl as $key => $data) {
            $no++;

            $siteid     = $data->siteid;
            $sitename   = $data->sitename;
            $tnl        = $data->tnl;
            $jml_muncul = $data->jml_muncul;

            $option_tnl .= "<tr>
                <td>$no</td>
                <td>$siteid</td>
                <td align='left'>$sitename</td>
                <td>$tnl</td>
                <td>$jml_muncul</td>
            </tr>";
        }

        $query_mme = \DB::connection("mysql")->table("monitoring_4g_rabfail_15mnit_$bln")
                        ->select('siteid','sitename', \DB::connection("mysql")->raw('SUM(rab_fail_est_mme) AS mme, COUNT(siteid) AS jml_muncul'))
                        ->where(\DB::connection("mysql")->raw("(DATE_FORMAT(mydate, '%Y-%m-%d'))"), $tanggal)
                        ->groupBy('siteid', 'sitename')
                        ->orderBy("mme", "desc")
                        ->limit(50)
                        ->get();

        $option_mme = "";

        $na = 0;
        foreach ($query_mme as $key => $data1) {
            $na++;

            $siteid     = $data1->siteid;
            $sitename   = $data1->sitename;
            $mme        = $data1->mme;
            $jml_muncul = $data1->jml_muncul;

            $option_mme .= "<tr>
                <td>$na</td>
                <td>$siteid</td>
                <td align='left'>$sitename</td>
                <td>$mme</td>
                <td>$jml_muncul</td>
            </tr>";
        }

        $arr = array("option_tnl" => $option_tnl, "option_mme" => $option_mme);

        return Core::setResponse("success","List 50", $arr);

    }

    public function exportRabfail(Request $request)
    {
        ini_set('memory_limit', '2048M');

        $r_tgl = $request->tanggal;
        $r_rtp = $request->rtp;

        $tgl        = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($r_tgl));
        $tanggal    = date("Y-m-d", strtotime($tgl));

        $rtp        = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($r_rtp));
        $bln        = date("Ym", strtotime($tgl));

        $result = \DB::connection("mysql")->select("SELECT @n := @n + 1 n,a.mydate, a.siteid, a.sitename, a.cellname, a.rab_fail_est_mme, a.rab_fail_est_rnl, a.rab_fail_est_tnl, b.RTPO FROM monitoring_4g_rabfail_15mnit_$bln AS a INNER JOIN dapot_transport_new AS b ON (a.siteid = b.Site_ID), (SELECT @n := 0) m WHERE b.RTPO = ? AND a.mydate BETWEEN ? AND ?", [$rtp, "$tanggal 00:00:00", "$tanggal 23:55:00"]);

        // $hsl = (object) $result;

        return Core::setResponse("success","Export Rabfail", array("result" => $result));
    }

    public function detailRTP(Request $request)
    {
        $r_tgl = $request->tanggal;
        $r_rtp = $request->rtp;
        $r_cat = $request->category;

        $tanggal    = date('Y-m-d H:i:s', strtotime($r_tgl));

        $rt     = $r_rtp;

        if ($rt == 'is null') {
            $rtp = "b.RTPO $rt";
        } else {
            $rtp = "b.RTPO like ?";
        }

        $category = $r_cat;

        if ($category != 'tot_fail') {
            $new_category   = substr($category, 4);
            $condition2     = "and " . $new_category . " > 0";
        } else {
            $condition2     = "";
            $new_category   = "";
        }

        list($tagl, $jasma) = explode(' ', $tanggal);
        list($y, $m, $d)    = explode('-', $tagl);
        $time   = mktime(0, 0, 0, $m, $d, $y);
        $bulan  =  date('Ym', $time);

        if ($rt == 'is null') {
            $isi = [$tanggal];
        } else {
            $isi = ["%$rt", $tanggal];
        }

        $doquery1 = \DB::connection("mysql")->select("SELECT a.mydate, a.siteid, a.neid, a.cellname, a.rab_abs_cong, a.rab_abs_hofailure, a.rab_abs_mme, a.rab_abs_radio, a.rab_abs_tnl, a.rab_fail_est_mme, a.rab_fail_est_rnl, a.rab_fail_est_tnl, b.RTPO
                FROM monitoring_4g_rabfail_15mnit_$bulan AS a
                LEFT JOIN dapot_transport_new AS b ON (a.siteid = b.Site_ID)
                WHERE $rtp
                and a.mydate = ?
                $condition2
            ", $isi);

        foreach($doquery1 as $object)
        {
            $doquery[] = (array) $object;
        }

        $value      = array();
        $counter    = 0;
        $str        = array();

        foreach ($doquery as $key => $data) {

            // unset($data['sitename']);

            $timestamp  = strtotime($data['mydate']);
            $awalstr    = 1000 * $timestamp + (3600 * 7 * 1000);

            $output['mydate'][] = $data['mydate'];
            // $output['result'][] = $data;
            $output['result'][] = [ "0" => $data['mydate'], "1" => $data['siteid'], "2" => $data['neid'], "3" => $data['cellname'], "4" => $data['rab_abs_cong'], "5" => $data['rab_abs_hofailure'], "6" => $data['rab_abs_mme'], "7" => $data['rab_abs_radio'], "8" => $data['rab_abs_tnl'], "9" => $data['rab_fail_est_mme'], "10" => $data['rab_fail_est_rnl'], "11" => $data['rab_fail_est_tnl'], "12" => $data['RTPO']];
        }

        $output['new_category'] = $new_category;

        return Core::setResponse("success", "Detail RTPO", $output);
    }

}
