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

    public function packetlossperhubmetro(Request $request)
    {
        $dt = $request->all();

        //$tanggal     = $dt['tanggal'];
        //$jam      = $dt['jam'];

        $output = array();
        // ambil hub nya saja
        if(isset($dt['tanggal']) && isset($dt['jam'])){
            /*
            $tanggal    = $_GET['tanggal'];
            $jam        = $_GET['jam'];
            */
            $tanggal    = $dt['tanggal'];
            $jam        = $dt['jam'];
            $doquery = \DB::connection("mysql170")->select("SELECT concat(tanggal,' ',jam) AS tanggal,hub AS metro_hub,count(*) AS jml_packetloss
            FROM hourly_monitoring_packetloss4g
            WHERE packetloss_r = 'CONSEC'
            AND CONCAT(tanggal,' ',jam) = '".$tanggal." ".$jam."'
            GROUP BY hub,tanggal,jam
            order by jml_packetloss desc
            ");
        }else{
            $doquery = \DB::connection("mysql170")->select("SELECT concat(tanggal,' ',jam) AS tanggal,hub AS metro_hub,count(*) AS jml_packetloss
            FROM hourly_monitoring_packetloss4g
            WHERE packetloss_r = 'CONSEC'
                and concat(tanggal,' ',jam) = 
                (select concat(tanggal,' ',jam) AS tanggal FROM 
                hourly_monitoring_packetloss4g GROUP BY tanggal,jam ORDER BY tanggal desc LIMIT 1)
            GROUP BY hub,tanggal,jam
            order by jml_packetloss desc 
            ");
        }
        //$doquery = mysqli_query($link, $query) or die(mysqli_error($link));
        $count = 0;
        $counter = 0;
        //while($data = mysqli_fetch_object($doquery)){
        foreach ($doquery as $doquery => $data) {
            $counter++;
            if($count <=20){
                $metros[] = $data->metro_hub;
            }
            $out['counter'] = $counter;
            $out['hub'] = $data->metro_hub;
            $out['packetloss'] = $data->jml_packetloss;
            $data_out[] = $out;
            $count++;
        }
        $output['category'] = $metros;
        $output['table'] = $data_out;
        // ambil data rnc dan value nya
        if(isset($dt['tanggal']) && isset($dt['jam'])){
            /*
            $tanggal    = $_GET['tanggal'];
            $jam        = $_GET['jam'];
            */
            $tanggal    = $dt['tanggal'];
            $jam        = $dt['jam'];
            $doquery = \DB::connection("mysql170")->select("SELECT concat(tanggal,' ',jam) AS tanggal,hub AS metro_hub, backhaul_4g AS rnc,count(*) AS jml_packetloss
            FROM hourly_monitoring_packetloss4g
            WHERE packetloss > 0.1 
                AND tanggal = '".$tanggal."' AND jam = '".$jam."'
            GROUP BY tanggal,jam,hub,backhaul_4g
            order by jml_packetloss desc
            ");
        }else{
            $doquery = \DB::connection("mysql170")->select("SELECT concat(tanggal,' ',jam) AS tanggal,hub AS metro_hub, backhaul_4g AS rnc,count(*) AS jml_packetloss
            FROM hourly_monitoring_packetloss4g
            WHERE packetloss > 0.1 
                and concat(tanggal,' ',jam) = (
                select concat(tanggal,' ',jam) AS tanggal FROM hourly_monitoring_packetloss4g order by tanggal desc  LIMIT 1)
            GROUP BY tanggal,jam,hub,backhaul_4g
            order by jml_packetloss desc
            ");
        }
        //$doquery = mysqli_query($link ,$query) or die(mysqli_error($link));

        //while($data = mysqli_fetch_object($doquery)){
        foreach ($doquery as $doquery => $data) {
            $packetloss[$data->metro_hub][$data->rnc] = $data->jml_packetloss;
            $rncs[] = $data->rnc;
            $tanggal = $data->tanggal;
        }
        $rncs = array_unique($rncs);
        foreach($rncs as $key => $val){
            $rncz[] = $val;
        }
        foreach($rncz as $rnc){
            $str = array();
            $str['name'] = $rnc;
            foreach($metros as $metro){
                if(empty($packetloss[$metro][$rnc])){
                    $iub = 0;
                }else{
                    $iub = $packetloss[$metro][$rnc];
                }
                $str['data'][] = $iub;
            }
            $series[] = $str;
        }

        $output['series'] = $series;
        $output['tanggal'] = $tanggal;
        //header("Content-type: application/json");
        //echo json_encode($output, JSON_NUMERIC_CHECK);
        return Core::setResponse("success",$output);
        //break;
    }

    public function hourlymonitoringpacketloss(Request $request)
    {
        $dt = $request->all();
        date_default_timezone_set("Asia/Jakarta");
          
        if(isset($dt['start'])){
            $tanggalstart = $dt['start'];
            $tanggalstop = $dt['stop'];
            $condition = " where  nsa is not null and date(DATETIME) BETWEEN '".$tanggalstart."' AND '".$tanggalstop."'				
                            ORDER BY DATETIME ASC,nsa ASC,rtp ASC";
        }else{
            $condition = " WHERE nsa is not null and date(DATETIME) between DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) AND CURDATE()  
                            ORDER BY DATETIME ASC,nsa ASC,rtp ASC";	
        }
      
        $doquery = \DB::connection("mysql170")->select( "
            SELECT *
            FROM hourly_monitoring_packetloss4g_rtp
            ".$condition);
    //    $query = "
    //		select tanggal,nsa,rtp,consec 
    //		from hourly_monitoring_packetloss_rtp".$condition;
    //		where DATE(tanggal) between DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) AND CURDATE()
    //	";
        
        //$doquery = mysqli_query($link, $query) or die(mysqli_error($link));
        $value = array();
        $counter = 0;
        //while($data = mysqli_fetch_object($doquery)){
        foreach ($doquery as $doquery => $data) {
            if(!empty($data->rtp)){
                $value[$counter]['consec'] = $data->consec;
                $value[$counter]['nsa'] = $data->nsa;
                $value[$counter]['rtp'] = $data->rtp;
                $value[$counter]['datetime'] = $data->datetime;
                $rtp[] = $data->rtp;
                $tanggal[] = $data->datetime;
                $counter++;
            }        
        }
        
        $tanggal = array_unique($tanggal);
        $lastseries = count($tanggal) -1;
        sort($tanggal);
        $rtpo = array_unique($rtp);
        $index = 0;
        foreach($rtpo as $key => $rtp){
            $output2[$index]['name'] = $rtp;
            $index2 = 0;
            foreach($value as $key => $values){
                if($rtp == $values['rtp']){
                    $output2[$index]['data'][$index2] = $values['consec'];
                    $index2++;
                }
            }
            $index++;
        }
        $tot = 0;
        $i = 0;
    //    echo "<pre>";
    //    print_r($output2);
    //    echo "</pre>";
        foreach($rtpo as $key => $rtp){
            if(!empty($output2[$i]['data'][$lastseries])){
                $tot = $tot + $output2[$i]['data'][$lastseries];
            }
            $i++;
        }
    //    $output3['temp'] = $output2;
        $output3['category'] = $tanggal;
        $output3['series'] = $output2;
        $output3['total_last'] = $tot;
        
        //$output3['query'] = $query;
        //mysqli_close($link);
        return Core::setResponse("success",$output3);
    }

    public function alarmdown(Request $request)
    {
        $dt = $request->all();

        $mode     = $dt['mode'];
        //$str      = $dt['tanggal_start'];
        //$stp      = $dt['tanggal_stop'];
        //$sid      = $dt['siteid'];

        switch ($mode) {
        case 'query_weeks_years':
            $output = \DB::connection("mysql222")->select("SELECT MAX(weeks_data) AS weeks, MAX(years_data) AS years
            FROM 16010754_dapot_sites.dapot_site
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'text_class':
            $output = \DB::connection("mysql222")->select("SELECT tab.*,
            100 * ( 1 - (tab.down / tab.total_all)) AS total_avail,
            100 * ( 1 - (tab.down_2g / tab.total_2g)) AS total_avail_2g,
            100 * ( 1 - (tab.down_3g / tab.total_3g)) AS total_avail_3g,
            100 * ( 1 - (tab.down_4g / tab.total_4g)) AS total_avail_4g,
            100 * ( 1 - ((tab.down_platinum_2g + tab.down_platinum_3g + tab.down_platinum_4g) / tab.total_platinum)) AS total_avail_platinum,
            100 * ( 1 - ((tab.down_gold_2g + tab.down_gold_3g + tab.down_gold_4g) / tab.total_gold)) AS total_avail_gold,
            100 * ( 1 - ((tab.down_silver_2g + tab.down_silver_3g + tab.down_silver_4g) / tab.total_silver)) AS total_avail_silver,
            100 * ( 1 - ((tab.down_bronze_2g + tab.down_bronze_3g + tab.down_bronze_4g) / tab.total_bronze)) AS total_avail_bronze
    
        FROM(	
            SELECT a.*,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '2G'
                AND LOWER(a1.class_revenue) = 'platinum'
            ) AS down_platinum_2g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '3G'
                AND LOWER(a1.class_revenue) = 'platinum'
            ) AS down_platinum_3g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '4G'
                AND LOWER(a1.class_revenue) = 'platinum'
            ) AS down_platinum_4g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND LOWER(a1.class_revenue) = 'platinum'
            ) AS down_platinum,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '2G'
                AND LOWER(a1.class_revenue) = 'gold'
            ) AS down_gold_2g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '3G'
                AND LOWER(a1.class_revenue) = 'gold'
            ) AS down_gold_3g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '4G'
                AND LOWER(a1.class_revenue) = 'gold'
            ) AS down_gold_4g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND LOWER(a1.class_revenue) = 'gold'
            ) AS down_gold,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '2G'
                AND LOWER(a1.class_revenue) = 'silver'
            ) AS down_silver_2g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '3G'
                AND LOWER(a1.class_revenue) = 'silver'
            ) AS down_silver_3g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '4G'
                AND LOWER(a1.class_revenue) = 'silver'
            ) AS down_silver_4g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND LOWER(a1.class_revenue) = 'silver'
            ) AS down_silver,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '2G'
                AND LOWER(a1.class_revenue) = 'bronze'
            ) AS down_bronze_2g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '3G'
                AND LOWER(a1.class_revenue) = 'bronze'
            ) AS down_bronze_3g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '4G'
                AND LOWER(a1.class_revenue) = 'bronze'
            ) AS down_bronze_4g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND LOWER(a1.class_revenue) = 'bronze'
            ) AS down_bronze,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band != 'EAS'
            ) AS down,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '2G'
            ) AS down_2g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '3G'
            ) AS down_3g,
            (
                SELECT COUNT(*) 
                FROM 85152_trafficability.ran_alarm a1
                WHERE a1.rtp = a.rtp
                AND a1.band = '4G'
            ) AS down_4g
            FROM 16010754_dapot_site.resume_dapotcell a
        ) AS tab
        ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'count_2g':
            $output = \DB::connection("mysql222")->select("SELECT *
            FROM ran_alarm a
            WHERE a.band = '2G'
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'count_3g':
            $output = \DB::connection("mysql222")->select("SELECT *
            FROM ran_alarm a
            WHERE a.band = '3G'
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'count_4g':
            $output = \DB::connection("mysql222")->select("SELECT *
            FROM ran_alarm a
            WHERE a.band = '4G'
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        }
    }

    public function alarmeas(Request $request)
    {
        $dt = $request->all();

        $mode     = $dt['mode'];
        //$str      = $dt['tanggal_start'];
        //$stp      = $dt['tanggal_stop'];
        //$sid      = $dt['siteid'];

        switch ($mode) {
        case 'text_class':
            $output = \DB::connection("mysql222")->select("select nsa,rtp,string_alarm,code,count(code) as jml_alarm from (
                select concat('alarm',substr(string_alarm,2,5)) as code,string_alarm,siteid,neid,sitename,mydatetime,band,nsa,rtp from ran_alarm 
                where band='EAS' or band='EAS_BOARD' order by string_alarm
                ) aa
                where nsa is not null
                group by rtp,code
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'ajaxeas':
            $dt = $request->all();
            $opsi1 =  $dt['opsi1'];
            $opsi2 =  $dt['opsi2'];
            $opsi3 =  $dt['opsi3'];
            $array_opsi1 = array('nsa', 'rtp', 'all');
            if ($opsi1 <> '' || $opsi2 <> '' || $opsi3 <> '') {
            $code = str_replace("alarm", "", $opsi3);
            if ($opsi1 == 'nsa' && $opsi2 <> 'all') {
                $tambahan = "and nsa='" . $opsi2 . "'";
            } elseif ($opsi1 == 'rtp' && $opsi2 <> 'all') {
                $tambahan = "and rtp='" . $opsi2 . "'";
            } else {
                $tambahan = '';
            }
            $query = \DB::connection("mysql222")->select("select a.*,TIMESTAMPDIFF(MINUTE, mydatetime, now()) as duration_min, (@curRank := @curRank + 1) AS rank from ran_alarm a,(SELECT @curRank := 0) c where (band='EAS' or band='EAS_BOARD') and string_alarm like '%" . $code . "%' " . $tambahan . " order by mydatetime desc");
            //$query = mysqli_query($link, $text) or die(mysqli_error($link));

            $output_array = array();
            //while ($result = mysqli_fetch_object($query)) {
            foreach ($query as $query => $result) {
                $output_array[] = $result;
                $output2['data'][] = $result;
            }
            $output = array();
            $output['query'] = $query;
            $output['output_array'] = $output_array;
            $output['output_json'] = json_encode($output_array);
            //echo json_encode($output2);
            //$output['series'] = $output_iub;

            return Core::setResponse("success",$output2);
            }
            break;
        }
    }

    public function alarmlocked(Request $request)
    {
        $dt = $request->all();

        $mode     = $dt['mode'];
        //$str      = $dt['tanggal_start'];
        //$stp      = $dt['tanggal_stop'];
        //$sid      = $dt['siteid'];

        switch ($mode) {
        case 'query1':  
            $output = \DB::connection("mysql222")->select("select distinct(rtp) as rtp,nsa from 16010754_dapot_site.dapot_site
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'ajaxeas':
            $dt = $request->all();
            $opsi1 =  $dt['opsi1'];
            $opsi2 =  $dt['opsi2'];
            $opsi3 =  $dt['opsi3'];
            $array_opsi1 = array('nsa', 'rtp', 'all');
            if ($opsi1 <> '' || $opsi2 <> '' || $opsi3 <> '') {
            $code = str_replace("alarm", "", $opsi3);
            if ($opsi1 == 'nsa' && $opsi2 <> 'all') {
                $tambahan = "and nsa='" . $opsi2 . "'";
            } elseif ($opsi1 == 'rtp' && $opsi2 <> 'all') {
                $tambahan = "and rtp='" . $opsi2 . "'";
            } else {
                $tambahan = '';
            }
            $query = \DB::connection("mysql222")->select("select a.*,TIMESTAMPDIFF(MINUTE, mydatetime, now()) as duration_min, (@curRank := @curRank + 1) AS rank from ran_alarm a,(SELECT @curRank := 0) c where (band='EAS' or band='EAS_BOARD') and string_alarm like '%" . $code . "%' " . $tambahan . " order by mydatetime desc");
            //$query = mysqli_query($link, $text) or die(mysqli_error($link));

            $output_array = array();
            //while ($result = mysqli_fetch_object($query)) {
            foreach ($query as $query => $result) {
                $output_array[] = $result;
                $output2['data'][] = $result;
            }
            $output = array();
            $output['query'] = $query;
            $output['output_array'] = $output_array;
            $output['output_json'] = json_encode($output_array);
            //echo json_encode($output2);
            //$output['series'] = $output_iub;

            return Core::setResponse("success",$output2);
            }
            break;
        }
    }

    public function dapotranneweekly(Request $request)
    {
        
        $dt = $request->all();

        $mode     = $dt['mode'];

        function db_get_last_update_year_query($table)
        {
            $q = \DB::connection("mysql222a")->select("
                SELECT years AS last_update
                FROM " . $table . "
                GROUP BY years
                ORDER BY years DESC
                LIMIT 1
            ");
            
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        function db_get_last_update_week_query_with_year($table, $year)
        {

            $q = \DB::connection("mysql222a")->select("
                SELECT weeks AS last_update
                FROM " . $table . "
                WHERE years = $year
                GROUP BY weeks
                ORDER BY weeks DESC
                LIMIT 1
            ");
            //$q = mysqli_query($conn, $query);
            //$r = mysqli_fetch_object($q);
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        $year = db_get_last_update_year_query('dapot_ran_ne');
        $week = db_get_last_update_week_query_with_year('dapot_ran_ne', $year);

        switch ($mode) {
        case 'query':  
            $output = \DB::connection("mysql222a")->select("SELECT *,
            (
                SELECT COUNT(*)
                FROM dapot_ran_resume_ne a1
                WHERE a1.weeks = a.weeks
                AND a1.years = a.years
                AND a1.nsa = a.nsa
            ) AS count_nsa  
            FROM dapot_ran_resume_ne a
            WHERE weeks = '" . $week . "'
            AND years = '" . $year . "'
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'query22':
            $output = \DB::connection("mysql222a")->select("SELECT DISTINCT nsa
            FROM dapot_ran_resume_ne a
            WHERE weeks = '" . $week . "' AND years = '" . $year . "'
            ORDER BY nsa asc");

            return Core::setResponse("success",$output);
            break;
        case 'query_2':
            $output = \DB::connection("mysql222a")->select("SELECT *, 
                (
                    SELECT COUNT(*) 
                    FROM ran_cluster_rtp a1
                    JOIN dapot_ran_ne b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.id_nsa = a.id_nsa
                    AND b1.id_ran_dapot_category IN ($res->id_ran_dapot_category, 3)
                    AND weeks = $week
                    AND years = $year
                    AND IF($week < 6, a1.start_week BETWEEN 1 AND 5, a1.start_week BETWEEN 6 AND 6) 
                ) AS count_rtpo
            FROM nsa a
            WHERE active = 1
            AND IF($week < 6, start_week BETWEEN 1 AND 5, start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query_detail':
            $output = \DB::connection("mysql222a")->select("SELECT *, a.sum_site_wow as wow_site, a.sum_2g_gsm_wow as wow_2g_gsm, a.sum_2g_dcs_wow as wow_2g_dcs, a.sum_3g_f1_wow as wow_3g_f1,  a.sum_3g_f2_wow as wow_3g_f2,  a.sum_3g_f3_wow as wow_3g_f3,  a.sum_3g_f4_wow as wow_3g_f4, a.sum_multisector_f1_wow as wow_multisector_f1, a.sum_multisector_f2_wow as wow_multisector_f2, a.sum_multisector_f3_wow as wow_multisector_f3, a.sum_lte_e_nodeB_wow as wow_lte_e_nodeB, a.sum_nr_e_nodeB_wow as wow_nr_e_nodeB
            FROM dapot_ran_ne a
            JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp) 
            JOIN ran_dapot_category c ON (a.id_ran_dapot_category = c.id_ran_dapot_category)
            WHERE b.id_ran_dapot_category = '" . $res->id_ran_dapot_category . "' 
            AND weeks = $week
            AND years = $year
            AND b.id_nsa = '" . $result->id_nsa . "'
            AND b.active = 1
            AND IF($week < 6, b.start_week BETWEEN 1 AND 5, b.start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query2':
            $output = \DB::connection("mysql222a")->select("select count(name_ran_cluster_rtp) as jml FROM ran_cluster_rtp WHERE id_nsa = '$result->id_nsa' AND id_ran_dapot_category = '$res->id_ran_dapot_category' AND active = 1 AND IF($week < 6, start_week BETWEEN 1 AND 5, start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query_a':
            $output = \DB::connection("mysql222a")->select("SELECT *, 
                (
                    SELECT COUNT(*) 
                    FROM dapot_ran_kota_kabupaten a1
                    WHERE a1.id_nsa = a.id_nsa
                    AND IF($week < 6, a1.start_week BETWEEN 1 AND 5, a1.start_week BETWEEN 6 AND 6) 
                ) AS count_kota_kabupaten
            FROM nsa a
            WHERE active = 1
            AND IF($week < 6, start_week BETWEEN 1 AND 5, start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query_detail_a':
            $output = \DB::connection("mysql222a")->select("SELECT *, a.sum_site_wow as wow_site, a.sum_2g_gsm_wow as wow_2g_gsm, a.sum_2g_dcs_wow as wow_2g_dcs, a.sum_3g_f1_wow as wow_3g_f1,  a.sum_3g_f2_wow as wow_3g_f2,  a.sum_3g_f3_wow as wow_3g_f3,  a.sum_3g_f4_wow as wow_3g_f4, a.sum_multisector_f1_wow as wow_multisector_f1, a.sum_multisector_f2_wow as wow_multisector_f2, a.sum_multisector_f3_wow as wow_multisector_f3, a.sum_lte_e_nodeB_wow as wow_lte_e_nodeB, a.sum_nr_e_nodeB_wow as wow_nr_e_nodeB
            FROM dapot_ran_ne_kota_kabupaten a
            JOIN dapot_ran_kota_kabupaten b ON (a.id_dapot_ran_kota_kabupaten = b.id_dapot_ran_kota_kabupaten) 
            WHERE weeks = $week
            AND years = $year
            AND b.id_nsa = '" . $result->id_nsa . "'
            AND b.active = 1		
            AND IF($week < 6, b.start_week BETWEEN 1 AND 5, b.start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query2_a':
            $output = \DB::connection("mysql222a")->select("select count(name_ran_kota_kabupaten) as jml FROM dapot_ran_kota_kabupaten WHERE id_nsa = '$result->id_nsa' AND active = 1 AND IF($week < 6, start_week BETWEEN 1 AND 5, start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query_b':
            $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*) 
                        FROM dapot_ran_kota_kabupaten a1
                        WHERE a1.id_nsa = a.id_nsa
                        AND IF($week < 6, a1.start_week BETWEEN 1 AND 5, a1.start_week BETWEEN 6 AND 6) 
                    ) AS count_kota_kabupaten
                FROM nsa a
                WHERE active = 1
                AND IF($week < 6, start_week BETWEEN 1 AND 5, start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query_detail_b':
            $output = \DB::connection("mysql222a")->select("SELECT *, a.sum_site_wow as wow_site, a.sum_2g_gsm_wow as wow_2g_gsm, a.sum_2g_dcs_wow as wow_2g_dcs, a.sum_3g_f1_wow as wow_3g_f1,  a.sum_3g_f2_wow as wow_3g_f2,  a.sum_3g_f3_wow as wow_3g_f3,  a.sum_3g_f4_wow as wow_3g_f4, a.sum_multisector_f1_wow as wow_multisector_f1, a.sum_multisector_f2_wow as wow_multisector_f2, a.sum_multisector_f3_wow as wow_multisector_f3, a.sum_lte_e_nodeB_wow as wow_lte_e_nodeB, a.sum_nr_e_nodeB_wow as wow_nr_e_nodeB
                FROM dapot_ran_ne_kota_kabupaten a
                JOIN dapot_ran_kota_kabupaten b ON (a.id_dapot_ran_kota_kabupaten = b.id_dapot_ran_kota_kabupaten) 
                WHERE weeks = $week
                AND years = $year
                AND b.id_nsa = '" . $result->id_nsa . "'
                AND b.active = 1		
                AND IF($week < 6, b.start_week BETWEEN 1 AND 5, b.start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        case 'query2_b':
            $output = \DB::connection("mysql222a")->select("select count(name_ran_kota_kabupaten) as jml FROM dapot_ran_kota_kabupaten WHERE id_nsa = '$result->id_nsa' AND active = 1 AND IF($week < 6, start_week BETWEEN 1 AND 5, start_week BETWEEN 6 AND 6)");

            return Core::setResponse("success",$output);
            break;
        }
    }

    public function dapotrannemonthly(Request $request)
    {
        
        $dt = $request->all();

        $mode     = $dt['mode'];

        function db_get_last_update_year_by_month_query($table)
            {
                $tahun = date('Y');
                $q = \DB::connection("mysql222a")->select("
                    SELECT years AS last_update
                    FROM " . $table . "
                    GROUP BY years
                    ORDER BY years DESC
                    LIMIT 1
                ");
                foreach ($q as $q => $r) {
                    if (empty($r)) {
                        return 1;
                    } else {
                        return $r->last_update;
                    }
                }
            }

            function db_get_last_update_month_query_by_year($table, $year)
            {
                $q = \DB::connection("mysql222a")->select("
                    SELECT MONTH(date_created) AS last_month
                    FROM " . $table . "
                    WHERE YEAR(date_created) = $year
                    GROUP BY MONTH(date_created)
                    ORDER BY MONTH(date_created) DESC
                    LIMIT 1
                ");
                foreach ($q as $q => $r) {
                    if (empty($r)) {
                        return 1;
                    } else {
                        return $r->last_month;
                    }
                }
            }

        $year = db_get_last_update_year_by_month_query('dapot_ran_ne_monthly');
        $month = db_get_last_update_month_query_by_year('dapot_ran_ne_monthly', $year);

        switch ($mode) {
        case 'query':  
            $output = \DB::connection("mysql222a")->select("SELECT * ,
            (
                SELECT COUNT(*)
                FROM dapot_ran_resume_ne_monthly a1
                WHERE a1.months = a.months
                AND a1.years = a.years
                AND a1.nsa = a.nsa
            ) AS count_nsa 
            FROM dapot_ran_resume_ne_monthly a
            WHERE months = '" . $month . "'
            AND years = '" . $year . "'
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        case 'query22':
            $output = \DB::connection("mysql222a")->select("SELECT DISTINCT nsa
            FROM dapot_ran_resume_ne_monthly a
            WHERE months = '" . $month . "' AND years = '" . $year . "'
            ORDER BY nsa asc");

            return Core::setResponse("success",$output);
            break;
        case 'query_category':
            $output = \DB::connection("mysql222a")->select("SELECT * FROM ran_dapot_category WHERE id_ran_dapot_category NOT IN (3)");

            return Core::setResponse("success",$output);
            break;
        case 'query_a':
            $output = \DB::connection("mysql222a")->select("SELECT *, 
                (
                    SELECT COUNT(*) 
                    FROM ran_cluster_rtp a1
                    JOIN dapot_ran_ne_monthly b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.id_nsa = a.id_nsa
                    AND b1.id_ran_dapot_category IN ('" . $res->id_ran_dapot_category . "', 3)
                    AND MONTH(b1.date_created) = '$month'
                    AND YEAR(b1.date_created) = $year
                ) AS count_rtpo
            FROM nsa a
            WHERE expired_years = '$year' AND (SELECT MAX(expired_years) FROM nsa) OR expired_years BETWEEN '$year' AND (SELECT MAX(expired_years) FROM nsa)");

            return Core::setResponse("success",$output);
            break;
        case 'query_detail':
            $output = \DB::connection("mysql222a")->select("SELECT *, a.sum_site_mom as mom_site, a.sum_2g_gsm_mom as mom_2g_gsm, a.sum_2g_dcs_mom as mom_2g_dcs, a.sum_3g_f1_mom as mom_3g_f1,  a.sum_3g_f2_mom as mom_3g_f2,  a.sum_3g_f3_mom as mom_3g_f3,  a.sum_3g_f4_mom as mom_3g_f4, a.sum_multisector_f1_mom as mom_multisector_f1, a.sum_multisector_f2_mom as mom_multisector_f2, a.sum_multisector_f3_mom as mom_multisector_f3, a.sum_lte_e_nodeB_mom as mom_lte_e_nodeB, a.sum_nr_e_nodeB_mom as mom_nr_e_nodeB
            FROM dapot_ran_ne_monthly a
            JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp) 
            JOIN ran_dapot_category c ON (a.id_ran_dapot_category = c.id_ran_dapot_category)
            WHERE a.id_ran_dapot_category = '" . $res->id_ran_dapot_category . "' 
            AND MONTH(date_created) = $month
            AND YEAR(date_created) = $year
            AND b.id_nsa = '" . $result->id_nsa . "'
            AND (expired_years = '$year' AND (SELECT MAX(expired_years) FROM nsa) OR expired_years BETWEEN '$year' AND (SELECT MAX(expired_years) FROM nsa))");

            return Core::setResponse("success",$output);
            break;
        case 'query2':
            $output = \DB::connection("mysql222a")->select("select count(name_ran_cluster_rtp) as jml FROM ran_cluster_rtp WHERE id_nsa = '$result->id_nsa' AND id_ran_dapot_category = '$res->id_ran_dapot_category' AND active = 1");

            return Core::setResponse("success",$output);
            break;
        case 'query_b':
            $output = \DB::connection("mysql222a")->select("SELECT *, 
                (
                    SELECT COUNT(*) 
                    FROM dapot_ran_kota_kabupaten a1
                    WHERE a1.id_nsa = a.id_nsa
                ) AS count_kota_kabupaten
            FROM nsa a
            WHERE expired_years = '$year' AND (SELECT MAX(expired_years) FROM nsa) OR expired_years BETWEEN '$year' AND (SELECT MAX(expired_years) FROM nsa)");

            return Core::setResponse("success",$output);
            break;
        case 'query_detail_b':
            $output = \DB::connection("mysql222a")->select("SELECT *, a.sum_site_mom as mom_site, a.sum_2g_gsm_mom as mom_2g_gsm, a.sum_2g_dcs_mom as mom_2g_dcs, a.sum_3g_f1_mom as mom_3g_f1,  a.sum_3g_f2_mom as mom_3g_f2,  a.sum_3g_f3_mom as mom_3g_f3,  a.sum_3g_f4_mom as mom_3g_f4, a.sum_multisector_f1_mom as mom_multisector_f1, a.sum_multisector_f2_mom as mom_multisector_f2, a.sum_multisector_f3_mom as mom_multisector_f3, a.sum_lte_e_nodeB_mom as mom_lte_e_nodeB, a.sum_nr_e_nodeB_mom as mom_nr_e_nodeB
            FROM dapot_ran_ne_kota_kabupaten_monthly a
            JOIN dapot_ran_kota_kabupaten b ON (a.id_dapot_ran_kota_kabupaten = b.id_dapot_ran_kota_kabupaten) 
            WHERE months = $month
            AND years = $year
            AND b.id_nsa = '" . $result->id_nsa . "'
            AND (expired_years = '$year' AND (SELECT MAX(expired_years) FROM nsa) OR expired_years BETWEEN '$year' AND (SELECT MAX(expired_years) FROM nsa))");

            return Core::setResponse("success",$output);
            break;
        case 'query2_b':
            $output = \DB::connection("mysql222a")->select("select count(name_ran_kota_kabupaten) as jml FROM dapot_ran_kota_kabupaten WHERE id_nsa = '$result->id_nsa' AND active = 1");

            return Core::setResponse("success",$output);
            break;
        }
    }

    public function avaweeklyresume(Request $request)
    {
        
        $dt = $request->all();
        $mode     = $dt['mode'];        
        function db_get_last_update_year_query($table)
        {
            $q = \DB::connection("mysql222a")->select("
                SELECT years AS last_update
                FROM " . $table . "
                GROUP BY years
                ORDER BY years DESC
                LIMIT 1
            ");
            
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        function db_get_last_update_week_query_with_year($table, $year)
        {

            $q = \DB::connection("mysql222a")->select("
                SELECT weeks AS last_update
                FROM " . $table . "
                WHERE years = $year
                GROUP BY weeks
                ORDER BY weeks DESC
                LIMIT 1
            ");
            //$q = mysqli_query($conn, $query);
            //$r = mysqli_fetch_object($q);
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        $year = db_get_last_update_year_query('dapot_ran_availability_weighting_ns');
        $week = db_get_last_update_week_query_with_year('dapot_ran_availability_weighting_ns', $year);


        switch ($mode) {
        case 'query':  
            $output = \DB::connection("mysql222a")->select("SELECT * ,
            (
                SELECT COUNT(*)
                FROM dapot_ran_resume_ne_monthly a1
                WHERE a1.months = a.months
                AND a1.years = a.years
                AND a1.nsa = a.nsa
            ) AS count_nsa 
            FROM dapot_ran_resume_ne_monthly a
            WHERE months = '" . $month . "'
            AND years = '" . $year . "'
            ");
            //$output['series'] = $output_iub;
            return Core::setResponse("success",$output);
            break;
        }
    }
}
