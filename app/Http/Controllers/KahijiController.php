<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;

class KahijiController extends Controller
{
    //
    public function index(Request $request)
    {
        
    }

    public function zplchartdirect(Request $request)
    {
        $dt = $request->all();

        $mode     = $dt['mode'];
        $str      = $dt['tanggal_start'];
        $stp      = $dt['tanggal_stop'];
        $sid      = $dt['siteid'];

        switch ($mode) {
        case 'packetlostratio-4g':
            //$server_address = "10.47.150.144";
            //$username = "rto_jkt";
              //$password = "H4n1FF4j@2019#";
            //$password = "PlyTpNZP";
            //$db_name = "capmon";
    
            //$link = mysqli_connect($server_address, $username, $password, $db_name) or die("Could not connect : " . mysqli_error($link));
            $start = $str;
            $stop = $stp;
            $siteid = $sid;
            $tgl = substr(str_replace("-", "", $start), 0, 6);
            $table = "raw_twamp_netsense_hourly";
            $table2 = "_old_raw_twamp_netsense_hourly";
    
            $sql_packetloss = \DB::connection("mysql144")->select("select concat(tanggal,' ',jam) as mydate,'packetloss' as tipedata,source_device_name,target_device_name,siteid,avg_two_way_packet_loss_ratio_percent as packet_loss,avg_two_way_delay_us as latency,avg_two_way_jitter_us as jitter
            from $table where tanggal >='" . $start . "' and tanggal <='" . $stop . "' and siteid='" . $siteid . "'
            order BY mydate ASC");

            //print_r($sql_packetloss);

            /*
            $query_packetloss4g = "select concat(tanggal,' ',jam) as mydate,'packetloss' as tipedata,source_device_name,target_device_name,siteid,avg_two_way_packet_loss_ratio_percent as packet_loss,avg_two_way_delay_us as latency,avg_two_way_jitter_us as jitter
                from $table where tanggal >='" . $start . "' and tanggal <='" . $stop . "' and siteid='" . $siteid . "'
                order BY mydate ASC
                ";
            */
                    /*$query_packetloss4g = "select concat(tanggal,' ',jam) as mydate,source_device_name,target_device_name,siteid,avg_two_way_packet_loss_ratio_percent as packet_loss
                        from $table where tanggal >='".$start."' and tanggal <='".$stop."' and siteid='".$siteid."'
                        union all
                        select concat(tanggal,' ',jam) as mydate,source_device_name,target_device_name,siteid,avg_two_way_packet_loss_ratio_percent as packet_loss
                        from $table2 where tanggal >='".$start."' and tanggal <='".$stop."' and siteid='".$siteid."'
                        ORDER BY mydate desc
                        ";*/
                    //echo $query_packetloss; 
            //$sql_packetloss = mysqli_query($link, $query_packetloss4g) or die(mysqli_error($link));
            $output = array();
            $objectname = array();
            //if (mysqli_num_rows($sql_packetloss) > 0) {
                foreach ($sql_packetloss as $sql_packetloss => $data) {
                //while ($data = mysqli_fetch_object($sql_packetloss)) {
                    date_default_timezone_set("UTC");
                    $point['latency'][] = strtotime($data->mydate) * 1000;
                    $point['latency'][] = $data->latency;
                    $point['packet_loss'][] = strtotime($data->mydate) * 1000;
                    $point['packet_loss'][] = $data->packet_loss;
                    $point['jitter'][] = strtotime($data->mydate) * 1000;
                    $point['jitter'][] = $data->jitter;
                    $object_name = $data->source_device_name . "_" . $data->siteid . "_" . $data->target_device_name . "_Latency";
                    $object_name_1 = $data->source_device_name . "_" . $data->siteid . "_" . $data->target_device_name . "_Packetloss";
                    $object_name_2 = $data->source_device_name . "_" . $data->siteid . "_" . $data->target_device_name . "_Jitter";
                    $series[$object_name]['latency'][] = $point['latency'];
                    $series[$object_name_1]['packet_loss'][] = $point['packet_loss'];
                    $series[$object_name_2]['jitter'][] = $point['jitter'];
                    $point = array();
                    $objectname[] = $object_name;
                    $objectname_1[] = $object_name_1;
                    $objectname_2[] = $object_name_2;
                    //$output[] = $data;
                }
            //}
            $output_iub = array();
            foreach (array_unique($objectname_1) as $nodename) {
                $output_packet_loss['name'] = $nodename;
                $output_packet_loss['data'] = $series[$nodename]['packet_loss'];
                $output_packet_loss['color'] = "#ff3a22";
                $output_packet_loss['yAxis'] = "0";
                $output_iub[] = $output_packet_loss;
            }
            foreach (array_unique($objectname) as $nodename) {
                $output_packet_loss['name'] = $nodename;
                $output_packet_loss['data'] = $series[$nodename]['latency'];
                $output_packet_loss['color'] = "#3C1874";
                $output_packet_loss['yAxis'] = "1";
                $output_iub[] = $output_packet_loss;
            }
            foreach (array_unique($objectname_2) as $nodename) {
                $output_packet_loss['name'] = $nodename;
                $output_packet_loss['data'] = $series[$nodename]['jitter'];
                $output_packet_loss['color'] = "#2e4600";
                $output_packet_loss['yAxis'] = "2";
                $output_iub[] = $output_packet_loss;
            }
            //echo json_encode($output);
            /*
            $output_data['data'] = $output;
            header("Content-type: application/json");
            $output['series'] = json_encode($output_iub, JSON_NUMERIC_CHECK);
            echo json_encode($output);
            break;
            */
            $output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'usagetrans-data':
            $mode     = $dt['mode'];
            $tanggal_start      = $dt['tanggal_start'];
            $tanggal_stop      = $dt['tanggal_stop'];
            $siteid   = $dt['siteid'];

            $sql = \DB::connection("mysql144")->select("SELECT CONCAT(tanggal,' ',jam) AS mydate, CONCAT(tipe,'-',NEName) AS object_name,SiteID, REPLACE(SUBSTRING(SUBSTRING_INDEX(NEName, '_', 2),LENGTH(SUBSTRING_INDEX(NEName, '_', 2 - 1)) + 1),'_', '') AS neid,
            CASE 
            WHEN RxMax > TxMax THEN RxMax
            WHEN RxMax < TxMax THEN TxMax
            WHEN RxMax = TxMax THEN RxMax
            END AS value FROM sum_traffic_hourly
            WHERE CONCAT(tanggal,' ',jam) >= '$tanggal_start 00:00:00' 
            AND  CONCAT(tanggal,' ',jam) <= '$tanggal_stop 23:00:00' 
            AND siteid='$siteid'
            order by mydate desc");

            $lanjut = true;
            $title = "Usage Transport  Max (RxMaxSpeed TxMaxSpeed) for " . $siteid . " from ($tanggal_start 00:00:00 - $tanggal_stop 23:00:00)";
            $axistitle = "Usage Transport";

            //echo $query_packetloss;
            $xaxis_sql = \DB::connection("mysql170")->select( "SELECT * FROM (
            SELECT DATE_FORMAT(now()-INTERVAL 3 DAY,'%Y-%m-%d 00:00:00')+INTERVAL a+b HOUR dte FROM (
            SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) d,(
            SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40 UNION SELECT 50 UNION SELECT 60 UNION SELECT 70 UNION SELECT 80 UNION SELECT 90) m ORDER BY a+b) aa 
            WHERE dte<=DATE_FORMAT(now()-INTERVAL 80 minute,'%Y-%m-%d %H:00:00')");
            foreach ($xaxis_sql as $xaxis_sql => $dt) {
            //while ($dt = mysqli_fetch_object($xaxis_sql)) {
                $xaxis[] = $dt->dte;
            }
            /*
            if ($_GET['mode'] == 'usagetrans-data') {
                $server_address = "10.47.150.144";
                $username = "rto_jkt";
                $password = "PlyTpNZP";
                $db_name = "capmon";
    
                $link = mysqli_connect($server_address, $username, $password, $db_name) or die("Could not connect : " . mysqli_error($link));
            }
            */
            //$sql = mysqli_query($link, $query); //or die(mysqli_error($link));
            //if ($sql) {
                $output = array();
                $objectname = array();
                //if (mysqli_num_rows($sql) > 0) {
                    //while ($data = mysqli_fetch_object($sql)) {
                    foreach ($sql as $sql => $data) {
                        date_default_timezone_set("UTC");
                        $point['parameter'][] = strtotime($data->mydate) * 1000;
                        $point['parameter'][] = $data->value;
                        //$object_name = $data->controller."-".$data->siteid."-".$data->cellname;
                        $object_name = $data->object_name;
                        $series[$object_name]['parameter'][] = $point['parameter'];
                        $point = array();
                        $objectname[] = $object_name;
                        $pointnya[$data->object_name][$data->mydate] = $data->value;
                        $xaxis_perjuangan[] = $data->mydate;
                        //$output[] = $data;
                    }
                //}
                $xaxis_per = array_unique($xaxis_perjuangan);
                $output_data_array = array();
                foreach (array_unique($objectname) as $nodename) {
                    $objnya[] = $nodename;
                    $output_data['name'] = $nodename;
                    $output_data['data'] = $series[$nodename]['parameter'];
                    $output_data_array[] = $output_data;
                }
                $custom_tanggal = false;
                array_unique($objnya);
                $out_baru_array = array();
                foreach ($objnya as $cell) {
                    $out_baru = array();
                    $data_apa = array();
                    if ($custom_tanggal == true) {
                        foreach ($xaxis_per as $a => $x) {
                            $apa = array();
                            $apa[] = strtotime($x) * 1000;
                            if (empty($pointnya[$cell][$x])) {
                                $apa[] = 0;
                            } else {
                                $apa[] = $pointnya[$cell][$x];
                            }
                            $data_apa[] = $apa;
                        }
                    } else {
                        foreach ($xaxis as $x) {
                            $apa = array();
                            $apa[] = strtotime($x) * 1000;
                            if (empty($pointnya[$cell][$x])) {
                                $apa[] = 0;
                            } else {
                                $apa[] = $pointnya[$cell][$x];
                            }
                            $data_apa[] = $apa;
                        }
                    }
    
                    $out_baru['name'] = $cell;
                    $out_baru['data'] = $data_apa;
                    $out_baru_array[] = $out_baru;
                }
                //            $output['uniq'] = $objnya;
                //            $output['uniq_time'] = $xaxis;
                //            $output['uniq_baru'] = json_encode($out_baru_array, JSON_NUMERIC_CHECK);
                //echo json_encode($output);
                $output_data['data'] = $output;
                //header("Content-type: application/json");
                //$output['series'] = json_encode($output_data_array, JSON_NUMERIC_CHECK);
                $output['test'] = $xaxis_per;
                $output['series'] = $out_baru_array;
                //$output['series'] = json_encode($out_baru_array, JSON_NUMERIC_CHECK);
                $output['chart_title'] = $title;
                $output['chart_axis'] = $axistitle;
                //$output['query'] = $query;
                $output['error'] = "";
                $output['status'] = true;
            /*
            } else {
                $output['error'] = mysqli_error($link);
                //$output['query'] = $query;
                $output['status'] = false;
            }
            echo json_encode($output);
            */
            return Core::setResponse("success",$output);

            break;
        }
    }
}
