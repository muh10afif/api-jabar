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
        case 'ajax-availability-weekly-graph':  
            $dt = $request->all();
            $class_revenue = $dt['class_revenue'];
            $year = $dt['year'];
            $types = $dt['types'];

            if ($class_revenue == 'BRONZE') {
                $min = 90;
                $threshold = 95;
            } else if ($class_revenue == 'SILVER') {
                $min = 90;
                $threshold = 97;
            } else if ($class_revenue == 'GOLD') {
                $min = 90;
                //$threshold = 99.7;
                $threshold = 98.4;
            } else {
                //$threshold = 99.9;
                $threshold = 99;
                $min = 95;
            }
            $output = array();

            if ($types == 'NS') {
                $data = \DB::connection("mysql222a")->select("
                        SELECT weeks
                        FROM dapot_ran_availability_weighting_ns
                        WHERE years = " . $year . "
                        GROUP BY weeks
                        ORDER BY weeks ASC
                    ");
                foreach ($data as $data => $result) {
                    $week[] = "W" . $result->weeks;
                }
            
                $series = array();
                $data = \DB::connection("mysql222a")->select("
                        SELECT a.ns
                        FROM dapot_ran_availability_weighting_ns a
                        
                        GROUP BY a.ns
                    ");
                $counter = 0;
                foreach ($data as $data => $result) {
                    $series[$counter]['name'] = $result->ns;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT * FROM (
                                SELECT *, (1 - (a.total_outage / (a.total_cell * 7 * 24 * 60))) * 100  AS sum_all
                                FROM dapot_ran_availability_weighting_ns a
                                WHERE a.class_revenue = '" . $class_revenue . "'
                                AND a.years = " . $year . "
                                ORDER BY weeks ASC
                            ) AS tab
                            WHERE tab.ns = '" . $result->ns . "'
                        ");
                    $counter2 = 0;
                    foreach ($data as $data => $res) {
                        $series[$counter]['data'][$counter2] = (float) number_format($res->sum_all, 2);
                        $counter2++;
                    }
                    $counter++;
                }
            } else {
                $data = \DB::connection("mysql222a")->select("
                        SELECT weeks
                        FROM dapot_ran_availability_weighting_rtp
                        WHERE years = " . $year . "
                        GROUP BY weeks
                        ORDER BY weeks ASC
                    ");
                foreach ($data as $data => $result) {
                    $week[] = "W" . $result->weeks;
                }
            
                $series = array();
                $data = \DB::connection("mysql222a")->select("
                        SELECT a.rtp
                        FROM dapot_ran_availability_weighting_rtp a
                        
                        GROUP BY a.rtp
                    ");
                $counter = 0;
                foreach ($data as $data => $result) {
                    $series[$counter]['name'] = $result->rtp;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT * FROM (
                                SELECT *, (1 - (a.total_outage / (a.total_cell * 7 * 24 * 60))) * 100  AS sum_all
                                FROM dapot_ran_availability_weighting_rtp a
                                WHERE a.class_revenue = '" . $class_revenue . "'
                                AND a.years = " . $year . "
                                ORDER BY weeks ASC
                            ) AS tab
                            WHERE tab.rtp = '" . $result->rtp . "'
                        ");
                    $counter2 = 0;
                    foreach ($data as $data => $res) {
                        $series[$counter]['data'][$counter2] = (float) number_format($res->sum_all, 2);
                        $counter2++;
                    }
                    $counter++;
                }
            }
            
            $output['categories'] = $week;
            $output['series'] = $series;
            $output['threshold'] = $threshold;
            $output['min'] = $min;
            
            return Core::setResponse("success",$output);
            break;
        case 'ajax-availability-ns-weekly':
            $dt = $request->all();
            $week = $dt['week'];
            $year = $dt['year'];

            function sortByOrder($a, $b)
            {
                return $a['sum_rank'] - $b['sum_rank'];
            }

            $row_weeks = \DB::connection("mysql222a")->select("
                    SELECT MAX(weeks) AS weeks
                    FROM dapot_ran_availability_weighting_ns
                    WHERE years = " . $year . "
                ");
            $tot_weeks = $row_weeks->weeks;

            $count_ns = \DB::connection("mysql222a")->select("
                SELECT *
                FROM dapot_ran_availability_weighting_ns
                WHERE weeks = " . $week . "
                AND years = " . $year . "
                AND ns NOT LIKE '%JABAR%'
                GROUP BY ns
            ");
            
            $sql = \DB::connection("mysql222a")->select("
            SELECT ns, rank, availability_weighting
            FROM (
                SELECT *, 
                (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting, @rank := @rank + 1 AS rank
                FROM (
                    SELECT a.ns,
                    IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_diamond
                        FROM dapot_ran_availability_weighting_ns a1
                        WHERE a1.class_revenue = 'DIAMOND'
                        AND a1.weeks = " . $week . "
                        AND a1.years = " . $year . "
                        AND a1.ns = a.ns
                    ),100) AS availability_diamond,
                    (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_platinum
                        FROM dapot_ran_availability_weighting_ns a1
                        WHERE a1.class_revenue = 'PLATINUM'
                        AND a1.weeks = " . $week . "
                        AND a1.years = " . $year . "
                        AND a1.ns = a.ns
                    ) AS availability_platinum,
                    (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_gold
                        FROM dapot_ran_availability_weighting_ns a1
                        WHERE a1.class_revenue = 'GOLD'
                        AND a1.weeks = " . $week . "
                        AND a1.years = " . $year . "
                        AND a1.ns = a.ns
                    ) AS availability_gold,
                    (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_silver
                        FROM dapot_ran_availability_weighting_ns a1
                        WHERE a1.class_revenue = 'SILVER'
                        AND a1.weeks = " . $week . "
                        AND a1.years = " . $year . "
                        AND a1.ns = a.ns
                    ) AS availability_silver,
                    (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_bronze
                        FROM dapot_ran_availability_weighting_ns a1
                        WHERE a1.class_revenue = 'BRONZE'
                        AND a1.weeks = " . $week . "
                        AND a1.years = " . $year . "
                        AND a1.ns = a.ns
                    ) AS availability_bronze
                    FROM dapot_ran_availability_weighting_ns a
                    WHERE a.ns NOT LIKE '%JABAR%'
                    GROUP BY a.ns
                ) AS tab, (SELECT @rank := 0) r
                ORDER BY (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) DESC
            ) AS tab
            WHERE availability_weighting IS NOT NULL
            ");

            $output = array();
            $value = array();

                $counter = 0;
                foreach ($sql as $sql => $result) {
                    if ($result->rank == 1) {
                        $bgcolor = "#5cb85c"; // green success
                    } else if ($result->rank == 2) {
                        $bgcolor = "#95d095"; // green
                    } else if ($result->rank == ($count_ns - 1)) {
                        $bgcolor = "#f0ad4e"; // orange warning
                    } else if ($result->rank == $count_ns) {
                        $bgcolor = "#d9534f"; // red danger
                    } else {
                        $bgcolor = "white";
                    }
                    $value[$counter]['ns'] = $result->ns;
                    $value[$counter]['rank'] = $result->rank;
                    $value[$counter]['bgcolor'] = $bgcolor;
                    $value[$counter]['availability_weighting'] = (float)number_format($result->availability_weighting, 2);
                    $data = \DB::connection("mysql222a")->select("
                            SELECT * 
                            FROM (
                                SELECT *, 
                                    @student:=CASE WHEN @class <> tab.weeks THEN 1 ELSE @student+1 END AS rank, 
                                    @class:=tab.weeks AS clset
                                    FROM 
                                    (
                                        SELECT *, 
                                        (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting
                                        FROM (
                                            SELECT a.ns, a.weeks,
                                            IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_diamond
                                                FROM dapot_ran_availability_weighting_ns a1
                                                WHERE a1.class_revenue = 'DIAMOND'
                                                AND a1.weeks = a.weeks
                                                AND a1.years = " . $year . "
                                                AND a1.ns = a.ns
                                            ),100) AS availability_diamond,
                                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_platinum
                                                FROM dapot_ran_availability_weighting_ns a1
                                                WHERE a1.class_revenue = 'PLATINUM'
                                                AND a1.weeks = a.weeks
                                                AND a1.years = " . $year . "
                                                AND a1.ns = a.ns
                                            ) AS availability_platinum,
                                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_gold
                                                FROM dapot_ran_availability_weighting_ns a1
                                                WHERE a1.class_revenue = 'GOLD'
                                                AND a1.weeks = a.weeks
                                                AND a1.years = " . $year . "
                                                AND a1.ns = a.ns
                                            ) AS availability_gold,
                                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_silver
                                                FROM dapot_ran_availability_weighting_ns a1
                                                WHERE a1.class_revenue = 'SILVER'
                                                AND a1.weeks = a.weeks
                                                AND a1.years = " . $year . "
                                                AND a1.ns = a.ns
                                            ) AS availability_silver,
                                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_bronze
                                                FROM dapot_ran_availability_weighting_ns a1
                                                WHERE a1.class_revenue = 'BRONZE'
                                                AND a1.weeks = a.weeks
                                                AND a1.years = " . $year . "
                                                AND a1.ns = a.ns
                                            ) AS availability_bronze
                                            FROM dapot_ran_availability_weighting_ns a
                                            WHERE a.ns NOT LIKE '%JABAR%'
                                            AND a.years = " . $year . "
                                            GROUP BY a.ns, a.weeks
                                            ORDER BY a.ns ASC, a.weeks DESC
                                        ) AS tab
                                        GROUP BY  tab.weeks, tab.ns
                                    ) AS tab,(SELECT @student:= 0) s, (SELECT @class:= 0) c
                                    ORDER BY tab.weeks DESC, tab.availability_weighting DESC, tab.ns
                            ) AS tab
                            WHERE tab.ns = '" . $result->ns . "'
                        ");
                    $counter2 = 0;
                    $sum_rank = 0;
                    $availability_weighting = 0;
                    foreach ($data as $data => $res) {
                        if ($res->rank == 1) {
                            $bgcolor = "#5cb85c"; // green success
                        } else if ($res->rank == 2) {
                            $bgcolor = "#95d095"; // green
                        } else if ($res->rank == ($count_ns - 1)) {
                            $bgcolor = "#f0ad4e"; // orange warning
                        } else if ($res->rank == $count_ns) {
                            $bgcolor = "#d9534f"; // red danger
                        } else {
                            $bgcolor = "white";
                        }

                        $value[$counter]['value'][$counter2]['availability_weighting'] = number_format($res->availability_weighting, 2);
                        $value[$counter]['value'][$counter2]['availability_platinum'] = $res->availability_platinum;
                        $value[$counter]['value'][$counter2]['availability_gold'] = $res->availability_gold;
                        $value[$counter]['value'][$counter2]['availability_silver'] = $res->availability_silver;
                        $value[$counter]['value'][$counter2]['availability_bronze'] = $res->availability_bronze;
                        $value[$counter]['value'][$counter2]['rank'] = $res->rank;
                        $value[$counter]['value'][$counter2]['weeks'] = $res->weeks;
                        $value[$counter]['value'][$counter2]['years'] = $year;
                        $value[$counter]['value'][$counter2]['bgcolor'] = $bgcolor;

                        $sum_rank = $sum_rank + $res->rank;
                        $availability_weighting = ($availability_weighting + $res->availability_weighting);
                        $counter2++;
                    }

                    $value[$counter]['sum_rank'] = $sum_rank;
                    $value[$counter]['cur_avail'] = number_format($availability_weighting / $tot_weeks, 2);

                    $counter++;
                }
                usort($value, 'sortByOrder');
                //$output['sort'] = sort($value);
                $output['count_ns'] = $count_ns;
                $output['result'] = $value;
                //echo json_encode($output, JSON_NUMERIC_CHECK);

            return Core::setResponse("success",$output);
            break;
        case 'ajax-availability-rtp-weekly':
            $dt = $request->all();
            $week = $dt['week'];
            $year = $dt['year'];

            function sortByOrder($a, $b)
            {
                return $a['sum_rank'] - $b['sum_rank'];
            }

                $count_rtp = \DB::connection("mysql222a")->select("
                        SELECT *
                        FROM dapot_ran_availability_weighting_rtp
                        WHERE weeks = " . $week . "
                        AND years = " . $year . "
                        AND rtp NOT LIKE '%JABAR%'
                        GROUP BY rtp
                    ");

                $row_weeks = \DB::connection("mysql222a")->select("
                        SELECT MAX(weeks) AS weeks
                        FROM dapot_ran_availability_weighting_ns
                        WHERE years = " . $year . "
                    ");
                $tot_weeks = $row_weeks->weeks;

                $sql = \DB::connection("mysql222a")->select("
                    SELECT rtp, rank, availability_weighting 
                    FROM (
                        SELECT *, 
                        (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting, @rank := @rank + 1 AS rank
                        FROM (
                            SELECT a.rtp,
                            IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_diamond
                                FROM dapot_ran_availability_weighting_rtp a1
                                WHERE a1.class_revenue = 'DIAMOND'
                                AND a1.weeks = " . $week . "
                                AND a1.years = " . $year . "
                                AND a1.rtp = a.rtp
                            ),100) AS availability_diamond,
                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_platinum
                                FROM dapot_ran_availability_weighting_rtp a1
                                WHERE a1.class_revenue = 'PLATINUM'
                                AND a1.weeks = " . $week . "
                                AND a1.years = " . $year . "
                                AND a1.rtp = a.rtp
                            ) AS availability_platinum,
                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_gold
                                FROM dapot_ran_availability_weighting_rtp a1
                                WHERE a1.class_revenue = 'GOLD'
                                AND a1.weeks = " . $week . "
                                AND a1.years = " . $year . "
                                AND a1.rtp = a.rtp
                            ) AS availability_gold,
                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_silver
                                FROM dapot_ran_availability_weighting_rtp a1
                                WHERE a1.class_revenue = 'SILVER'
                                AND a1.weeks = " . $week . "
                                AND a1.years = " . $year . "
                                AND a1.rtp = a.rtp
                            ) AS availability_silver,
                            (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_bronze
                                FROM dapot_ran_availability_weighting_rtp a1
                                WHERE a1.class_revenue = 'BRONZE'
                                AND a1.weeks = " . $week . "
                                AND a1.years = " . $year . "
                                AND a1.rtp = a.rtp
                            ) AS availability_bronze
                            FROM dapot_ran_availability_weighting_rtp a
                            WHERE a.rtp NOT LIKE '%JABAR%'
                            GROUP BY a.rtp
                        ) AS tab, (SELECT @rank := 0) r
                        ORDER BY (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) DESC
                    ) AS tab
                    ");

                $output = array();
                $value = array();
                $counter = 0;
                foreach ($sql as $sql => $result) {
                    if ($result->rank == 1) {
                        $bgcolor = "#5cb85c"; // green success
                    } else if ($result->rank == 2) {
                        $bgcolor = "#95d095"; // green
                    } else if ($result->rank == ($count_rtp - 1)) {
                        $bgcolor = "#f0ad4e"; // orange warning
                    } else if ($result->rank == $count_rtp) {
                        $bgcolor = "#d9534f"; // red danger
                    } else {
                        $bgcolor = "white";
                    }
                    $value[$counter]['rtp'] = $result->rtp;
                    $value[$counter]['rank'] = $result->rank;
                    $value[$counter]['bgcolor'] = $bgcolor;
                    $value[$counter]['availability_weighting'] = (float)$result->availability_weighting;
                    $data = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM (
                            SELECT *, 
                                @student:=CASE WHEN @class <> tab.weeks THEN 1 ELSE @student+1 END AS rank, 
                                @class:=tab.weeks AS clset
                                FROM 
                                (
                                    SELECT *, 
                                    (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting
                                    FROM (
                                        SELECT a.rtp, a.weeks,
                                        IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_diamond
                                            FROM dapot_ran_availability_weighting_rtp a1
                                            WHERE a1.class_revenue = 'DIAMOND'
                                            AND a1.weeks = a.weeks
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ),100) AS availability_diamond,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_platinum
                                            FROM dapot_ran_availability_weighting_rtp a1
                                            WHERE a1.class_revenue = 'PLATINUM'
                                            AND a1.weeks = a.weeks
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_platinum,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_gold
                                            FROM dapot_ran_availability_weighting_rtp a1
                                            WHERE a1.class_revenue = 'GOLD'
                                            AND a1.weeks = a.weeks
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_gold,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_silver
                                            FROM dapot_ran_availability_weighting_rtp a1
                                            WHERE a1.class_revenue = 'SILVER'
                                            AND a1.weeks = a.weeks
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_silver,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * 7 * 24 * 60))) * 100  AS availability_bronze
                                            FROM dapot_ran_availability_weighting_rtp a1
                                            WHERE a1.class_revenue = 'BRONZE'
                                            AND a1.weeks = a.weeks
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_bronze
                                        FROM dapot_ran_availability_weighting_rtp a
                                        WHERE a.rtp NOT LIKE '%JABAR%'
                                        AND a.years = " . $year . "
                                        GROUP BY a.rtp, a.weeks
                                        ORDER BY a.rtp ASC, a.weeks DESC
                                    ) AS tab
                                    GROUP BY  tab.weeks, tab.rtp
                                ) AS tab,(SELECT @student:= 0) s, (SELECT @class:= 0) c
                                ORDER BY tab.weeks DESC, tab.availability_weighting DESC, tab.rtp
                        ) AS tab
                        WHERE tab.rtp = '" . $result->rtp . "'
                        ");
                    $counter2 = 0;
                    $sum_rank = 0;
                    $availability_weighting = 0;
                    foreach ($data as $data => $res) {
                        if ($res->rank == 1) {
                            $bgcolor = "#5cb85c"; // green success
                        } else if ($res->rank == 2) {
                            $bgcolor = "#95d095"; // green
                        } else if ($res->rank == ($count_rtp - 1)) {
                            $bgcolor = "#f0ad4e"; // orange warning
                        } else if ($res->rank == $count_rtp) {
                            $bgcolor = "#d9534f"; // red danger
                        } else {
                            $bgcolor = "white";
                        }

                        $value[$counter]['value'][$counter2]['availability_weighting'] = $res->availability_weighting;
                        $value[$counter]['value'][$counter2]['availability_platinum'] = $res->availability_platinum;
                        $value[$counter]['value'][$counter2]['availability_gold'] = $res->availability_gold;
                        $value[$counter]['value'][$counter2]['availability_silver'] = $res->availability_silver;
                        $value[$counter]['value'][$counter2]['availability_bronze'] = $res->availability_bronze;
                        $value[$counter]['value'][$counter2]['rank'] = $res->rank;
                        $value[$counter]['value'][$counter2]['weeks'] = $res->weeks;
                        $value[$counter]['value'][$counter2]['years'] = $year;
                        $value[$counter]['value'][$counter2]['bgcolor'] = $bgcolor;

                        $sum_rank = $sum_rank + $res->rank;
                        $availability_weighting = $availability_weighting + $res->availability_weighting;
                        $counter2++;
                    }
                    $value[$counter]['sum_rank'] = $sum_rank;
                    $value[$counter]['cur_avail'] = number_format($availability_weighting / $tot_weeks, 2);
                    $counter++;
                }

                usort($value, 'sortByOrder');
                $output['result'] = $value;
                $output['count_rtp'] = $count_rtp;

            return Core::setResponse("success",$output);
            break;
        case 'nsavailabilityweekly':
            $output = \DB::connection("mysql222a")->select("SELECT weeks
                FROM dapot_ran_availability_weighting_ns
                WHERE years = " . $year . "
                GROUP BY weeks
                ORDER BY weeks DESC");
            return Core::setResponse("success",$output);
            break;
        case 'rtpavailabilityweekly':
            $output = \DB::connection("mysql222a")->select("SELECT weeks
                FROM dapot_ran_availability_weighting_rtp
                WHERE years = " . $year . "
                GROUP BY weeks
                ORDER BY weeks DESC");
            return Core::setResponse("success",$output);
            break;
        }
    }

    public function avamonthlyresume(Request $request)
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
        function db_get_last_update_months_query_by_year($table, $year)
        {
            $q = \DB::connection("mysql222a")->select("
                SELECT months AS last_month
                FROM " . $table . "
                WHERE years = $year
                GROUP BY months
                ORDER BY months DESC
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

        if (empty($_GET['year']) && empty($_GET['month'])) {
            $year = db_get_last_update_year_query('dapot_ran_availability_weighting_ns_monthly');
            $month = db_get_last_update_months_query_by_year('dapot_ran_availability_weighting_ns_monthly', $year);
        } else {
            if ($_GET['month'] > 0 && $_GET['month'] <= 12) {
                $year = $_GET['year'];
                $month = $_GET['month'];
            } else {
                $year = db_get_last_update_year_query('dapot_ran_availability_weighting_ns_monthly', $link2);
                $month = db_get_last_update_months_query_by_year('dapot_ran_availability_weighting_ns_monthly', $year);
            }
        }

        switch ($mode) {
        case 'ajax-availability-monthly-graph':
                $dt = $request->all();
                $class_revenue = $dt['class_revenue'];
                $year = $dt['year'];
                $types = $dt['types'];

                if ($class_revenue == 'BRONZE') {
                    $min = 90;
                    $threshold = 95;
                } else if ($class_revenue == 'SILVER') {
                    $min = 90;
                    $threshold = 97;
                } else if ($class_revenue == 'GOLD') {
                    $min = 90;
                    //$threshold = 99.7;
                    $threshold = 98.4;
                } else {
                    //$threshold = 99.9;
                    $threshold = 99;
                    $min = 95;
                }
                $output = array();

                if ($types == 'NS') {
                    $data = \DB::connection("mysql222a")->select("
                            SELECT months
                            FROM dapot_ran_availability_weighting_ns_monthly
                            WHERE years = " . $year . "
                            GROUP BY months
                            ORDER BY months ASC
                        ");
                    foreach ($data as $result) {
                        $dateObj   = DateTime::createFromFormat('!m', $result->months);
                        $monthName = $dateObj->format('F');
                        $month[] = $monthName;
                    }

                    $series = array();
                    $data = \DB::connection("mysql222a")->select("
                            SELECT a.ns
                            FROM dapot_ran_availability_weighting_ns_monthly a
                            GROUP BY a.ns
                        ");
                    $counter = 0;
                    foreach ($data as $result) {
                        $series[$counter]['name'] = $result->ns;
                        $data = \DB::connection("mysql222a")->select("
                                SELECT * FROM (
                                    SELECT *, (1 - (a.total_outage / (a.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS sum_all, DAY(LAST_DAY(CONCAT('2018-', months, '-', '1')))AS tot_day
                                    FROM dapot_ran_availability_weighting_ns_monthly a
                                    WHERE a.class_revenue = '" . $class_revenue . "'
                                    AND a.years = " . $year . "
                                    ORDER BY months ASC
                                ) AS tab
                                WHERE tab.ns = '" . $result->ns . "'
                            ");
                        $counter2 = 0;
                        foreach ($data as $res) {
                            $series[$counter]['data'][$counter2] = (float) number_format($res->sum_all, 3);
                            $counter2++;
                        }
                        $counter++;
                    }
                } else {
                    $data = \DB::connection("mysql222a")->select("
                            SELECT months
                            FROM dapot_ran_availability_weighting_rtp_monthly
                            WHERE years = " . $year . "
                            GROUP BY months
                            ORDER BY months ASC
                        ");
                    foreach ($data as $result) {
                        $dateObj   = DateTime::createFromFormat('!m', $result->months);
                        $monthName = $dateObj->format('F');
                        $month[] = $monthName;
                    }

                    $series = array();
                    $data = \DB::connection("mysql222a")->select("
                            SELECT a.rtp
                            FROM dapot_ran_availability_weighting_rtp_monthly a
                            
                            GROUP BY a.rtp
                        ");
                    $counter = 0;
                    foreach ($data as $result) {
                        $series[$counter]['name'] = $result->rtp;
                        $data = \DB::connection("mysql222a")->select("
                                SELECT * FROM (
                                    SELECT *, (1 - (a.total_outage / (a.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS sum_all, DAY(LAST_DAY(CONCAT('2018-', months, '-', '1')))AS tot_day
                                    FROM dapot_ran_availability_weighting_rtp_monthly a
                                    WHERE a.class_revenue = '" . $class_revenue . "'
                                    AND a.years = " . $year . "
                                    ORDER BY months ASC
                                ) AS tab
                                WHERE tab.rtp = '" . $result->rtp . "'
                            ");
                        $counter2 = 0;
                        foreach ($data as $res) {
                            $series[$counter]['data'][$counter2] = (float) number_format($res->sum_all, 2);
                            $counter2++;
                        }
                        $counter++;
                    }
                }

                $output['categories'] = json_encode($month);
                $output['series'] = json_encode($series);
                $output['threshold'] = $threshold;
                $output['min'] = $min;
            return Core::setResponse("success",$output);
            break;    
        case 'ajax-availability-ns-monthly':
            $dt = $request->all();
            $month = $dt['month'];
            $year = $dt['year'];
            function sortByOrder($a, $b)
                {
                    return $a['sum_rank'] - $b['sum_rank'];
                }

                $row_months = \DB::connection("mysql222a")->select("
                        SELECT MAX(months) AS months
                        FROM dapot_ran_availability_weighting_rtp_monthly
                        WHERE years = " . $year . "
                    ");
                $tot_months = $row_months->months;

                $count_ns = \DB::connection("mysql222a")->select("
                        SELECT *
                        FROM dapot_ran_availability_weighting_ns_monthly
                        WHERE months = " . $month . "
                        AND years = " . $year . "
                        AND ns NOT LIKE '%JABAR%'
                        GROUP BY ns
                    ");

                $sql = \DB::connection("mysql222a")->select("
                        SELECT ns, rank, availability_weighting
                        FROM (
                            SELECT *, 
                            (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting, @rank := @rank + 1 AS rank
                            FROM (
                                SELECT a.ns,
                                IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_diamond
                                    FROM dapot_ran_availability_weighting_ns_monthly a1
                                    WHERE a1.class_revenue = 'DIAMOND'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.ns = a.ns
                                ),100) AS availability_diamond,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_platinum
                                    FROM dapot_ran_availability_weighting_ns_monthly a1
                                    WHERE a1.class_revenue = 'PLATINUM'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.ns = a.ns
                                ) AS availability_platinum,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_gold
                                    FROM dapot_ran_availability_weighting_ns_monthly a1
                                    WHERE a1.class_revenue = 'GOLD'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.ns = a.ns
                                ) AS availability_gold,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_silver
                                    FROM dapot_ran_availability_weighting_ns_monthly a1
                                    WHERE a1.class_revenue = 'SILVER'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.ns = a.ns
                                ) AS availability_silver,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_bronze
                                    FROM dapot_ran_availability_weighting_ns_monthly a1
                                    WHERE a1.class_revenue = 'BRONZE'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.ns = a.ns
                                ) AS availability_bronze
                                FROM dapot_ran_availability_weighting_ns_monthly a
                                WHERE a.ns NOT LIKE '%JABAR%'
                                GROUP BY a.ns
                            ) AS tab, (SELECT @rank := 0) r
                            ORDER BY (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) DESC
                        ) AS tab
                    ");
                $output = array();
                $value = array();
                $counter = 0;
                foreach ($sql as $result) {
                    if ($result->rank == 1) {
                        $bgcolor = "#5cb85c"; // green success
                    } else if ($result->rank == 2) {
                        $bgcolor = "#95d095"; // green
                    } else if ($result->rank == ($count_ns - 1)) {
                        $bgcolor = "#f0ad4e"; // orange warning
                    } else if ($result->rank == $count_ns) {
                        $bgcolor = "#d9534f"; // red danger
                    } else {
                        $bgcolor = "white";
                    }
                    $value[$counter]['ns'] = $result->ns;
                    $value[$counter]['rank'] = $result->rank;
                    $value[$counter]['bgcolor'] = $bgcolor;
                    $value[$counter]['availability_weighting'] = (float)number_format($result->availability_weighting, 2);
                    $data = \DB::connection("mysql222a")->select("
                            SELECT months
                            FROM dapot_ran_availability_weighting_ns_monthly
                            WHERE years = " . $year . "
                            GROUP BY months
                            ORDER BY months DESC
                        ");
                    $counter2 = 0;
                    $sum_rank = 0;
                    $cur_avail = 0;
                    foreach ($data as $res) {
                        $row = \DB::connection("mysql222a")->select("
                                SELECT * 
                                FROM (
                                    SELECT *, 
                                    (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting, @rank := @rank + 1 AS rank
                                    FROM (
                                        SELECT a.ns,
                                        IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_diamond
                                            FROM dapot_ran_availability_weighting_ns_monthly a1
                                            WHERE a1.class_revenue = 'DIAMOND'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.ns = a.ns
                                        ),100) AS availability_diamond,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_platinum
                                            FROM dapot_ran_availability_weighting_ns_monthly a1
                                            WHERE a1.class_revenue = 'PLATINUM'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.ns = a.ns
                                        ) AS availability_platinum,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_gold
                                            FROM dapot_ran_availability_weighting_ns_monthly a1
                                            WHERE a1.class_revenue = 'GOLD'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.ns = a.ns
                                        ) AS availability_gold,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_silver
                                            FROM dapot_ran_availability_weighting_ns_monthly a1
                                            WHERE a1.class_revenue = 'SILVER'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.ns = a.ns
                                        ) AS availability_silver,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_bronze
                                            FROM dapot_ran_availability_weighting_ns_monthly a1
                                            WHERE a1.class_revenue = 'BRONZE'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.ns = a.ns
                                        ) AS availability_bronze
                                        FROM dapot_ran_availability_weighting_ns_monthly a
                                        WHERE a.ns NOT LIKE '%JABAR%'
                                        AND a.years = " . $year . "
                                        GROUP BY a.ns
                                    ) AS tab, (SELECT @rank := 0) r
                                    ORDER BY (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) DESC
                                ) AS tab
                                WHERE tab.ns = '" . $result->ns . "'
                            ");

                        if ($row->rank == 1) {
                            $bgcolor = "#5cb85c"; // green success
                        } else if ($row->rank == 2) {
                            $bgcolor = "#95d095"; // green
                        } else if ($row->rank == ($count_ns - 1)) {
                            $bgcolor = "#f0ad4e"; // orange warning
                        } else if ($row->rank == $count_ns) {
                            $bgcolor = "#d9534f"; // red danger
                        } else {
                            $bgcolor = "white";
                        }

                        $dateObj   = DateTime::createFromFormat('!m', $res->months);
                        $monthName = $dateObj->format('F');

                        $value[$counter]['value'][$counter2]['availability_weighting'] = number_format($row->availability_weighting, 2);
                        $value[$counter]['value'][$counter2]['availability_platinum'] = $row->availability_platinum;
                        $value[$counter]['value'][$counter2]['availability_gold'] = $row->availability_gold;
                        $value[$counter]['value'][$counter2]['availability_silver'] = $row->availability_silver;
                        $value[$counter]['value'][$counter2]['availability_bronze'] = $row->availability_bronze;
                        $value[$counter]['value'][$counter2]['rank'] = $row->rank;
                        $value[$counter]['value'][$counter2]['months'] = $monthName;
                        $value[$counter]['value'][$counter2]['years'] = $year;
                        $value[$counter]['value'][$counter2]['bgcolor'] = $bgcolor;

                        $sum_rank = $sum_rank + $row->rank;
                        $cur_avail = $cur_avail + $row->availability_weighting;
                        $counter2++;
                    }
                    $value[$counter]['sum_rank'] = $sum_rank;
                    $value[$counter]['cur_avail'] = number_format($cur_avail / $tot_months, 2);
                    $counter++;
                }
                usort($value, 'sortByOrder');
                $output['result'] = $value;
                $output['count_ns'] = $count_ns;
            return Core::setResponse("success",$output);
            break;
        case 'ajax-availability-rtp-monthly':
                $dt = $request->all();
                $month = $dt['month'];
                $year = $dt['year'];
            function sortByOrder($a, $b)
                {
                    return $a['sum_rank'] - $b['sum_rank'];
                }
                $row_months = \DB::connection("mysql222a")->select("
                        SELECT MAX(months) AS months
                        FROM dapot_ran_availability_weighting_rtp_monthly
                        WHERE years = " . $year . "
                    ");
                $tot_months = $row_months->months;

                $count_rtp = \DB::connection("mysql222a")->select("
                        SELECT *
                        FROM dapot_ran_availability_weighting_rtp_monthly
                        WHERE months = " . $month . "
                        AND years = " . $year . "
                        AND rtp NOT LIKE '%JABAR%'
                        GROUP BY rtp
                    ");

                $sql = \DB::connection("mysql222a")->select("
                        SELECT rtp, rank, availability_weighting
                        FROM (
                            SELECT *, 
                            (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting, @rank := @rank + 1 AS rank
                            FROM (
                                SELECT a.rtp,
                                IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_diamond
                                    FROM dapot_ran_availability_weighting_rtp_monthly a1
                                    WHERE a1.class_revenue = 'DIAMOND'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.rtp = a.rtp
                                ),100) AS availability_diamond,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_platinum
                                    FROM dapot_ran_availability_weighting_rtp_monthly a1
                                    WHERE a1.class_revenue = 'PLATINUM'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.rtp = a.rtp
                                ) AS availability_platinum,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_gold
                                    FROM dapot_ran_availability_weighting_rtp_monthly a1
                                    WHERE a1.class_revenue = 'GOLD'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.rtp = a.rtp
                                ) AS availability_gold,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_silver
                                    FROM dapot_ran_availability_weighting_rtp_monthly a1
                                    WHERE a1.class_revenue = 'SILVER'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.rtp = a.rtp
                                ) AS availability_silver,
                                (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_bronze
                                    FROM dapot_ran_availability_weighting_rtp_monthly a1
                                    WHERE a1.class_revenue = 'BRONZE'
                                    AND a1.months = " . $month . "
                                    AND a1.years = " . $year . "
                                    AND a1.rtp = a.rtp
                                ) AS availability_bronze
                                FROM dapot_ran_availability_weighting_rtp_monthly a
                                WHERE a.rtp NOT LIKE '%JABAR%'
                                GROUP BY a.rtp
                            ) AS tab, (SELECT @rank := 0) r
                            ORDER BY (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) DESC
                        ) AS tab
                    ");

                $output = array();
                $value = array();
                $counter = 0;
                foreach ($sql as $result) {
                    if ($result->rank == 1) {
                        $bgcolor = "#5cb85c"; // green success
                    } else if ($result->rank == 2) {
                        $bgcolor = "#95d095"; // green
                    } else if ($result->rank == ($count_rtp - 1)) {
                        $bgcolor = "#f0ad4e"; // orange warning
                    } else if ($result->rank == $count_rtp) {
                        $bgcolor = "#d9534f"; // red danger
                    } else {
                        $bgcolor = "white";
                    }
                    $value[$counter]['rtp'] = $result->rtp;
                    $value[$counter]['rank'] = $result->rank;
                    $value[$counter]['bgcolor'] = $bgcolor;
                    $value[$counter]['availability_weighting'] = (float)number_format($result->availability_weighting, 2);
                    $data = \DB::connection("mysql222a")->select("
                            SELECT months
                            FROM dapot_ran_availability_weighting_rtp_monthly
                            WHERE years = " . $year . "
                            GROUP BY months
                            ORDER BY months DESC
                        ");
                    $counter2 = 0;
                    $sum_rank = 0;
                    $cur_avail = 0;
                    foreach ($data as $res) {
                        $row = \DB::connection("mysql222a")->select("
                                SELECT * 
                                FROM (
                                    SELECT *, 
                                    (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) AS availability_weighting, @rank := @rank + 1 AS rank
                                    FROM (
                                        SELECT a.rtp,
                                        IFNULL((SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_diamond
                                            FROM dapot_ran_availability_weighting_rtp_monthly a1
                                            WHERE a1.class_revenue = 'DIAMOND'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ), 100) AS availability_diamond,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_platinum
                                            FROM dapot_ran_availability_weighting_rtp_monthly a1
                                            WHERE a1.class_revenue = 'PLATINUM'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_platinum,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_gold
                                            FROM dapot_ran_availability_weighting_rtp_monthly a1
                                            WHERE a1.class_revenue = 'GOLD'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_gold,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_silver
                                            FROM dapot_ran_availability_weighting_rtp_monthly a1
                                            WHERE a1.class_revenue = 'SILVER'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_silver,
                                        (SELECT (1 - (a1.total_outage / (a1.total_cell * DAY(LAST_DAY(CONCAT('2018-', months, '-', '1'))) * 24 * 60))) * 100  AS availability_bronze
                                            FROM dapot_ran_availability_weighting_rtp_monthly a1
                                            WHERE a1.class_revenue = 'BRONZE'
                                            AND a1.months = " . $res->months . "
                                            AND a1.years = " . $year . "
                                            AND a1.rtp = a.rtp
                                        ) AS availability_bronze
                                        FROM dapot_ran_availability_weighting_rtp_monthly a
                                        WHERE a.rtp NOT LIKE '%JABAR%'
                                        AND a.years = " . $year . "
                                        GROUP BY a.rtp
                                    ) AS tab, (SELECT @rank := 0) r
                                    ORDER BY (availability_diamond * 0.3) + (availability_platinum * 0.25) + (availability_gold * 0.2) + (availability_silver * 0.15) + (availability_bronze * 0.1) DESC
                                ) AS tab
                                WHERE tab.rtp = '" . $result->rtp . "'
                            ");

                        if ($row->rank == 1) {
                            $bgcolor = "#5cb85c"; // green success
                        } else if ($row->rank == 2) {
                            $bgcolor = "#95d095"; // green
                        } else if ($row->rank == ($count_rtp - 1)) {
                            $bgcolor = "#f0ad4e"; // orange warning
                        } else if ($row->rank == $count_rtp) {
                            $bgcolor = "#d9534f"; // red danger
                        } else {
                            $bgcolor = "white";
                        }

                        $dateObj   = DateTime::createFromFormat('!m', $res->months);
                        $monthName = $dateObj->format('F');

                        $value[$counter]['value'][$counter2]['availability_weighting'] = number_format($row->availability_weighting, 2);
                        $value[$counter]['value'][$counter2]['availability_platinum'] = $row->availability_platinum;
                        $value[$counter]['value'][$counter2]['availability_gold'] = $row->availability_gold;
                        $value[$counter]['value'][$counter2]['availability_silver'] = $row->availability_silver;
                        $value[$counter]['value'][$counter2]['availability_bronze'] = $row->availability_bronze;
                        $value[$counter]['value'][$counter2]['rank'] = $row->rank;
                        $value[$counter]['value'][$counter2]['months'] = $monthName;
                        $value[$counter]['value'][$counter2]['years'] = $year;
                        $value[$counter]['value'][$counter2]['bgcolor'] = $bgcolor;

                        $sum_rank = $sum_rank + $row->rank;
                        $cur_avail = $cur_avail + $row->availability_weighting;
                        $counter2++;
                    }
                    $value[$counter]['sum_rank'] = $sum_rank;
                    $value[$counter]['cur_avail'] = number_format($cur_avail / $tot_months, 2);
                    $counter++;
                }
                usort($value, 'sortByOrder');
                $output['result'] = $value;
                $output['count_rtp'] = $count_rtp;

            return Core::setResponse("success",$output);
            break;
        case 'nsavailabilitymonthly':
            $output = \DB::connection("mysql222a")->select("SELECT months
                FROM dapot_ran_availability_weighting_ns_monthly
                WHERE years = " . $year . "
                GROUP BY months
                ORDER BY months DESC");
            return Core::setResponse("success",$output);
            break;
        case 'rtpavailabilitymonthly':
            $output = \DB::connection("mysql222a")->select("SELECT months
                FROM dapot_ran_availability_weighting_rtp_monthly
                WHERE years = " . $year . "
                GROUP BY months
                ORDER BY months DESC");
            return Core::setResponse("success",$output);
            break;
        }
    }

    public function tpavaweekly(Request $request)
    {
        $dt = $request->all();
        date_default_timezone_set("Asia/Jakarta");
        
        $mode = $dt['mode'];

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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        $year = db_get_last_update_year_query('dapot_ran_availability_tp');
        $week = db_get_last_update_week_query_with_year('dapot_ran_availability_tp', $year);

        switch ($mode) {
            case 'ajax-availability-weekly-tp-graph':
                $dt = $request->all();

                date_default_timezone_set("Asia/Jakarta");

                $output = array();
                $year = $dt['year'];
                $class_revenue = $dt['class_revenue'];

                $data = \DB::connection("mysql222a")->select("
                        SELECT a.weeks
                        FROM dapot_ran_availability_tp a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        GROUP BY weeks
                        ORDER BY a.weeks ASC
                    ");
                foreach ($data as $result) {
                    $week[] = "W" . $result->weeks;
                }

                $data = \DB::connection("mysql222a")->select("
                        SELECT a.tp_name
                        FROM dapot_ran_availability_tp a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        AND a.years = " . $year . "
                        GROUP BY tp_name
                    ");
                $counter = 0;
                foreach ($data as $result) {
                    $series[$counter]['name'] = $result->tp_name;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT a.tp_name, a.weeks, a.availability
                            FROM dapot_ran_availability_tp a
                            WHERE a.class_revenue = '" . $class_revenue . "'
                            AND a.tp_name = '" . $result->tp_name . "'
                            AND a.years = '" . $year . "'
                            GROUP BY a.weeks
                            ORDER BY a.weeks ASC
                        ");
                    $counter2 = 0;
                    foreach ($data as $res) {
                        $series[$counter]['data'][$counter2] = (float) $res->availability;
                        $counter2++;
                    }
                    $counter++;
                }
                $output['categories'] = json_encode($week);
                $output['series'] = json_encode($series);

                return Core::setResponse("success",$output);
                break;
                
            case 'ajax-availability-tp-weekly':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");

                $week = $dt['week'];
                $year = $dt['year'];
                $class_revenue = $dt['class_revenue'];

                $max_weeks = \DB::connection("mysql222a")->select("
                        SELECT *
                        FROM dapot_ran_availability_tp
                        WHERE years = " . $year . "
                        GROUP BY weeks
                    ");

                $count_rtp = \DB::connection("mysql222a")->select("
                        SELECT *
                        FROM dapot_ran_availability_tp
                        WHERE weeks = " . $week . "
                        AND years = " . $year . "
                        GROUP BY tp_name
                    ");

                $sql = \DB::connection("mysql222a")->select("
                        SELECT a.tp_name, a.weeks, a.availability
                        FROM dapot_ran_availability_tp a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        AND a.years = " . $year . "
                        GROUP BY a.tp_name
                        ORDER BY a.availability DESC
                    ");

                $output = array();
                $value = array();
                $counter = 0;
                foreach ($sql as $result) {
                    $value[$counter]['tp_name'] = $result->tp_name;
                    $value[$counter]['availability'] = (float)$result->availability;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT a.tp_name, a.weeks, a.availability
                            FROM dapot_ran_availability_tp a
                            WHERE a.class_revenue = '" . $class_revenue . "'
                            AND a.tp_name = '" . $result->tp_name . "'
                            AND a.years = '" . $year . "'
                            GROUP BY a.tp_name, weeks
                            ORDER BY a.weeks DESC
                        ");
                    $counter2 = 0;
                    $total = 0;
                    foreach ($data as $res) {
                        $value[$counter]['value'][$counter2]['availability'] = $res->availability;
                        $value[$counter]['value'][$counter2]['weeks'] = $res->weeks;
                        $value[$counter]['value'][$counter2]['years'] = $year;

                        $total = $total + $res->availability;
                        $counter2++;
                    }
                    $value[$counter]['total'] = (float)($total);
                    $value[$counter]['total_availability'] = (float)floor((($total / $max_weeks) * 100)) / 100;

                    $counter++;
                }
                $output['result'] = $value;
               
                return Core::setResponse("success",$output);
                break;
            case 'data1platinum':
                //$dt = $request->all();
                $output = \DB::connection("mysql222a")->select("
                        SELECT weeks
                        FROM dapot_ran_availability_tp
                        WHERE years = " . $year . "
                        GROUP BY weeks
                        ORDER BY weeks DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'data2gold':
                $output = \DB::connection("mysql222a")->select("
                        SELECT weeks
                        FROM dapot_ran_availability_tp
                        WHERE years = " . $year . "
                        GROUP BY weeks
                        ORDER BY weeks DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'data2silver':
                $output = \DB::connection("mysql222a")->select("
                        SELECT weeks
                        FROM dapot_ran_availability_tp
                        WHERE years = " . $year . "
                        GROUP BY weeks
                        ORDER BY weeks DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'data2bronze':
                $output = \DB::connection("mysql222a")->select("
                    SELECT weeks
                    FROM dapot_ran_availability_tp
                    WHERE years = " . $year . "
                    GROUP BY weeks
                    ORDER BY weeks DESC
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function tpavamonthly(Request $request)
    {
        $dt = $request->all();
        date_default_timezone_set("Asia/Jakarta");
        
        $mode = $dt['mode'];

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

        function db_get_last_update_months_query_by_year($table, $year)
        {
            $q = \DB::connection("mysql222a")->select("
                SELECT months AS last_month
                FROM " . $table . "
                WHERE years = $year
                GROUP BY months
                ORDER BY months DESC
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
        $year = db_get_last_update_year_query('dapot_ran_availability_weighting_ns_monthly');
        $month = db_get_last_update_months_query_by_year('dapot_ran_availability_weighting_ns_monthly', $year);

        switch ($mode) {
            case 'ajax-availability-monthly-tp-graph':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");

                $output = array();
                $year = $dt['year'];
                $class_revenue = $dt['class_revenue'];
                $data = \DB::connection("mysql222a")->select("
                        SELECT a.months
                        FROM dapot_ran_availability_tp_monthly a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        GROUP BY months
                        ORDER BY a.months ASC
                    ");
                foreach ($data as $result) {
                    $dateObj   = DateTime::createFromFormat('!m', $result->months);
                    $monthName = $dateObj->format('F');
                    $week[] = $monthName;
                }

                $data = \DB::connection("mysql222a")->select("
                        SELECT a.tp_name
                        FROM dapot_ran_availability_tp_monthly a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        AND a.years = " . $year . "
                        GROUP BY tp_name
                    ");
                $counter = 0;
                foreach ($data as $result) {
                    $series[$counter]['name'] = $result->tp_name;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT a.tp_name, a.months, a.availability
                            FROM dapot_ran_availability_tp_monthly a
                            WHERE a.class_revenue = '" . $class_revenue . "'
                            AND a.tp_name = '" . $result->tp_name . "'
                            AND a.years = '" . $year . "'
                            GROUP BY a.months
                            ORDER BY a.months ASC
                        ");
                    $counter2 = 0;
                    foreach ($data as $res) {
                        $series[$counter]['data'][$counter2] = (float) $res->availability;
                        $counter2++;
                    }
                    $counter++;
                }
                $output['categories'] = json_encode($week);
                $output['series'] = json_encode($series);
                return Core::setResponse("success",$output);
                break;
            case 'ajax-availability-tp-monthly':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $output = array();
                $year = $dt['year'];
                $class_revenue = $dt['class_revenue'];

                $data = \DB::connection("mysql222a")->select("
                        SELECT a.months
                        FROM dapot_ran_availability_tp_monthly a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        GROUP BY months
                        ORDER BY a.months ASC
                    ");
                foreach ($data as $result) {
                    $dateObj   = DateTime::createFromFormat('!m', $result->months);
                    $monthName = $dateObj->format('F');
                    $week[] = $monthName;
                }

                $data = \DB::connection("mysql222a")->select("
                        SELECT a.tp_name
                        FROM dapot_ran_availability_tp_monthly a
                        WHERE a.class_revenue = '" . $class_revenue . "'
                        AND a.years = " . $year . "
                        GROUP BY tp_name
                    ");
                $counter = 0;
                foreach ($data as $result) {
                    $series[$counter]['name'] = $result->tp_name;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT a.tp_name, a.months, a.availability
                            FROM dapot_ran_availability_tp_monthly a
                            WHERE a.class_revenue = '" . $class_revenue . "'
                            AND a.tp_name = '" . $result->tp_name . "'
                            AND a.years = '" . $year . "'
                            GROUP BY a.months
                            ORDER BY a.months ASC
                        ");
                    $counter2 = 0;
                    foreach ($data as $res) {
                        $series[$counter]['data'][$counter2] = (float) $res->availability;
                        $counter2++;
                    }
                    $counter++;
                }
                $output['categories'] = json_encode($week);
                $output['series'] = json_encode($series);
                return Core::setResponse("success",$output);
                break;
            case 'data1platinum':
                //$dt = $request->all();
                $output = \DB::connection("mysql222a")->select("
                        SELECT months
                        FROM dapot_ran_availability_tp_monthly
                        WHERE years = " . $year . "
                        GROUP BY months
                        ORDER BY months DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'data2gold':
                $output = \DB::connection("mysql222a")->select("
                        SELECT months
                        FROM dapot_ran_availability_tp_monthly
                        WHERE years = " . $year . "
                        GROUP BY months
                        ORDER BY months DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'data2silver':
                $output = \DB::connection("mysql222a")->select("
                        SELECT months
                        FROM dapot_ran_availability_tp_monthly
                        WHERE years = " . $year . "
                        GROUP BY months
                        ORDER BY months DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'data2bronze':
                $output = \DB::connection("mysql222a")->select("
                    SELECT months
                    FROM dapot_ran_availability_tp_monthly
                    WHERE years = " . $year . "
                    GROUP BY months
                    ORDER BY months DESC
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function mttr(Request $request)
    {
        $dt = $request->all();
        function db_get_last_update_months_query_by_year($table, $year)
        {
            $q = \DB::connection("mysql222a")->select("
                SELECT months AS last_month
                FROM " . $table . "
                WHERE years = $year
                GROUP BY months
                ORDER BY months DESC
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

        if (isset($_GET['search_week'])) {
            $week = $dt['week'];
            $year = $dt['year'];
            $get_last_update_month = db_get_last_update_months_query_by_year('dapot_ran_new_mttr_monthly', $year);
            $month = $get_last_update_month;
        } else {
            $week = $week;
            $year = $year;
            $month = db_get_last_update_months_query_by_year('dapot_ran_new_mttr_monthly', $year);
        }

        switch ($mode) {
            case 'ajax-detail-ran-mttr':
                $dt = $request->all();
                $id_dapot_ran_mttr = $dt['id_dapot_ran_mttr'];
                
                $sql= \DB::connection("mysql222a")->select("
                        SELECT * FROM dapot_ran_mttr
                        WHERE id_dapot_ran_mttr = '" . $id_dapot_ran_mttr . "'
                    ");

                $output = array();
                $output["sql"] = $query;
                //$row = mysqli_fetch_object($sql);
                foreach ($sql as $sql => $row) {
                $output["title_content"] = "<h2>" . $row->site_name . "</h2>";
                $output["content"] = '<table class="table bordered border striped">';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Sum Of Occurence</th>';
                $output["content"] .= '<th>' . $row->sum_of_occurence . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Sum Of Outage</th>';
                $output["content"] .= '<th>' . $row->sum_of_outage . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>MTTR</th>';
                $output["content"] .= '<th>' . $row->mttr . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Day</th>';
                $output["content"] .= '<th>' . $row->day . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Category</th>';
                $output["content"] .= '<th>' . $row->mttr_category . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Description</th>';
                $output["content"] .= '<th>' . $row->description . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '</table>';
                }
                //echo json_encode($output);
                return Core::setResponse("success",$output);
                break;
            case 'series1':
                $output = \DB::connection("mysql222a")->select("SELECT a.rtp 
                FROM dapot_ran_new_mttr_monthly a 
                GROUP BY a.rtp");
                return Core::setResponse("success",$output);
                break;
            case 'series2':
                $output = \DB::connection("mysql222a")->select("SELECT AVG(a.mttr) AS value_ran_mttr
                FROM dapot_ran_new_mttr_monthly a 
                WHERE a.rtp = '" . $result->rtp . "'
                AND a.months = '" . $i . "'
                AND a.years = '" . $year . "'");
                return Core::setResponse("success",$output);
                break;
            case 'series3':
                $output = \DB::connection("mysql222a")->select("SELECT a.ns 
                FROM dapot_ran_new_mttr_monthly a 
                GROUP BY a.ns");
                return Core::setResponse("success",$output);
                break;
            case 'series4':
                $output = \DB::connection("mysql222a")->select("SELECT AVG(a.mttr) AS value_ran_mttr
                FROM dapot_ran_new_mttr_monthly a 
                WHERE a.ns = '" . $result->ns . "'
                AND a.months = '" . $i . "'
                AND a.years = '" . $year . "'");
                return Core::setResponse("success",$output);
                break;
            case 'top10downplatinum':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                FROM dapot_ran_new_mttr a
                WHERE weeks = $week
                AND years = $year
                AND class = 'Platinum'
                ORDER BY a.sum_of_occurence DESC, a.site_name ASC
                LIMIT 10");
                return Core::setResponse("success",$output);
                break;
            case 'top10downgold':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                FROM dapot_ran_new_mttr a
                WHERE weeks = $week
                AND years = $year
                AND class = 'Gold'
                ORDER BY a.sum_of_occurence DESC, a.site_name ASC
                LIMIT 10");
                return Core::setResponse("success",$output);
                break;
            case 'top10besarplatinum':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                FROM dapot_ran_new_mttr a
                WHERE weeks = $week
                AND years = $year
                AND class = 'Platinum'
                ORDER BY a.sum_of_outage DESC, a.site_name ASC
                LIMIT 10");
                return Core::setResponse("success",$output);
                break;
            case 'top10besargold':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                FROM dapot_ran_new_mttr a
                WHERE weeks = $week
                AND years = $year
                AND class = 'Gold'
                ORDER BY a.sum_of_outage DESC, a.site_name ASC
                LIMIT 10");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function reconavaweeklyallband(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        $year = db_get_last_update_year_query('dapot_ran_rekon_availability_ne');
        $get_last_update_week = db_get_last_update_week_query_with_year('dapot_ran_rekon_availability_ne', $year);
        $week = $get_last_update_week;
        if (isset($dt['search'])) {
            $week = $dt['week'];
            $year = $dt['year'];
        } else {
            $week = $week;
            $year = $year;
        }

        $table;
        if (($week >= '4' && $year == '2017') || $year > 2017) {
            $table = 'dapot_ran_rekon_availability_ne';
            $function = "showDetail2";
            $file_rtp = 'export-rekon-availability-ne-rtp.php';
            $file_nsa = 'export-rekon-availability-ne-nsa.php';
        } else {
            $table = 'dapot_ran_rekon_availability';
            $function = "showDetail";
            $file_rtp = 'export-rekon-availability-rtp.php';
            $file_nsa = 'export-rekon-availability-nsa.php';
        }

        switch ($mode) {
            case 'ajax-ran-availability':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");

                $id_ran_cluster_rtp = $dt['id_ran_cluster_rtp'];
                $id_class_revenue = $dt['id_class_revenue'];
                $frequency = $dt['frequency'];
                $week = $dt['week'];
                $year = $dt['year'];
                
                if($id_class_revenue == 'not-yet'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND name_problem_cause_category = ''
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                    ");

                } else if($id_class_revenue == 'done'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND name_problem_cause_category != ''
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                    ");

                } else {
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND id_class_revenue = '".$id_class_revenue."'
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                    ");
                }
                
                $output = array();
                $output["sql"] = $sql;
                $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<thead>';
                        $output["content"] .= '<tr>';
                            $output["content"] .= '<td>No</td>';
                            $output["content"] .= '<td>Site Name</td>';
                            $output["content"] .= '<td>Sum Of Outage</td>';
                            $output["content"] .= '<td>Avaialability</td>';
                            $output["content"] .= '<td>Problem Cause</td>';
                            $output["content"] .= '<td>Action Plan</td>';
                            $output["content"] .= '<td>Status</td>';
                            $output["content"] .= '<td>Category</td>';
                            $output["content"] .= '<td>RTP</td>';
                        $output["content"] .= '</tr>';
                    $output["content"] .= '</thead>';	
                    $output["content"] .= '<tbody>';	
                    $no = 0;
                while($result = mysql_fetch_object($sql)){
                    $no++;
                    $output["content"] .= '<tr>';
                        $output["content"] .= '<td>'.$no.'</td>';
                        $output["content"] .= '<td>'.$result->site_name.'</td>';
                        $output["content"] .= '<td>'.$result->sum_of_outage.'</td>';
                        $output["content"] .= '<td>'.$result->availability.'</td>';
                        $output["content"] .= '<td>'.$result->problem_cause.'</td>';
                        $output["content"] .= '<td>'.$result->action_plan.'</td>';
                        $output["content"] .= '<td>'.$result->status_availability.'</td>';
                        $output["content"] .= '<td>'.$result->name_problem_cause_category.'</td>';
                        $output["content"] .= '<td>'.$result->name_ran_cluster_rtp.'</td>';
                    $output["content"] .= '</tr>';
                }
                    $output["content"] .= '</tbody>';	
                $output["content"] .= '</table>';	
                $output["title_content"] = strtoupper($frequency).' Availability ';

                return Core::setResponse("success",$output);
                break;
            case 'ajax-rekon-availability-ne':
                date_default_timezone_set("Asia/Jakarta");
                $dt = $request->all();

                $id_ran_cluster_rtp = $dt['id_ran_cluster_rtp'];
                $id_class_revenue = $dt['id_class_revenue'];
                $frequency = $dt['frequency'];
                $week = $dt['week'];
                $year = $dt['year'];
                
                if($id_class_revenue == 'not-yet'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability_ne a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND frequency = '".$frequency."'
                        AND name_problem_cause_category = ''
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                        GROUP BY a.bts_name
                    ");
                } else if($id_class_revenue == 'done'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability_ne a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND frequency = '".$frequency."'
                        AND name_problem_cause_category != ''
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                        GROUP BY a.bts_name
                    ");
                } else {
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability_ne a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND id_class_revenue = '".$id_class_revenue."'
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                        GROUP BY a.bts_name
                    ");
                }
                
                $output = array();
                $output["sql"] = $sql;
                $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<thead>';
                        $output["content"] .= '<tr>';
                            $output["content"] .= '<td>No</td>';
                            $output["content"] .= '<td>BTS Name</td>';
                            $output["content"] .= '<td>Sum Of Outage</td>';
                            $output["content"] .= '<td>Avaialability</td>';
                            $output["content"] .= '<td>Problem Cause</td>';
                            $output["content"] .= '<td>Action Plan</td>';
                            $output["content"] .= '<td>Status</td>';
                            $output["content"] .= '<td>Category</td>';
                            $output["content"] .= '<td>RTP</td>';
                        $output["content"] .= '</tr>';
                    $output["content"] .= '</thead>';	
                    $output["content"] .= '<tbody>';	
                    $no = 0;
                while($result = mysql_fetch_object($sql)){
                    $no++;
                    $output["content"] .= '<tr>';
                        $output["content"] .= '<td>'.$no.'</td>';
                        $output["content"] .= '<td>'.$result->bts_name.'</td>';
                        $output["content"] .= '<td>'.$result->sum_of_outage.'</td>';
                        $output["content"] .= '<td>'.$result->availability.'</td>';
                        $output["content"] .= '<td>'.$result->problem_cause.'</td>';
                        $output["content"] .= '<td>'.$result->action_plan.'</td>';
                        $output["content"] .= '<td>'.$result->status_availability.'</td>';
                        $output["content"] .= '<td>'.$result->name_problem_cause_category.'</td>';
                        $output["content"] .= '<td>'.$result->name_ran_cluster_rtp.'</td>';
                    $output["content"] .= '</tr>';
                }
                    $output["content"] .= '</tbody>';	
                $output["content"] .= '</table>';	
                $output["title_content"] = strtoupper($frequency).' Availability ';
                echo json_encode($output);

                return Core::setResponse("success",$output);
                break;
            case 'countofbtsavaallweek':
                date_default_timezone_set("Asia/Jakarta");
                $dt = $request->all();

                $data = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*) 
                        FROM ran_cluster_rtp a1
                        WHERE a1.id_nsa = a.id_nsa
                        AND id_ran_dapot_category IN (2,3)
                    ) AS count_rtpo
                FROM nsa a
                WHERE expired_years = '" . $year . "'
                ORDER BY a.nsa_name");
                
                if ($table == 'dapot_ran_rekon_availability_ne') {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                } else {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '3G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '4G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                }

                return Core::setResponse("success",$output);
                break;
            case 'countofbtsavaallweek':
                date_default_timezone_set("Asia/Jakarta");
                $dt = $request->all();

                $data = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*) 
                        FROM ran_cluster_rtp a1
                        WHERE a1.id_nsa = a.id_nsa
                        AND id_ran_dapot_category IN (2,3)
                    ) AS count_rtpo
                FROM nsa a
                WHERE expired_years = '" . $year . "'
                ORDER BY a.nsa_name");
                
                if ($table == 'dapot_ran_rekon_availability_ne') {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                } else {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '3G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '4G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                }

                return Core::setResponse("success",$output);
                break;
        }
    }

    public function reconavamonthlyallband(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }

        $year = db_get_last_update_year_query('dapot_ran_rekon_availability_ne');
        $get_last_update_week = db_get_last_update_week_query_with_year('dapot_ran_rekon_availability_ne', $year);
        $week = $get_last_update_week;
        if (isset($dt['search'])) {
            $week = $dt['week'];
            $year = $dt['year'];
        } else {
            $week = $week;
            $year = $year;
        }

        $table;
        if (($week >= '4' && $year == '2017') || $year > 2017) {
            $table = 'dapot_ran_rekon_availability_ne';
            $function = "showDetail2";
            $file_rtp = 'export-rekon-availability-ne-rtp.php';
            $file_nsa = 'export-rekon-availability-ne-nsa.php';
        } else {
            $table = 'dapot_ran_rekon_availability';
            $function = "showDetail";
            $file_rtp = 'export-rekon-availability-rtp.php';
            $file_nsa = 'export-rekon-availability-nsa.php';
        }

        switch ($mode) {
            case 'ajax-ran-availability':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");

                $id_ran_cluster_rtp = $dt['id_ran_cluster_rtp'];
                $id_class_revenue = $dt['id_class_revenue'];
                $frequency = $dt['frequency'];
                $week = $dt['week'];
                $year = $dt['year'];
                
                if($id_class_revenue == 'not-yet'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND name_problem_cause_category = ''
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                    ");

                } else if($id_class_revenue == 'done'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND name_problem_cause_category != ''
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                    ");

                } else {
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND id_class_revenue = '".$id_class_revenue."'
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                    ");
                }
                
                $output = array();
                $output["sql"] = $sql;
                $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<thead>';
                        $output["content"] .= '<tr>';
                            $output["content"] .= '<td>No</td>';
                            $output["content"] .= '<td>Site Name</td>';
                            $output["content"] .= '<td>Sum Of Outage</td>';
                            $output["content"] .= '<td>Avaialability</td>';
                            $output["content"] .= '<td>Problem Cause</td>';
                            $output["content"] .= '<td>Action Plan</td>';
                            $output["content"] .= '<td>Status</td>';
                            $output["content"] .= '<td>Category</td>';
                            $output["content"] .= '<td>RTP</td>';
                        $output["content"] .= '</tr>';
                    $output["content"] .= '</thead>';	
                    $output["content"] .= '<tbody>';	
                    $no = 0;
                while($result = mysql_fetch_object($sql)){
                    $no++;
                    $output["content"] .= '<tr>';
                        $output["content"] .= '<td>'.$no.'</td>';
                        $output["content"] .= '<td>'.$result->site_name.'</td>';
                        $output["content"] .= '<td>'.$result->sum_of_outage.'</td>';
                        $output["content"] .= '<td>'.$result->availability.'</td>';
                        $output["content"] .= '<td>'.$result->problem_cause.'</td>';
                        $output["content"] .= '<td>'.$result->action_plan.'</td>';
                        $output["content"] .= '<td>'.$result->status_availability.'</td>';
                        $output["content"] .= '<td>'.$result->name_problem_cause_category.'</td>';
                        $output["content"] .= '<td>'.$result->name_ran_cluster_rtp.'</td>';
                    $output["content"] .= '</tr>';
                }
                    $output["content"] .= '</tbody>';	
                $output["content"] .= '</table>';	
                $output["title_content"] = strtoupper($frequency).' Availability ';

                return Core::setResponse("success",$output);
                break;
            case 'ajax-rekon-availability-ne':
                date_default_timezone_set("Asia/Jakarta");
                $dt = $request->all();

                $id_ran_cluster_rtp = $dt['id_ran_cluster_rtp'];
                $id_class_revenue = $dt['id_class_revenue'];
                $frequency = $dt['frequency'];
                $week = $dt['week'];
                $year = $dt['year'];
                
                if($id_class_revenue == 'not-yet'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability_ne a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND frequency = '".$frequency."'
                        AND name_problem_cause_category = ''
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                        GROUP BY a.bts_name
                    ");
                } else if($id_class_revenue == 'done'){
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability_ne a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND frequency = '".$frequency."'
                        AND name_problem_cause_category != ''
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                        GROUP BY a.bts_name
                    ");
                } else {
                    $sql = \DB::connection("mysql222a")->select("
                        SELECT * 
                        FROM dapot_ran_rekon_availability_ne a
                        JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                        WHERE weeks = '".$week."'
                        AND years = '".$year."'
                        AND id_class_revenue = '".$id_class_revenue."'
                        AND frequency = '".$frequency."'
                        AND a.id_ran_cluster_rtp = '".$id_ran_cluster_rtp."'
                        GROUP BY a.bts_name
                    ");
                }
                
                $output = array();
                $output["sql"] = $sql;
                $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<thead>';
                        $output["content"] .= '<tr>';
                            $output["content"] .= '<td>No</td>';
                            $output["content"] .= '<td>BTS Name</td>';
                            $output["content"] .= '<td>Sum Of Outage</td>';
                            $output["content"] .= '<td>Avaialability</td>';
                            $output["content"] .= '<td>Problem Cause</td>';
                            $output["content"] .= '<td>Action Plan</td>';
                            $output["content"] .= '<td>Status</td>';
                            $output["content"] .= '<td>Category</td>';
                            $output["content"] .= '<td>RTP</td>';
                        $output["content"] .= '</tr>';
                    $output["content"] .= '</thead>';	
                    $output["content"] .= '<tbody>';	
                    $no = 0;
                while($result = mysql_fetch_object($sql)){
                    $no++;
                    $output["content"] .= '<tr>';
                        $output["content"] .= '<td>'.$no.'</td>';
                        $output["content"] .= '<td>'.$result->bts_name.'</td>';
                        $output["content"] .= '<td>'.$result->sum_of_outage.'</td>';
                        $output["content"] .= '<td>'.$result->availability.'</td>';
                        $output["content"] .= '<td>'.$result->problem_cause.'</td>';
                        $output["content"] .= '<td>'.$result->action_plan.'</td>';
                        $output["content"] .= '<td>'.$result->status_availability.'</td>';
                        $output["content"] .= '<td>'.$result->name_problem_cause_category.'</td>';
                        $output["content"] .= '<td>'.$result->name_ran_cluster_rtp.'</td>';
                    $output["content"] .= '</tr>';
                }
                    $output["content"] .= '</tbody>';	
                $output["content"] .= '</table>';	
                $output["title_content"] = strtoupper($frequency).' Availability ';
                echo json_encode($output);

                return Core::setResponse("success",$output);
                break;
            case 'countofbtsavaallweek':
                date_default_timezone_set("Asia/Jakarta");
                $dt = $request->all();

                $data = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*) 
                        FROM ran_cluster_rtp a1
                        WHERE a1.id_nsa = a.id_nsa
                        AND id_ran_dapot_category IN (2,3)
                    ) AS count_rtpo
                FROM nsa a
                WHERE expired_years = '" . $year . "'
                ORDER BY a.nsa_name");
                
                if ($table == 'dapot_ran_rekon_availability_ne') {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                } else {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '3G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '4G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                }

                return Core::setResponse("success",$output);
                break;
            case 'countofbtsavaallweek':
                date_default_timezone_set("Asia/Jakarta");
                $dt = $request->all();

                $data = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*) 
                        FROM ran_cluster_rtp a1
                        WHERE a1.id_nsa = a.id_nsa
                        AND id_ran_dapot_category IN (2,3)
                    ) AS count_rtpo
                FROM nsa a
                WHERE expired_years = '" . $year . "'
                ORDER BY a.nsa_name");
                
                if ($table == 'dapot_ran_rekon_availability_ne') {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '3G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category = ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM dapot_ran_rekon_availability_ne a2 
                                        WHERE a2.frequency = '4G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                        AND a2.name_problem_cause_category != ''
                                        GROUP BY a2.bts_name
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                } else {
                    $data_detail = \DB::connection("mysql222a")->select("
                                SELECT *, 
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '2G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_2g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '2G' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_2g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '3G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_3g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '3G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_3g,
                                
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 1 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_platinum_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 2 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_gold_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 3 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_silver_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.id_class_revenue = 4 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_bronze_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.frequency = '4G' 
                                        AND a2.name_problem_cause_category = '' 
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_not_yet_4g,
                                (
                                    SELECT COUNT(*) FROM (
                                        SELECT *
                                        FROM $table a2 
                                        WHERE a2.name_problem_cause_category != '' 
                                        AND a2.frequency = '4G'
                                        AND weeks = '" . $week . "'
                                        AND years = '" . $year . "'
                                    ) tab
                                    WHERE tab.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                                ) AS count_done_4g
                                
                                FROM ran_cluster_rtp a 
                                WHERE id_nsa = '" . $result->id_nsa . "'
                                AND id_ran_dapot_category IN (2,3)
                                AND a.expired_years = '" . $year . "'
                                ORDER BY a.name_ran_cluster_rtp
                            ");
                }

                return Core::setResponse("success",$output);
                break;
        }
    }

    public function reconavaweeklynotachieved(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_availability_rekon_not_achieved');
        $week = db_get_last_update_week_query_with_year('dapot_ran_availability_rekon_not_achieved', $year);

        if (isset($dt['search_week'])) {
            $week = $dt['week'];
            $year = $dt['year'];
        } else {
            $week = $week;
            $year = $year;
        }

        switch ($mode) {
            case 'ajax-get-detail-availability-not-achieved':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $output = array();
                $years = $dt['years'];
                $weeks = $dt['weeks'];
                $name = $dt['name'];
                $revenue_class = $dt['revenue_class'];
                $type = $dt['type'];
                switch ($type) {
                    case "NSA":
                        $data = \DB::connection("mysql222a")->select("
                                SELECT *, a.availability  AS avail
                                FROM dapot_ran_availability_rekon_not_achieved a 
                                WHERE a.nsa = '" . $name . "'
                                AND a.status_achievement = 'NOT ACHIEVED'
                                AND a.revenue_class = '" . $revenue_class . "'
                                AND a.weeks = '" . $weeks . "'
                                AND a.years = '" . $years . "'
                            ");
                        $status = TRUE;
                        $message = "DATA FOUND";
                        break;
                    case "RTP":
                        $data = \DB::connection("mysql222a")->select("
                                SELECT *, a.availability  AS avail
                                FROM dapot_ran_availability_rekon_not_achieved a 
                                WHERE a.rtp = '" . $name . "'
                                AND a.status_achievement = 'NOT ACHIEVED'
                                AND a.revenue_class = '" . $revenue_class . "'
                                AND a.weeks = '" . $weeks . "'
                                AND a.years = '" . $years . "'
                            ");
                        $status = TRUE;
                        $message = "DATA FOUND";
                        break;
                    default:
                        $data = NULL;
                        $status = FALSE;
                        $message = ERROR;
                }
                $output['result'] = $data;
                $output['status'] = $status;
                $output['message'] = $message;
                return Core::setResponse("success",$output);
                break;
            case 'data1':
                $output = \DB::connection("mysql222a")->select("SELECT a.rtp,
                    COUNT(CASE WHEN a.revenue_class = 'Platinum' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_platinum,
                    COUNT(CASE WHEN a.revenue_class = 'Gold' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_gold,
                    COUNT(CASE WHEN a.revenue_class = 'Silver' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_silver,
                    COUNT(CASE WHEN a.revenue_class = 'Bronze' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_bronze
                    FROM dapot_ran_availability_rekon_not_achieved a 
                    WHERE a.rtp != ''
                    AND a.weeks = '" . $week . "' 
                    AND a.years = '" . $year . "' 
                    GROUP BY a.rtp
                    ORDER BY a.nsa, a.rtp");
                return Core::setResponse("success",$output);
                break;
            case 'data2':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa,
                    COUNT(CASE WHEN a.revenue_class = 'Platinum' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_platinum,
                    COUNT(CASE WHEN a.revenue_class = 'Gold' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_gold,
                    COUNT(CASE WHEN a.revenue_class = 'Silver' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_silver,
                    COUNT(CASE WHEN a.revenue_class = 'Bronze' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_bronze
                    FROM dapot_ran_availability_rekon_not_achieved a 
                    WHERE a.nsa != ''
                    AND a.weeks = '" . $week . "' 
                    AND a.years = '" . $year . "' 
                    GROUP BY a.nsa
                    ORDER BY a.nsa, a.rtp");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function reconavamonthlynotachieved(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

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

        function db_get_last_update_months_query_by_year($table, $year)
        {
            $q = \DB::connection("mysql222a")->select("
                SELECT months AS last_month
                FROM " . $table . "
                WHERE years = $year
                GROUP BY months
                ORDER BY months DESC
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
        $year = db_get_last_update_year_query('dapot_ran_availability_rekon_not_achieved_monthly');
        $month = db_get_last_update_months_query_by_year('dapot_ran_availability_rekon_not_achieved_monthly', $year);
        if (isset($dt['search_month'])) {
            $month = $dt['month'];
            $year = $dt['year'];
        } else {
            $month = $month;
            $year = $year;
        }

        switch ($mode) {
            case 'ajax-get-detail-availability-not-achieved-monthly':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $output = array();
                $years = $dt['years'];
                $months = $dt['months'];
                $name = $dt['name'];
                $revenue_class = $dt['revenue_class'];
                $type = $dt['type'];

                switch ($type) {
                    case "NSA":
                        $data = \DB::connection("mysql222a")->select("
                                SELECT *, a.availability  AS avail
                                FROM dapot_ran_availability_rekon_not_achieved_monthly a 
                                WHERE a.nsa = '" . $name . "'
                                AND a.status_achievement = 'NOT ACHIEVED'
                                AND a.revenue_class = '" . $revenue_class . "'
                                AND a.months = '" . $months . "'
                                AND a.years = '" . $years . "'
                            ");
                        $status = TRUE;
                        $message = "DATA FOUND";
                        break;
                    case "RTP":
                        $data = \DB::connection("mysql222a")->select("
                                SELECT *, a.availability  AS avail
                                FROM dapot_ran_availability_rekon_not_achieved_monthly a 
                                WHERE a.rtp = '" . $name . "'
                                AND a.status_achievement = 'NOT ACHIEVED'
                                AND a.revenue_class = '" . $revenue_class . "'
                                AND a.months = '" . $months . "'
                                AND a.years = '" . $years . "'
                            ");
                        $status = TRUE;
                        $message = "DATA FOUND";
                        break;
                    default:
                        $data = NULL;
                        $status = FALSE;
                        $message = ERROR;
                }
                $output['result'] = $data;
                $output['status'] = $status;
                $output['message'] = $message;

                return Core::setResponse("success",$output);
                break;
            case 'data1':
                $output = \DB::connection("mysql222a")->select("SELECT a.rtp,
                    COUNT(CASE WHEN a.revenue_class = 'Platinum' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_platinum,
                    COUNT(CASE WHEN a.revenue_class = 'Gold' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_gold,
                    COUNT(CASE WHEN a.revenue_class = 'Silver' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_silver,
                    COUNT(CASE WHEN a.revenue_class = 'Bronze' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_bronze
                    FROM dapot_ran_availability_rekon_not_achieved_monthly a 
                    WHERE a.rtp != ''
                    AND a.months = '" . $month . "' 
                    AND a.years = '" . $year . "' 
                    GROUP BY a.rtp
                    ORDER BY a.nsa, a.rtp");
                return Core::setResponse("success",$output);
                break;
            case 'data2':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa,
                    COUNT(CASE WHEN a.revenue_class = 'Platinum' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_platinum,
                    COUNT(CASE WHEN a.revenue_class = 'Gold' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_gold,
                    COUNT(CASE WHEN a.revenue_class = 'Silver' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_silver,
                    COUNT(CASE WHEN a.revenue_class = 'Bronze' AND a.status_achievement = 'NOT ACHIEVED' THEN 1 ELSE NULL END) AS count_bronze
                    FROM dapot_ran_availability_rekon_not_achieved_monthly a 
                    WHERE a.nsa != ''
                    AND a.months = '" . $month . "' 
                    AND a.years = '" . $year . "' 
                    GROUP BY a.nsa
                    ORDER BY a.nsa, a.rtp");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function combatresume(Request $request)
    {   
        $dt = $request->all();
        $mode = $dt['mode'];

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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_combat');
        $week = db_get_last_update_week_query_with_year('dapot_ran_combat', $year);
        
        if (isset($dt['search_week'])) {
            $week = $dt['week'];
            $year = $dt['year'];
        } else {
            $week = $week;
            $year = $year;
        }

        switch ($mode) {
            case 'query_graph':
                $output = \DB::connection("mysql222a")->select("
                    SELECT type_combat, 
                    (
                        SELECT COUNT(*) 
                        FROM dapot_ran_combat a1 
                        WHERE a1.status_combat = 'On Air' 
                        AND a.type_combat = a1.type_combat
                        AND a.date_created = a1.date_created
                    ) AS on_air,
                    (
                        SELECT COUNT(*) 
                        FROM dapot_ran_combat a1 
                        WHERE a1.status_combat = 'Off Air' 
                        AND a.type_combat = a1.type_combat
                        AND a.date_created = a1.date_created
                    ) AS off_air
                    FROM dapot_ran_combat a 
                    WHERE weeks = '" . $week . "'
                    AND years = $year
                    AND type_combat != ''
                    AND type_combat != 'SNIPER'
                    GROUP BY type_combat
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_graph_distribution':
                $output = \DB::connection("mysql222a")->select("
                    SELECT rtp, 
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'ARROW'
                            AND a.date_created = a1.date_created
                        ) AS arrow,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'CKD'
                            AND a.date_created = a1.date_created
                        ) AS ckd,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'CRUISER'
                            AND a.date_created = a1.date_created
                        ) AS cruiser,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'RUSH/COMRO'
                            AND a.date_created = a1.date_created
                        ) AS comro,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'VELOCE'
                            AND a.date_created = a1.date_created
                        ) AS veloce
                    FROM dapot_ran_combat a
                    WHERE weeks = '" . $week . "'
                    AND years = $year
                    AND rtp != ''
                    GROUP BY rtp
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_graph_distribution':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $id_dapot_ran_combat = $dt['id_dapot_ran_combat'];
                $sql = \DB::connection("mysql222a")->select("
                        SELECT * FROM dapot_ran_combat
                        WHERE id_dapot_ran_combat = '" . $id_dapot_ran_combat . "'
                    ");
                $output = array();
                $output["sql"] = $query;
                foreach ($sql as $sql => $row) {
                    $output["title_content"] = "<h2>" . $row->site_combat_name . "</h2>";
                    $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Status</th>';
                    $output["content"] .= '<th>' . $row->status_combat . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Combat Name</th>';
                    $output["content"] .= '<th>' . $row->site_combat_name . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Site Id</th>';
                    $output["content"] .= '<th>' . $row->site_id . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Type Combat</th>';
                    $output["content"] .= '<th>' . $row->type_combat . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Longitude</th>';
                    $output["content"] .= '<th>' . $row->longitude . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Latitude</th>';
                    $output["content"] .= '<th>' . $row->latitude . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Band Frequency</th>';
                    $output["content"] .= '<th>' . $row->frequency . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Tanggal On Air</th>';
                    $output["content"] .= '<th>' . $row->tanggal_on_air . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Akhir Sewa Lahan</th>';
                    $output["content"] .= '<th>' . $row->akhir_sewa_lahan . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>NSA</th>';
                    $output["content"] .= '<th>' . $row->ns . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>RTP</th>';
                    $output["content"] .= '<th>' . $row->rtp . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Donor BTS</th>';
                    $output["content"] .= '<th>' . $row->donor_bts . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Alamat</th>';
                    $output["content"] .= '<th>' . $row->alamat . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>History combat</th>';
                    $output["content"] .= '<th>' . $row->history_combat . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '</table>';
                }
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $dt = $request->all();
                //isset($dt['page']) ? $noPage = $dt['page'] : $noPage = 1;
				//$offset = ($noPage - 1) * $dataPerPage;
                $offset = $dt['offset']; $dataPerPage = $dt['dataPerPage']; $kondisi = $dt['kondisi'];
                $output = \DB::connection("mysql222a")->select("
                    SELECT * FROM dapot_ran_combat
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    $kondisi
                    LIMIT  $offset,$dataPerPage
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $dt = $request->all();
                $kondisi = $dt['kondisi'];
                $output = \DB::connection("mysql222a")->select("SELECT COUNT(*) AS jumData 
                    FROM dapot_ran_combat
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function batterybackup(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_batere_backup');
        $week = db_get_last_update_week_query_with_year('dapot_ran_batere_backup', $year);

        switch ($mode) {
            case 'query':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                        (
                            SELECT COUNT(*) 
                            FROM ran_cluster_rtp a1
                            WHERE a1.id_nsa = a.id_nsa
                        ) AS count_rtpo
                    FROM nsa a
                    WHERE active = 1
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_detail':
                $dt = $request->all();
                $week = $dt['week']; $year = $dt['year']; $dt = $dt['id_nsa'];
                $output = \DB::connection("mysql222a")->select("SELECT *,
                        (
                            SELECT COUNT(a1.hours)
                            FROM dapot_ran_batere_backup a1
                            WHERE weeks = $week
                            AND years = $year
                            AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                            AND a1.hours < 1
                        ) AS kurang_sejam,
                        (
                            SELECT COUNT(a1.hours)
                            FROM dapot_ran_batere_backup a1
                            WHERE weeks = $week
                            AND years = $year
                            AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                            AND a1.hours BETWEEN 1 AND 2
                        ) AS antara_satu_dua_jam,
                        (
                            SELECT COUNT(a1.hours)
                            FROM dapot_ran_batere_backup a1
                            WHERE weeks = $week
                            AND years = $year
                            AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                            AND a1.hours BETWEEN 2 AND 3
                        ) AS antara_dua_tiga_jam,
                        (
                            SELECT COUNT(a1.hours)
                            FROM dapot_ran_batere_backup a1
                            WHERE weeks = $week
                            AND years = $year
                            AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                            AND a1.hours BETWEEN 3 AND 4
                        ) AS antara_tiga_empat_jam,
                        (
                            SELECT COUNT(a1.hours)
                            FROM dapot_ran_batere_backup a1
                            WHERE weeks = $week
                            AND years = $year
                            AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                            AND a1.hours > 4
                        ) AS lebih_empat_jam
                    FROM ran_cluster_rtp a
                    WHERE a.id_nsa = '" . $result->id_nsa . "'
                        ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $dt = $request->all();
                $week = $dt['week']; $year = $dt['year']; $kondisi = $dt['kondisi'];
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_batere_backup
                    WHERE weeks = '$week'
                    AND years = $year
                    AND latitude <> ''
                    AND longitude <> ''
                    $kondisi");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function topology(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_topology_type 
                    WHERE id_dapot_ran_topology_type = '" . $id_dapot_ran_topology_type . "'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type']; $frequency = $dt['frequency'];
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_topology
                    WHERE id_dapot_ran_topology_type = '" . $id_dapot_ran_topology_type . "' AND frequency = '" . $frequency . "'
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function utilizationweekly(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_utilization_weekly');
        $week = db_get_last_update_week_query_with_year('dapot_ran_utilization_weekly', $year);

        switch ($mode) {
            case 'query1':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_2g != ''
                    ORDER BY ROUND(utilisasi_2g) ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_3g != ''
                    ORDER BY ROUND(utilisasi_3g) ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_4g != ''
                    ORDER BY ROUND(utilisasi_4g) ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_2g != ''
                    ORDER BY ROUND(utilisasi_2g) DESC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_3g != ''
                    ORDER BY ROUND(utilisasi_3g) DESC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_4g != ''
                    ORDER BY ROUND(utilisasi_4g) DESC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_utilization_weekly
                    WHERE weeks = '$week'
                    AND years = $year
                    AND latitude <> ''
                    AND longitude <> ''
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function utilizationmonthly(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

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
        $year = db_get_last_update_year_by_month_query('dapot_ran_utilization_monthly');
        $month = db_get_last_update_month_query_by_year('dapot_ran_utilization_monthly', $year);

        switch ($mode) {
            case 'query1':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_monthly
                    WHERE months = '" . $month . "'
                    AND years = '" . $year . "'
                    AND utilisasi_2g != ''
                    ORDER BY ROUND(utilisasi_2g) ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_monthly
                    WHERE months = '" . $month . "'
                    AND years = '" . $year . "'
                    AND utilisasi_3g != ''
                    ORDER BY ROUND(utilisasi_3g) ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_monthly
                    WHERE months = '" . $month . "'
                    AND years = '" . $year . "'
                    AND utilisasi_4g != ''
                    ORDER BY ROUND(utilisasi_4g) ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_monthly
                    WHERE months = '" . $month . "'
                    AND years = '" . $year . "'
                    AND utilisasi_2g != ''
                    ORDER BY ROUND(utilisasi_2g) DESC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_monthly
                    WHERE months = '" . $month . "'
                    AND years = '" . $year . "'
                    AND utilisasi_3g != ''
                    ORDER BY ROUND(utilisasi_3g) DESC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_monthly
                    WHERE months = '" . $month . "'
                    AND years = '" . $year . "'
                    AND utilisasi_4g != ''
                    ORDER BY ROUND(utilisasi_4g) DESC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_utilization_monthly
                    WHERE months = '$month'
                    AND years = $year
                    AND latitude <> ''
                    AND longitude <> ''
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function sitebaseavail(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
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
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_availability_site_based');
        $week = db_get_last_update_week_query_with_year('dapot_ran_availability_site_based', $year);
        
        switch ($mode) {
            case 'ajax-detail-ran-combat':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $id_dapot_ran_combat = $dt['id_dapot_ran_combat'];
                $sql = \DB::connection("mysql222a")->select("
                        SELECT * FROM dapot_ran_combat
                        WHERE id_dapot_ran_combat = '" . $id_dapot_ran_combat . "'
                    ");
                $output = array();
                $output["sql"] = $sql;
                $row = mysqli_fetch_object($sql);
                $output["title_content"] = "<h2>" . $row->site_combat_name . "</h2>";
                $output["content"] = '<table class="table bordered border striped">';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Status</th>';
                $output["content"] .= '<th>' . $row->status_combat . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Combat Name</th>';
                $output["content"] .= '<th>' . $row->site_combat_name . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Site Id</th>';
                $output["content"] .= '<th>' . $row->site_id . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Type Combat</th>';
                $output["content"] .= '<th>' . $row->type_combat . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Longitude</th>';
                $output["content"] .= '<th>' . $row->longitude . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Latitude</th>';
                $output["content"] .= '<th>' . $row->latitude . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Band Frequency</th>';
                $output["content"] .= '<th>' . $row->frequency . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Tanggal On Air</th>';
                $output["content"] .= '<th>' . $row->tanggal_on_air . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Akhir Sewa Lahan</th>';
                $output["content"] .= '<th>' . $row->akhir_sewa_lahan . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>NSA</th>';
                $output["content"] .= '<th>' . $row->ns . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>RTP</th>';
                $output["content"] .= '<th>' . $row->rtp . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Donor BTS</th>';
                $output["content"] .= '<th>' . $row->donor_bts . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>Alamat</th>';
                $output["content"] .= '<th>' . $row->alamat . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '<tr>';
                $output["content"] .= '<th>History combat</th>';
                $output["content"] .= '<th>' . $row->history_combat . '</th>';
                $output["content"] .= '</tr>';
                $output["content"] .= '</table>';
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_availability_site_based
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Platinum'
                    AND nsa = 'NS Bandung'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Gold'
                    AND nsa = 'NS Bandung'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Silver'
                    AND nsa = 'NS Bandung'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Bronze'
                    AND nsa = 'NS Bandung'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Platinum'
                    AND nsa = 'NS Cirebon'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Gold'
                    AND nsa = 'NS Cirebon'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query8':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Silver'
                    AND nsa = 'NS Cirebon'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query9':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Bronze'
                    AND nsa = 'NS Cirebon'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query10':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Platinum'
                    AND nsa = 'NS Soreang'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query11':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Gold'
                    AND nsa = 'NS Soreang'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query12':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Silver'
                    AND nsa = 'NS Soreang'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query13':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Bronze'
                    AND nsa = 'NS Soreang'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query14':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Platinum'
                    AND nsa = 'NS Tasikmalaya'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query15':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Gold'
                    AND nsa = 'NS Tasikmalaya'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query16':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Silver'
                    AND nsa = 'NS Tasikmalaya'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query17':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_availability_site_based a1
                        WHERE a1.site_name = a.site_name
                        AND MONTH(a1.created_date) = MONTH(a.created_date)
                        AND years = '" . $year . "'
                    ) AS count_per_bulan
                    FROM dapot_ran_availability_site_based a
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND class = 'Bronze'
                    AND nsa = 'NS Tasikmalaya'
                    ORDER BY availability ASC
                    LIMIT 10
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function surveillance2g(Request $request)
    {
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
        $year = db_get_last_update_year_by_month_query('dapot_ran_surveillance_2g');
        $month = db_get_last_update_month_query_by_year('dapot_ran_surveillance_2g', $year);

        switch ($mode) {
            case 'query1':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_surveillance_2g
                    WHERE MONTH(date_created) = '" . $month . "'
                    AND YEAR(date_created) = $year
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function surveillance3g(Request $request)
    {
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
        $year = db_get_last_update_year_by_month_query('dapot_ran_surveillance_3g');
        $month = db_get_last_update_month_query_by_year('dapot_ran_surveillance_3g', $year);

        switch ($mode) {
            case 'query1':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_surveillance_3g
                    WHERE MONTH(date_created) = '" . $month . "'
                    AND YEAR(date_created) = $year
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function surveillance4g(Request $request)
    {
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
        $year = db_get_last_update_year_by_month_query('dapot_ran_surveillance_4g');
        $month = db_get_last_update_month_query_by_year('dapot_ran_surveillance_4g', $year);

        switch ($mode) {
            case 'query1':
                $id_dapot_ran_topology_type = $dt['id_dapot_ran_topology_type'];
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_surveillance_4g
                    WHERE MONTH(date_created) = '" . $month . "'
                    AND YEAR(date_created) = $year
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function ranreport(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                    MONTH(date_created) AS month_date_created, 
                    WEEK(date_created) AS week_date_created 
                    FROM dapot_ran_report
                    WHERE type_report = '" . $type_report . "' 
                    
                    LIMIT  $offset,$dataPerPage
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function raisaweeklyreport(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
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

            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $week = db_get_last_update_week_query_with_year('dapot_ran_rekon_availability_ne', $year);
        switch ($mode) {
            case 'getGraphRekon':
                $output = \DB::connection("mysql222a")->select("SELECT *, ROUND(tab.counts/ tab.sums * 100) as value FROM (
                        SELECT a.name_problem_cause_category,
                        (
                            SELECT COUNT(*) 
                            FROM dapot_ran_rekon_availability_ne a1
                            JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                            JOIN nsa c1 ON (b1.id_nsa = c1.id_nsa)
                            WHERE a1.name_problem_cause_category = a.name_problem_cause_category
                            AND a1.weeks = " . $week . "
                            AND a1.years = " . $year . "
                            AND c1.nsa_name = '" . $nsa_name . "'
                            AND a1.id_class_revenue = " . $id_class_revenue . "
                        ) AS counts,
                        (
                            SELECT COUNT(*)
                            FROM dapot_ran_rekon_availability_ne a1
                            JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                            JOIN nsa c1 ON (b1.id_nsa = c1.id_nsa)
                            WHERE  a1.weeks = " . $week . "
                            AND a1.years = " . $year . "
                            AND c1.nsa_name = '" . $nsa_name . "'
                            AND a1.id_class_revenue = " . $id_class_revenue . "
                            AND (
                                a1.name_problem_cause_category  = 'POWER' 
                            OR a1.name_problem_cause_category = 'TRANSMISI'
                            OR a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                            OR a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                            OR a1.name_problem_cause_category LIKE '%OTHER%'
                            )
                        ) AS sums
                        FROM dapot_ran_rekon_availability_ne a
                        WHERE a.weeks = " . $week . "
                        AND a.years = " . $year . "
                        AND a.id_class_revenue = " . $id_class_revenue . "
                        AND (
                            a.name_problem_cause_category  = 'POWER' 
                        OR a.name_problem_cause_category = 'TRANSMISI'
                        OR a.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        OR a.name_problem_cause_category = 'COMMUNITY ISSUE'
                        OR a.name_problem_cause_category LIKE '%OTHER%'
                        )
                        GROUP BY a.name_problem_cause_category
                    ) AS tab
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query_graph':
                $output = \DB::connection("mysql222a")->select("SELECT type_combat, 
                    (
                        SELECT COUNT(*) 
                        FROM dapot_ran_combat a1 
                        WHERE a1.status_combat = 'On Air' 
                        AND a.type_combat = a1.type_combat
                        AND a.date_created = a1.date_created
                    ) AS on_air,
                    (
                        SELECT COUNT(*) 
                        FROM dapot_ran_combat a1 
                        WHERE a1.status_combat = 'Off Air' 
                        AND a.type_combat = a1.type_combat
                        AND a.date_created = a1.date_created
                    ) AS off_air
                    FROM dapot_ran_combat a 
                    WHERE weeks = '" . $week . "'
                    AND years = $year
                    AND type_combat != ''
                    AND type_combat != 'SNIPER'
                    GROUP BY type_combat
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_graph_distribution':
                $output = \DB::connection("mysql222a")->select("SELECT rtp, 
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'ARROW'
                            AND a.date_created = a1.date_created
                        ) AS arrow,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'CKD'
                            AND a.date_created = a1.date_created
                        ) AS ckd,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'CRUISER'
                            AND a.date_created = a1.date_created
                        ) AS cruiser,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'RUSH/COMRO'
                            AND a.date_created = a1.date_created
                        ) AS comro,
                        (	
                            SELECT COUNT(*)
                            FROM dapot_ran_combat a1
                            WHERE a1.rtp = a.rtp
                            AND a1.type_combat = 'VELOCE'
                            AND a.date_created = a1.date_created
                        ) AS veloce
                    FROM dapot_ran_combat a
                    WHERE weeks = '" . $week . "'
                    AND years = $year
                    AND rtp != ''
                    GROUP BY rtp
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_unreturn_ns':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name,
                        (
                            SELECT COUNT(*)
                            FROM dapot_ran_spms a1
                            WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                            AND a1.missing_day BETWEEN 0 AND 1
                            AND a1.return_status = 'UNRETURN'
                            AND a1.spms_status = 'JABAR'
                            AND a1.weeks = '" . $week_spms . "'
                            AND a1.years = '" . $year . "'
                        ) AS total_0_7_hari,
                        (
                            SELECT COUNT(*)
                            FROM dapot_ran_spms a1
                            WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                            AND a1.missing_day BETWEEN 2 AND 5
                            AND a1.return_status = 'UNRETURN'
                            AND a1.spms_status = 'JABAR'
                            AND a1.weeks = '" . $week_spms . "'
                            AND a1.years = '" . $year . "'
                        ) AS total_8_14_hari,
                        (
                            SELECT COUNT(*)
                            FROM dapot_ran_spms a1
                            WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                            AND a1.missing_day >= 6
                            AND a1.return_status = 'UNRETURN'
                            AND a1.spms_status = 'JABAR'
                            AND a1.weeks = '" . $week_spms . "'
                            AND a1.years = '" . $year . "'
                        ) AS total_15_hari
                        FROM nsa a
                        WHERE a.expired_years = '$year' AND (SELECT MAX(a.expired_years) FROM nsa) OR a.expired_years BETWEEN '$year' AND (SELECT MAX(a.expired_years) FROM nsa)
                        ORDER BY a.nsa_name");
                    return Core::setResponse("success",$output);
                    break;
            case 'ajax-get-graph-source-log-alarm':
                $dt = $request->all();
                $year = $dt['year'];
                $data = \DB::connection("mysql222a")->select("
                        SELECT weeks
                        FROM dapot_ran_source_log_alarm
                        WHERE years = " . $year . "
                        GROUP BY weeks
                        ORDER BY weeks ASC
                    ");
                foreach ($data as $result) {
                    $week[] = "W" . $result->weeks;
                }
                $series = array();
                $query_rekon_category = \DB::connection("mysql222a")->select("
                        SELECT UPPER(rekon_category) AS rekon_category
                        FROM dapot_ran_source_log_alarm a
                        WHERE years = " . $year . "
                        AND UPPER(a.rekon_category) NOT IN  ('ACTIVITY', 'COMMCASE', '0', '', 'HARDWARE' )
                        GROUP BY a.rekon_category
                    ");
                //$data = db_query2list($query_rekon_category);
                $data = array("HARDWARE/SOFTWARE", "OTHER", "POWER", "TRANSMISI");
                $counter = 0;
                foreach ($data as $result) {
                    $series[$counter]['name'] = $result;
                    $data = \DB::connection("mysql222a")->select("
                            SELECT *, (tab.count_rekon / tab.tot_count_rekon ) * 100 AS percentage
                            FROM (
                                SELECT UPPER(rekon_category) AS rekon_category, weeks, COUNT(*) AS count_rekon,
                                (
                                    SELECT COUNT(*)
                                    FROM dapot_ran_source_log_alarm a1
                                    WHERE a1.years = a.years
                                    AND a1.weeks = a.weeks
                                    AND UPPER(a1.rekon_category) NOT IN  ('ACTIVITY', 'COMMCASE', '0', '', 'HARDWARE' )
                                ) AS tot_count_rekon
                                FROM dapot_ran_source_log_alarm a
                                WHERE years = " . $year . "
                                AND UPPER(rekon_category) = '" . $result . "'
                                GROUP BY a.rekon_category, weeks
                                ORDER BY a.weeks, a.rekon_category
                            ) AS tab
                        ");
                    $counter2 = 0;
                    foreach ($data as $res) {
                        $series[$counter]['data'][$counter2] = (float)number_format($res->percentage, 2);
                        $counter2++;
                    }
                    $counter++;
                }
                $output['categories'] = json_encode($week);
                $output['series'] = json_encode($series);
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarCore':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group = 'CORE'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarPower':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group = 'POWER'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarRadio':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group = 'RADIO'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarTransmission':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group = 'TRANSMISSION'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarValue':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa,  COUNT(*) AS qty
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week_spms . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'JABAR'
                    GROUP BY a.nsa");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarFaulty':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.return_status = 'FAULTY'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarROK':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.return_status = 'ROK'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'getDataGraphJabarUNRETURN':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.return_status = 'UNRETURN'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week_spms . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa) OR a.end_expired_years BETWEEN '$year' AND (SELECT MAX(a.end_expired_years) FROM nsa)
                    ORDER BY a.nsa_name");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_pie':
                $output = \DB::connection("mysql222a")->select("SELECT a.return_status,  COUNT(*) AS qty
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week_spms . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'JABAR'
                    GROUP BY a.return_status");
                return Core::setResponse("success",$output);
                break;
            case 'query1':
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
                    AND years = '" . $year . "'");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                        (
                            SELECT COUNT(*) 
                            FROM dapot_ran_rtp a1
                            WHERE a1.id_dapot_ran_ns = a.id_dapot_ran_ns
                        ) AS count_rtpo
                    FROM dapot_ran_ns a
                    WHERE id_dapot_ran_ns != '4'
                    AND (expired_years = '$year' AND (SELECT MAX(end_expired_years) FROM nsa) OR end_expired_years BETWEEN '$year' AND (SELECT MAX(end_expired_years) FROM nsa))
                    ORDER BY count_rtpo DESC");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql222a")->select("SELECT *,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 1
                        AND years = $year
                        AND frequency = '2G'
                    ) AS platinum_2g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 2
                        AND years = $year
                        AND frequency = '2G'
                    ) AS gold_2g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 3
                        AND years = $year
                        AND frequency = '2G'
                    ) AS silver_2g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 4
                        AND years = $year
                        AND frequency = '2G'
                    ) AS bronze_2g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 1
                        AND years = $year
                        AND frequency = '3G'
                    ) AS platinum_3g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 2
                        AND years = $year
                        AND frequency = '3G'
                    ) AS gold_3g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 3
                        AND years = $year
                        AND frequency = '3G'
                    ) AS silver_3g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 4
                        AND years = $year
                        AND frequency = '3G'
                    ) AS bronze_3g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 1
                        AND years = $year
                        AND frequency = '4G'
                    ) AS platinum_4g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 2
                        AND years = $year
                        AND frequency = '4G'
                    ) AS gold_4g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 3
                        AND years = $year
                        AND frequency = '4G'
                    ) AS silver_4g,
                    (
                        SELECT FORMAT(value_ran_availability_rtp, 2)
                        FROM dapot_ran_availability_rtp a1
                        WHERE a1.id_dapot_ran_rtp = a.id_dapot_ran_rtp
                        AND weeks = $week_rtp
                        AND id_class_revenue = 4
                        AND years = $year
                        AND frequency = '4G'
                    ) AS bronze_4g
                    FROM dapot_ran_rtp a
                    WHERE id_dapot_ran_ns = '" . $result->id_dapot_ran_ns . "'");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                        (
                            SELECT COUNT(*) 
                            FROM ran_cluster_rtp a1
                            WHERE a1.id_nsa = a.id_nsa
                            AND id_ran_dapot_category IN (2,3)
                        ) AS count_rtpo
                    FROM nsa a
                    WHERE expired_years = '$year' AND (SELECT MAX(end_expired_years) FROM nsa) OR end_expired_years BETWEEN '$year' AND (SELECT MAX(end_expired_years) FROM nsa)");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql222a")->select("SELECT *,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_platinum_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_platinum_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_platinum_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_platinum_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_platinum_2g,
                    
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_gold_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_gold_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_gold_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_gold_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_gold_2g,

                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_silver_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_silver_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_silver_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_silver_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_silver_2g,

                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_bronze_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_bronze_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_bronze_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_bronze_2g,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '2G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_bronze_2g,
                    
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_platinum_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_platinum_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_platinum_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_platinum_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                        ) AS outage_others_platinum_3G,

                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_gold_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_gold_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_gold_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_gold_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 2
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_gold_3G,

                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_silver_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_silver_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_silver_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_silver_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 3
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_silver_3G,

                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_bronze_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_bronze_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_bronze_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_bronze_3G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'OTHERS'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '3G'
                        AND a1.id_class_revenue = 4
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_others_bronze_3G,
                    
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'POWER'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '4G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_power_platinum_4G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'TRANSMISI'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '4G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_transmisi_platinum_4G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '4G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_hardware_software_platinum_4G,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_rekon_availability_ne a1 
                        JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                        WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                        AND b1.id_nsa = b.id_nsa
                        AND a1.frequency = '4G'
                        AND a1.id_class_revenue = 1
                        AND weeks = $week
                        AND years = $year
                    ) AS outage_community_issue_platinum_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'OTHERS'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 1
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_others_platinum_4G,

                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'POWER'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 2
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_power_gold_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'TRANSMISI'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 2
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_transmisi_gold_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 2
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_hardware_software_gold_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 2
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_community_issue_gold_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'OTHERS'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 2
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_others_gold_4G,

                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'POWER'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 3
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_power_silver_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'TRANSMISI'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 3
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_transmisi_silver_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 3
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_hardware_software_silver_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 3
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_community_issue_silver_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'OTHERS'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 3
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_others_silver_4G,

                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'POWER'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 4
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_power_bronze_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'TRANSMISI'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 4
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_transmisi_bronze_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category LIKE '%HARDWARE/SOFTWARE%'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 4
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_hardware_software_bronze_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'COMMUNITY ISSUE'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 4
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_community_issue_bronze_4G,
                    (
                    SELECT COUNT(*)
                    FROM dapot_ran_rekon_availability_ne a1 
                    JOIN ran_cluster_rtp b1 ON (a1.id_ran_cluster_rtp = b1.id_ran_cluster_rtp)
                    WHERE a1.name_problem_cause_category = 'OTHERS'
                    AND b1.id_nsa = b.id_nsa
                    AND a1.frequency = '4G'
                    AND a1.id_class_revenue = 4
                    AND weeks = $week
                    AND years = $year
                    ) AS outage_others_bronze_4G
                    
                    FROM nsa b
                    WHERE b.id_nsa = '" . $result->id_nsa . "'");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_availability_ne_based
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND frequency = '2G'
                    ORDER BY availability ASC
                    LIMIT $dataPerPage");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_availability_ne_based
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND frequency = '3G'
                    ORDER BY availability ASC
                    LIMIT $dataPerPage");
                return Core::setResponse("success",$output);
                break;
            case 'query8':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_availability_ne_based
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND frequency = '4G'
                    ORDER BY availability ASC
                    LIMIT $dataPerPage");
                return Core::setResponse("success",$output);
                break;
            case 'query9':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_mttr a
                    JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                    JOIN nsa c ON (b.id_nsa = c.id_nsa)
                    WHERE frequency = '2G'
                    AND weeks = $week
                    AND years = $year
                    ORDER BY a.sum_of_occurence DESC, site_name ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query10':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_mttr a
                    JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                    JOIN nsa c ON (b.id_nsa = c.id_nsa)
                    WHERE frequency = '3G'
                    AND weeks = $week
                    AND years = $year
                    ORDER BY a.sum_of_occurence DESC, site_name ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query11':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_mttr a
                    JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                    JOIN nsa c ON (b.id_nsa = c.id_nsa)
                    WHERE frequency = '2G'
                    AND weeks = $week
                    AND years = $year
                    ORDER BY a.sum_of_outage DESC, site_name ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query12':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_mttr a
                    JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                    JOIN nsa c ON (b.id_nsa = c.id_nsa)
                    WHERE frequency = '3G'
                    AND weeks = $week
                    AND years = $year
                    ORDER BY a.sum_of_outage DESC, site_name ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query13':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_2g != ''
                    ORDER BY ROUND(utilisasi_2g) ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query14':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_3g != ''
                    ORDER BY ROUND(utilisasi_3g) ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query15':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_4g != ''
                    ORDER BY ROUND(utilisasi_4g) ASC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query16':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_2g != ''
                    ORDER BY ROUND(utilisasi_2g) DESC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query17':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_3g != ''
                    ORDER BY ROUND(utilisasi_3g) DESC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query18':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM dapot_ran_utilization_weekly
                    WHERE weeks = '" . $week . "'
                    AND years = '" . $year . "'
                    AND utilisasi_4g != ''
                    ORDER BY ROUND(utilisasi_4g) DESC
                    LIMIT 5");
                return Core::setResponse("success",$output);
                break;
            case 'query19':
                $output = \DB::connection("mysql222a")->select("SELECT *, 
                        (
                            SELECT COUNT(*) 
                            FROM ran_cluster_rtp a1
                            WHERE a1.id_nsa = a.id_nsa
                        ) AS count_rtpo
                    FROM nsa a
                    WHERE expired_years = '$year' AND (SELECT MAX(end_expired_years) FROM nsa) OR end_expired_years BETWEEN '$year' AND (SELECT MAX(end_expired_years) FROM nsa)");
                return Core::setResponse("success",$output);
                break;
            case 'query20':
                $output = \DB::connection("mysql222a")->select("SELECT *,
                    (
                        SELECT COUNT(a1.hours)
                        FROM dapot_ran_batere_backup a1
                        WHERE weeks = $week_batere
                        AND years = $year
                        AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                        AND a1.hours < 1
                    ) AS kurang_sejam,
                    (
                        SELECT COUNT(a1.hours)
                        FROM dapot_ran_batere_backup a1
                        WHERE weeks = $week_batere
                        AND years = $year
                        AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                        AND a1.hours BETWEEN 1 AND 2
                    ) AS antara_satu_dua_jam,
                    (
                        SELECT COUNT(a1.hours)
                        FROM dapot_ran_batere_backup a1
                        WHERE weeks = $week_batere
                        AND years = $year
                        AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                        AND a1.hours BETWEEN 2 AND 3
                    ) AS antara_dua_tiga_jam,
                    (
                        SELECT COUNT(a1.hours)
                        FROM dapot_ran_batere_backup a1
                        WHERE weeks = $week_batere
                        AND years = $year
                        AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                        AND a1.hours BETWEEN 3 AND 4
                    ) AS antara_tiga_empat_jam,
                    (
                        SELECT COUNT(a1.hours)
                        FROM dapot_ran_batere_backup a1
                        WHERE weeks = $week_batere
                        AND years = $year
                        AND a1.id_ran_cluster_rtp = a.id_ran_cluster_rtp
                        AND a1.hours > 4
                    ) AS lebih_empat_jam
                FROM ran_cluster_rtp a
                WHERE a.id_nsa = '" . $result->id_nsa . "'");
                return Core::setResponse("success",$output);
                break;
            case 'query21':
                $output = \DB::connection("mysql222a")->select("");
                return Core::setResponse("success",$output);
                break;
            case 'query22':
                $output = \DB::connection("mysql222a")->select("");
                return Core::setResponse("success",$output);
                break;
            case 'query23':
                $output = \DB::connection("mysql222a")->select("");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function spmsweekly(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
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
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_spms');
        $week = db_get_last_update_week_query_with_year('dapot_ran_spms', $year);

        switch ($mode) {
            case 'query_graph':
                $output = \DB::connection("mysql222a")->select("SELECT a.region, COUNT(*) AS grand_total,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms a1
                        WHERE a1.region = a.region
                        AND a1.return_actual_day BETWEEN 0 AND 7
                        AND a1.spms_status = 'NASIONAL'
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_0_7_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms a1
                        WHERE a1.region = a.region
                        AND a1.return_actual_day BETWEEN 8 AND 14
                        AND a1.spms_status = 'NASIONAL'
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_8_14_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms a1
                        WHERE a1.region = a.region
                        AND a1.return_actual_day > 14
                        AND a1.spms_status = 'NASIONAL'
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_15_hari
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'NASIONAL'
                    GROUP BY a.region
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_unreturn_ns':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms a1
                        WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.return_actual_day BETWEEN 0 AND 7
                        AND a1.return_status = 'UNRETURN'
                        AND a1.spms_status = 'JABAR'
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_0_7_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms a1
                        WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.return_actual_day BETWEEN 8 AND 14
                        AND a1.return_status = 'UNRETURN'
                        AND a1.spms_status = 'JABAR'
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_8_14_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms a1
                        WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.return_actual_day > 14
                        AND a1.return_status = 'UNRETURN'
                        AND a1.spms_status = 'JABAR'
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_15_hari
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-detail-ran-spms':
                    $dt = $request->all();
                    date_default_timezone_set("Asia/Jakarta");
                    $id_dapot_ran_spms = $dt['id_dapot_ran_spms']; 
                    $sql = \DB::connection("mysql222a")->select("
                            SELECT * FROM dapot_ran_spms
                            WHERE id_dapot_ran_spms = '". $id_dapot_ran_spms ."'
                        ");
                    $output = array();
                    $output["sql"] = $sql;
                    $row = mysqli_fetch_object($sql);
                    $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Missing Day</th>';
                    $output["content"] .= '<th>' . $row->missing_day . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Ticket Number</th>';
                    $output["content"] .= '<th>' . $row->ticket_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Vendor</th>';
                    $output["content"] .= '<th>' . $row->vendor . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Request Number</th>';
                    $output["content"] .= '<th>' . $row->request_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Request Time</th>';
                    $output["content"] .= '<th>' . $row->request_time . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery Time</th>';
                    $output["content"] .= '<th>' . $row->delivery_time . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery SLA</th>';
                    $output["content"] .= '<th>' . $row->delivery_sla . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery Actual Hour</th>';
                    $output["content"] .= '<th>' . $row->delivery_actual_hour . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery Achievement</th>';
                    $output["content"] .= '<th>' . $row->delivery_achievement . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Time</th>';
                    $output["content"] .= '<th>' . $row->return_time . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return SLA</th>';
                    $output["content"] .= '<th>' . $row->return_sla . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Actual Day</th>';
                    $output["content"] .= '<th>' . $row->return_actual_day . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Status</th>';
                    $output["content"] .= '<th>' . $row->return_status . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Achievement</th>';
                    $output["content"] .= '<th>' . $row->return_achievement . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>SLA Extension</th>';
                    $output["content"] .= '<th>' . $row->sla_extension . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Product Number</th>';
                    $output["content"] .= '<th>' . $row->product_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Serial Number</th>';
                    $output["content"] .= '<th>' . $row->serial_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Description</th>';
                    $output["content"] .= '<th>' . $row->description . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Severity</th>';
                    $output["content"] .= '<th>' . $row->severity . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Product Group</th>';
                    $output["content"] .= '<th>' . $row->product_group . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Site Id</th>';
                    $output["content"] .= '<th>' . $row->site_id . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Site Name</th>';
                    $output["content"] .= '<th>' . $row->site_name . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Ne Id</th>';
                    $output["content"] .= '<th>' . $row->ne_id . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Requester Name</th>';
                    $output["content"] .= '<th>' . $row->requester_name . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '</table>';        
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_moduleCore':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group = 'CORE'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_modulePower':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group IN ('POWER', 'RADIO & POWER')
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_moduleRadio':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group IN ('RADIO', 'RADIO & POWER')
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_moduleTransmission':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.product_group IN ('TRANSMISSION', 'TRANSPORT')
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_pie_requested_module':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa,  COUNT(*) AS qty
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'JABAR'
                    GROUP BY a.nsa
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_bar':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.return_status = 'FAULTY'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_barROK':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.return_status = 'ROK'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_barUNRETURN':
                $output = \DB::connection("mysql222a")->select("
                    SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms a1
                        WHERE a1.return_status = 'UNRETURN'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.weeks = '" . $week . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_pie':
                $output = \DB::connection("mysql222a")->select("SELECT a.return_status,  COUNT(*) AS qty
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'JABAR'
                    GROUP BY a.return_status
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week . "'
                    AND a.years = '" . $year . "'
                    AND a.return_status = 'UNRETURN'
                    AND a.spms_status = 'JABAR'
                    $kondisi
                    LIMIT  $offset,$dataPerPage
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222a")->select("SELECT COUNT(*) AS jumData 
                    FROM dapot_ran_spms a
                    WHERE a.weeks = '" . $week . "'
                    AND a.years = '" . $year . "'
                    AND a.return_status = 'UNRETURN'
                    AND a.spms_status = 'JABAR'
                    $kondisi
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function spmsmonthly(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
        function db_get_last_update_months_query_by_year($table, $year, $conn)
        {
            $query = \DB::connection("mysql222a")->select("
                SELECT months AS last_month
                FROM " . $table . "
                WHERE years = $year
                GROUP BY months
                ORDER BY months DESC
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
        function db_get_last_update_years_by_month_query($table, $conn)
        {
            $query = \DB::connection("mysql222a")->select("
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
        $year = db_get_last_update_years_by_month_query('dapot_ran_spms_monthly');
        $months = db_get_last_update_months_query_by_year('dapot_ran_spms_monthly', $year);

        switch ($mode) {
            case 'query_graph':
                $output = \DB::connection("mysql222a")->select("SELECT a.region, COUNT(*) AS grand_total,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.region = a.region
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.return_actual_day BETWEEN 0 AND 7
                        AND a1.spms_status = 'NASIONAL'
                    ) AS total_0_7_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.region = a.region
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.return_actual_day BETWEEN 8 AND 14
                        AND a1.spms_status = 'NASIONAL'
                    ) AS total_8_14_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.region = a.region
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.return_actual_day > 14
                        AND a1.spms_status = 'NASIONAL'
                    ) AS total_15_hari
                    FROM dapot_ran_spms_monthly a
                    WHERE a.months = '" . $months . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'NASIONAL'
                    GROUP BY a.region
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_unreturn_ns':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.return_actual_day BETWEEN 0 AND 7
                        AND a1.return_status = 'UNRETURN'
                        AND a1.spms_status = 'JABAR'
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_0_7_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.return_actual_day BETWEEN 8 AND 14
                        AND a1.return_status = 'UNRETURN'
                        AND a1.spms_status = 'JABAR'
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_8_14_hari,
                    (
                        SELECT COUNT(*)
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.return_actual_day > 14
                        AND a1.return_status = 'UNRETURN'
                        AND a1.spms_status = 'JABAR'
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                    ) AS total_15_hari
                    FROM nsa a
                    WHERE a.expired_years = 2018
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-detail-ran-spms':
                    $dt = $request->all();
                    date_default_timezone_set("Asia/Jakarta");
                    $id_dapot_ran_spms = $dt['id_dapot_ran_spms']; 
                    $sql = \DB::connection("mysql222a")->select("
                            SELECT * FROM dapot_ran_spms
                            WHERE id_dapot_ran_spms = '". $id_dapot_ran_spms ."'
                        ");
                    $output = array();
                    $output["sql"] = $sql;
                    $row = mysqli_fetch_object($sql);
                    $output["content"] = '<table class="table bordered border striped">';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Missing Day</th>';
                    $output["content"] .= '<th>' . $row->missing_day . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Ticket Number</th>';
                    $output["content"] .= '<th>' . $row->ticket_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Vendor</th>';
                    $output["content"] .= '<th>' . $row->vendor . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Request Number</th>';
                    $output["content"] .= '<th>' . $row->request_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Request Time</th>';
                    $output["content"] .= '<th>' . $row->request_time . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery Time</th>';
                    $output["content"] .= '<th>' . $row->delivery_time . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery SLA</th>';
                    $output["content"] .= '<th>' . $row->delivery_sla . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery Actual Hour</th>';
                    $output["content"] .= '<th>' . $row->delivery_actual_hour . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Delivery Achievement</th>';
                    $output["content"] .= '<th>' . $row->delivery_achievement . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Time</th>';
                    $output["content"] .= '<th>' . $row->return_time . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return SLA</th>';
                    $output["content"] .= '<th>' . $row->return_sla . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Actual Day</th>';
                    $output["content"] .= '<th>' . $row->return_actual_day . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Status</th>';
                    $output["content"] .= '<th>' . $row->return_status . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Return Achievement</th>';
                    $output["content"] .= '<th>' . $row->return_achievement . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>SLA Extension</th>';
                    $output["content"] .= '<th>' . $row->sla_extension . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Product Number</th>';
                    $output["content"] .= '<th>' . $row->product_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Serial Number</th>';
                    $output["content"] .= '<th>' . $row->serial_number . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Description</th>';
                    $output["content"] .= '<th>' . $row->description . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Severity</th>';
                    $output["content"] .= '<th>' . $row->severity . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Product Group</th>';
                    $output["content"] .= '<th>' . $row->product_group . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Site Id</th>';
                    $output["content"] .= '<th>' . $row->site_id . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Site Name</th>';
                    $output["content"] .= '<th>' . $row->site_name . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Ne Id</th>';
                    $output["content"] .= '<th>' . $row->ne_id . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '<tr>';
                    $output["content"] .= '<th>Requester Name</th>';
                    $output["content"] .= '<th>' . $row->requester_name . '</th>';
                    $output["content"] .= '</tr>';
                    $output["content"] .= '</table>';        
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_moduleCore':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.product_group = 'CORE'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_modulePower':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.product_group IN ('POWER', 'RADIO & POWER')
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_moduleRadio':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.product_group IN ('RADIO', 'RADIO & POWER')
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_requested_moduleTransmission':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.product_group IN ('TRANSMISSION', 'TRANSPORT')
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_pie_requested_module':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa,  COUNT(*) AS qty
                    FROM dapot_ran_spms_monthly a
                    WHERE a.months = '" . $months . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'JABAR'
                    GROUP BY a.nsa
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_bar':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.return_status = 'FAULTY'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_barROK':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.return_status = 'ROK'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_barUNRETURN':
                $output = \DB::connection("mysql222a")->select("SELECT a.nsa_name, IFNULL((
                        SELECT COUNT(*) AS qty
                        FROM dapot_ran_spms_monthly a1
                        WHERE a1.return_status = 'UNRETURN'
                        AND a1.nsa = SUBSTR(a.nsa_name, 5)
                        AND a1.months = '" . $months . "'
                        AND a1.years = '" . $year . "'
                        AND a1.spms_status = 'JABAR'
                        GROUP BY a1.nsa
                    ), 0) AS qty
                    FROM nsa a
                    WHERE a.expired_years = 2017
                    ORDER BY a.nsa_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_return_status_pie':
                $output = \DB::connection("mysql222a")->select("SELECT a.return_status,  COUNT(*) AS qty
                    FROM dapot_ran_spms_monthly a
                    WHERE a.months = '" . $months . "'
                    AND a.years = '" . $year . "'
                    AND a.spms_status = 'JABAR'
                    GROUP BY a.return_status
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_spms_monthly a
                    WHERE a.months = '" . $months . "'
                    AND a.years = '" . $year . "'
                    AND a.return_status = 'UNRETURN'
                    AND a.spms_status = 'JABAR'
                    $kondisi
                    LIMIT  $offset,$dataPerPage
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222a")->select("SELECT COUNT(*) AS jumData 
                    FROM dapot_ran_spms_monthly a
                    WHERE a.months = '" . $months . "'
                    AND a.years = '" . $year . "'
                    AND a.return_status = 'UNRETURN'
                    AND a.spms_status = 'JABAR'
                    $kondisi
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function spmsrecontransaction(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
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
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_spms');
        $week = db_get_last_update_week_query_with_year('dapot_ran_spms', $year);

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql222a")->select("SELECT * 
										FROM dapot_ran_quarter_report
										LIMIT  $offset,$dataPerPage
                ");
            return Core::setResponse("success",$output);
            break;
        case 'query2':
                $output = \DB::connection("mysql222a")->select("SELECT COUNT(*) AS jumData 
								FROM dapot_ran_quarter_report
                ");
            return Core::setResponse("success",$output);
            break;
        }
    }

    public function rparadiomaterial(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];
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
            foreach ($q as $q => $r) {
                if (empty($r)) {
                    return 1;
                } else {
                    return $r->last_update;
                }
            }
        }
        $year = db_get_last_update_year_query('dapot_ran_spms');
        $get_last_update_week = db_get_last_update_week_query_with_year('dapot_ran_spms', $year);

        switch ($mode) {
            case 'query_graph':
                $output = \DB::connection("mysql222a")->select("SELECT a.region, COUNT(*) AS grand_total,
                (
                    SELECT COUNT(*)
                    FROM dapot_ran_spms a1
                    WHERE a1.region = a.region
                    AND a1.return_status LIKE '%0%'
                    AND a.spms_status = 'NASIONAL'
                    AND a1.weeks = '" . $week . "'
                    AND a1.years = '" . $year . "'
                ) AS total_0_7_hari,
                (
                    SELECT COUNT(*)
                    FROM dapot_ran_spms a1
                    WHERE a1.region = a.region
                    AND a1.return_status LIKE '%8%'
                    AND a.spms_status = 'NASIONAL'
                    AND a1.weeks = '" . $week . "'
                    AND a1.years = '" . $year . "'
                ) AS total_8_14_hari,
                (
                    SELECT COUNT(*)
                    FROM dapot_ran_spms a1
                    WHERE a1.region = a.region
                    AND a1.return_status LIKE '%15%'
                    AND a.spms_status = 'NASIONAL'
                    AND a1.weeks = '" . $week . "'
                    AND a1.years = '" . $year . "'
                ) AS total_15_hari
                FROM dapot_ran_spms a
                WHERE a.weeks = '" . $week . "'
                AND a.years = '" . $year . "'
                AND a.spms_status = 'NASIONAL'
                GROUP BY a.region
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-detail-ran-spms':
                $output = \DB::connection("mysql222a")->select("
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph2_nsa':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM nsa WHERE expired_years = (SELECT MAX(expired_years) FROM nsa)
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph2_nsa2':
                $output = \DB::connection("mysql222a")->select("SELECT a.tanggal_keluar
                    FROM dapot_ran_outgoing_item a
                    GROUP BY a.tanggal_keluar");
                return Core::setResponse("success",$output);
                break;
            case 'graph2_nsa3':
                $output = \DB::connection("mysql222a")->select("SELECT COUNT(*) AS value
                    FROM dapot_ran_outgoing_item a 
                    JOIN ran_cluster_rtp b ON (a.id_ran_cluster_rtp = b.id_ran_cluster_rtp)
                    WHERE a.tanggal_keluar = '" . $res->tanggal_keluar . "'
                    AND b.id_nsa = '" . $result->id_nsa . "'");
                return Core::setResponse("success",$output);
                break;
            case 'graph2':
                $output = \DB::connection("mysql222a")->select("SELECT * FROM ran_cluster_rtp WHERE expired_years = (SELECT MAX(expired_years) FROM ran_cluster_rtp)
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph2a':
                $output = \DB::connection("mysql222a")->select("SELECT a.tanggal_keluar
                FROM dapot_ran_outgoing_item a
                GROUP BY a.tanggal_keluar
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph2b':
                $output = \DB::connection("mysql222a")->select("SELECT IFNULL(SUM(qty), 0) AS value
                    FROM dapot_ran_outgoing_item a
                    WHERE a.tanggal_keluar = '" . $res->tanggal_keluar . "'
                    AND a.id_ran_cluster_rtp = '" . $result->id_ran_cluster_rtp . "'
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph1':
                $output = \DB::connection("mysql222a")->select("SELECT a.material_name
                FROM dapot_ran_outgoing_item a
                GROUP BY a.material_name
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph1a':
                $output = \DB::connection("mysql222a")->select("SELECT IFNULL(SUM(qty), 0) AS value
                    FROM dapot_ran_outgoing_item a
                    WHERE a.tanggal_keluar = '" . $res->tanggal_keluar . "'
                    AND a.material_name = '" . $result->material_name . "'
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph_material_stock':
                $output = \DB::connection("mysql222a")->select("SELECT *
                FROM dapot_ran_stock_rpa a
                GROUP BY a.material_name
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'graph_material_stock2':
                $output = \DB::connection("mysql222a")->select("SELECT *, SUM(qty_unit) AS qty
                FROM dapot_ran_stock_rpa a
                GROUP BY a.material_name
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql222a")->select("SELECT * 
                    FROM dapot_ran_stock_rpa a
                    $kondisi
                    LIMIT  $offset,$dataPerPage
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222a")->select("SELECT COUNT(*) AS jumData 
                    FROM dapot_ran_stock_rpa a
                    $kondisi
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corecsmsscapacity(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query_date':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_REF_VLR_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_time':
                $output = \DB::connection("mysql145")->select("SELECT a.TIME_ID
                    FROM N_CORE_REF_VLR_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    GROUP BY a.TIME_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_mgw':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
                    FROM N_CORE_REF_VLR_HOURLY a
                    WHERE (MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')) 
                    and DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_value':
                $output = \DB::connection("mysql145")->select("SELECT VLR_OCCUPANCY
                    FROM N_CORE_REF_VLR_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_REF_VLR_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage2':
                $output = \DB::connection("mysql145")->select("SELECT a.TIME_ID
                    FROM N_CORE_REF_VLR_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    GROUP BY a.TIME_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage3':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
                    FROM N_CORE_REF_VLR_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW() and MSS_NAME LIKE '%MSSBDG%'
                    GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage4':
                $output = \DB::connection("mysql145")->select("SELECT VLR_USAGE
                    FROM N_CORE_REF_VLR_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage5':
                $output = \DB::connection("mysql145")->select("SELECT VLR_USAGE, DATE_ID, TIME_ID FROM (
                        SELECT SUM(VLR_USAGE) AS VLR_USAGE,DATE_ID, TIME_ID
                        FROM N_CORE_REF_VLR_HOURLY a1
                        WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                        AND MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
                        AND MONTH(DATE_ID) <> 0
                        GROUP BY DATE_ID,HOUR(TIME_ID)
                        ORDER BY DATE_ID 
                    ) TAB 
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_license':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_license2':
                $output = \DB::connection("mysql145")->select("SELECT a.TIME_ID
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    AND MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
                    GROUP BY a.TIME_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_license3':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a
                    WHERE MSS_NAME LIKE '%BDG%' OR MSS_NAME LIKE '%CRB%'
                    GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_license4':
                $output = \DB::connection("mysql145")->select("SELECT PEAK_CAP_LIC_USAGE
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_csfb_usage':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_csfb_usage2':
                $output = \DB::connection("mysql145")->select("
                    SELECT a.TIME_ID
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    GROUP BY HOUR(a.TIME_ID)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_csfb_usage3':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a
                    WHERE MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
                    GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_csfb_usage4':
                $output = \DB::connection("mysql145")->select("SELECT round((PEAK_CAP_LIC_USAGE*CAP_LIC_LIMIT)/100,2) AS csfb_usage
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_csfb_usage5':
                $output = \DB::connection("mysql145")->select("SELECT round(SUM(((PEAK_CAP_LIC_USAGE*CAP_LIC_LIMIT)/100)),2) AS csfb_usage
                    FROM N_CORE_LTE_CSFBLIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
                    AND MONTH(DATE_ID) <> 0
                    GROUP BY DATE_ID,HOUR(TIME_ID)
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_cpu_load':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_REF_COMP_LOAD_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_cpu_load2':
                $output = \DB::connection("mysql145")->select("SELECT SEC_TO_TIME((TIME_TO_SEC(concat(DATE_ID,' ',a.TIME_ID)) DIV 3600) * 3600) as TIME_ID
                    FROM N_CORE_REF_COMP_LOAD_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    AND MSS_NAME in (MSS_NAME like '%BDG%' or MSS_NAME like '%CRB%')
                    GROUP BY SEC_TO_TIME((TIME_TO_SEC(concat(DATE_ID,' ',a.TIME_ID)) DIV 3600) * 3600)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_cpu_load3':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
                    FROM N_CORE_REF_COMP_LOAD_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW() and MSS_NAME LIKE '%MSSBDG%'
                    GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_cpu_load4':
                $output = \DB::connection("mysql145")->select("SELECT COMPL_LOAD_PERCENT
                    FROM N_CORE_REF_COMP_LOAD_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
                    AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corecsmssscrccr(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query_date':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                        FROM N_CORE_SCR_INCLUDE_RADIO_HOUR a
                        WHERE HOUR_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                        GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_time':
                $output = \DB::connection("mysql145")->select("SELECT a.HOUR_ID
                        FROM N_CORE_SCR_INCLUDE_RADIO_HOUR a
                        WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                        GROUP BY HOUR(a.HOUR_ID)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_mgw':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
						FROM N_CORE_SCR_INCLUDE_RADIO_HOUR a
						WHERE MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
						GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_value':
                $output = \DB::connection("mysql145")->select("SELECT SCR
                        FROM N_CORE_SCR_INCLUDE_RADIO_HOUR a1
                        WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW() 
                        AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                        GROUP BY DATE_ID , HOUR_ID
                        ORDER BY DATE_ID, HOUR_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_ccr':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                        FROM N_CORE_CCR_INCLUDE_RADIO_HOURLY a
                        WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                        GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_ccr1':
                $output = \DB::connection("mysql145")->select("SELECT a.TIME_ID
                        FROM N_CORE_CCR_INCLUDE_RADIO_HOURLY a
                        WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                        GROUP BY HOUR(a.TIME_ID)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_ccr2':
                $output = \DB::connection("mysql145")->select("SELECT a.MSS_NAME
                    FROM N_CORE_CCR_INCLUDE_RADIO_HOURLY a
                    WHERE MSS_NAME LIKE '%MSSBDG%' OR MSS_NAME IN ('MSSCRB1')
                    GROUP BY a.MSS_NAME
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_ccr3':
                $output = \DB::connection("mysql145")->select("SELECT CCR
                    FROM N_CORE_CCR_INCLUDE_RADIO_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 8 DAY) AND NOW()
                    AND a1.MSS_NAME = '" . $result_mss->MSS_NAME . "'
                    GROUP BY HOUR(TIME_ID), DATE_ID
                    ORDER BY DATE_ID, TIME_ID
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corecsmssmgwcapacity(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                    SELECT a.TIME_ID
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    GROUP BY a.TIME_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage2':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a
                    " . $whereclus . " 
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage3':
                $output = \DB::connection("mysql145")->select("SELECT round((CC_PEAK/100)*CC_FEATURE_CAPACITY,2) as val
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    AND a1.MGW_NAME = '" . $result_mgw->mgw_name . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage4':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    " . $whereclus . " 
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage5':
                $output = \DB::connection("mysql145")->select("SELECT round((CC_PEAK/100)*CC_FEATURE_CAPACITY,2) as val
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    AND a1.MGW_NAME = '" . $result_mgw->mgw_name . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage6':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a
                    " . $whereclus . " 
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage7':
                $output = \DB::connection("mysql145")->select("SELECT round((CC_PEAK/100)*CC_FEATURE_CAPACITY,2) as val
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    AND a1.MGW_NAME = '" . $result_mgw->mgw_name . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    GROUP BY a.DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy1':
                $output = \DB::connection("mysql145")->select("SELECT a.TIME_ID
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    GROUP BY a.TIME_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy2':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a
                    " . $whereclus . " 
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy3':
                $output = \DB::connection("mysql145")->select("SELECT " . $field . "
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    AND a1.MGW_NAME = '" . $result_mgw->mgw_name . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy4':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    " . $whereclus . " 
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy5':
                $output = \DB::connection("mysql145")->select("SELECT " . $field . "
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    AND a1.MGW_NAME = '" . $result_mgw->mgw_name . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy6':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a
                    " . $whereclus . " 
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_occupancy7':
                $output = \DB::connection("mysql145")->select("SELECT " . $field . "
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                    WHERE DATE_ID BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
                    AND a1.MGW_NAME = '" . $result_mgw->mgw_name . "'
                    ORDER BY DATE_ID
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corecsmssmgwchecklist(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql145")->select("SELECT * FROM (	
                        SELECT MAX($field) AS field_value, a.MGW_NAME, a.DATE_ID
                        FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                        WHERE MONTH(a.DATE_ID) = $month
                        AND YEAR(a.DATE_ID) = $year
                        GROUP BY a.MGW_NAME, a.DATE_ID
                        UNION 
                        SELECT MAX($field) AS field_value, a.MGW_NAME, a.DATE_ID
                        FROM N_CORE_REF_MGWU6_LIC_HOURLY a
                        WHERE MONTH(a.DATE_ID) = $month
                        AND YEAR(a.DATE_ID) = $year
                        GROUP BY a.MGW_NAME, a.DATE_ID
                    ) TAB
                    WHERE MGW_NAME LIKE '%BDG%' OR MGW_NAME IN ('WCRB1')
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql145")->select("SELECT a.mgw_name,
                        (
                            SELECT MAX(a1.CC_AVERAGE)
                            FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS SCC,
                        (
                            SELECT MAX(a1.PC_IU_IP_AVERAGE)
                            FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS RNC_OVER_IP,
                        (
                            SELECT MAX(a1.PC_NB_IP_AVERAGE)
                            FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS NB_OVER_IP,
                        (
                            SELECT MAX(a1.PC_MB_AVERAGE)
                            FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS SIP,
                        (
                            SELECT MAX(a1.PC_AOIP_AVERAGE)
                            FROM N_CORE_REF_ALL_LICENSE_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS BSC_OVER_IP
                    FROM N_CORE_REF_ALL_LICENSE_HOURLY a
                    WHERE MGW_NAME LIKE '%BDG%' OR MGW_NAME IN ('WCRB1')
                    GROUP BY a.mgw_name
                    
                    UNION 
                    
                    SELECT a.mgw_name,
                        (
                            SELECT MAX(a1.CC_AVERAGE)
                            FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS SCC,
                        (
                            SELECT MAX(a1.PC_IU_IP_AVERAGE)
                            FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS RNC_OVER_IP,
                        (
                            SELECT MAX(a1.PC_NB_IP_AVERAGE)
                            FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS NB_OVER_IP,
                        (
                            SELECT MAX(a1.PC_MB_AVERAGE)
                            FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS SIP,
                        (
                            SELECT MAX(a1.PC_AOIP_AVERAGE)
                            FROM N_CORE_REF_MGWU6_LIC_HOURLY a1
                            WHERE a1.MGW_NAME = a.MGW_NAME
                            AND MONTH(a1.DATE_ID) = '" . $month . "' and YEAR(a1.DATE_ID) = '" . $year . "'
                        ) AS BSC_OVER_IP
                    FROM N_CORE_REF_MGWU6_LIC_HOURLY a
                    WHERE MGW_NAME LIKE '%BDG%' OR MGW_NAME IN ('WCRB1')
                    GROUP BY a.mgw_name
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function dapotcorelacci(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql145")->select("SELECT *,(SELECT SUM(jumlah) FROM dapot_core a1 WHERE a.id_network_element = a1.id_network_element) AS jumlah_ne FROM network_element a WHERE id_division = 1
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql145")->select("SELECT *,(SELECT SUM(jumlah) FROM dapot_core a1 WHERE a.id_site = a1.id_site) AS jumlah_site FROM site a WHERE id_division = 1
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql145")->select("SELECT * FROM site WHERE id_division = 1 ORDER BY  id_site ASC
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql145")->select("SELECT *, $jumlah_ne FROM network_element a WHERE id_division = 1
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql145")->select("SELECT *, $total_ne FROM network_element a WHERE id_division = 1
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql145")->select("SELECT * FROM dapot_lacci $where_out
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function coreps2gnetwork(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql145")->select("SELECT a.mydate
                    FROM monitoring_sgsn_2g a
                    $condition
                    GROUP BY a.mydate
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql145")->select("SELECT a.object_name
                    FROM monitoring_sgsn_2g a
                    WHERE object_name IN ('SGBDG5','SGBDG6','SGBDG7')
                    GROUP BY a.object_name");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql145")->select("SELECT gb_mode_attach_sr
                    FROM monitoring_sgsn_2g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql145")->select("SELECT gb_mode_pdp_context_activation_sr
                    FROM monitoring_sgsn_2g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql145")->select("SELECT gb_mode_intra_rau_sr
                    FROM monitoring_sgsn_2g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql145")->select("SELECT gb_mode_inter_rau_sr
                    FROM monitoring_sgsn_2g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function coreps3gnetwork(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql145")->select("SELECT a.mydate
                    FROM monitoring_sgsn_3g a
                    $condition
                    GROUP BY a.mydate
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql145")->select("SELECT a.object_name
                    FROM monitoring_sgsn_3g a
                    WHERE object_name IN ('SGBDG5','SGBDG6','SGBDG7','SBDG8')
                    GROUP BY a.object_name");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql145")->select("SELECT iu_mode_attach_sr
                    FROM monitoring_sgsn_3g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql145")->select("SELECT iu_mode_inter_sau_sr
                    FROM monitoring_sgsn_3g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql145")->select("SELECT iu_mode_intra_sau
                    FROM monitoring_sgsn_3g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql145")->select("SELECT iu_mode_pdp_context_activation_sr
                    FROM monitoring_sgsn_3g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function coreps4gnetwork(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql145")->select("SELECT a.mydate
                    FROM monitoring_sgsn_4g a
                    $condition
                    GROUP BY a.mydate
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql145")->select("SELECT a.object_name
                    FROM monitoring_sgsn_4g a
                    WHERE object_name IN ('SGBDG5','SGBDG6','SGBDG7')
                    GROUP BY a.object_name");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql145")->select("SELECT pdn_connectivity_sr
                    FROM monitoring_sgsn_4g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql145")->select("SELECT combined_attach_sr
                    FROM monitoring_sgsn_4g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql145")->select("SELECT default_bearer_sr
                    FROM monitoring_sgsn_4g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql145")->select("SELECT intra_tau_sr
                    FROM monitoring_sgsn_4g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $output = \DB::connection("mysql145")->select("SELECT service_request_sr
                    FROM monitoring_sgsn_4g a1
                    $condition 
                    AND a1.object_name = '" . $result_sgsn->object_name . "'
                    GROUP BY mydate
                    ORDER BY mydate");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corepssgsn(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'sgsn_occ':
                $sql_sgsn = \DB::connection("mysql145")->select("SELECT a.date_id,SUM(a.sampling) sample, sgsn,  SUM(a.sau)*100/2100000 AS y_value
                    FROM (
                     SELECT date_id,sgsn,'4g' tip,MAX(maximum_attached_users) sau,COUNT(hour_id) sampling FROM s1_mode_user_resource WHERE date_id >= CURDATE() - INTERVAL 7 DAY AND sgsn LIKE '%BDG%'
                     GROUP BY date_id,sgsn
                     UNION
                     SELECT date_id,sgsn,'3g' tip,MAX(iu_mode_maximum_attached_users) sau,COUNT(hour_id) sampling FROM iu_mode_radio_resource_117440578 WHERE date_id >= CURDATE() - INTERVAL 7 DAY AND sgsn LIKE '%BDG%'
                     GROUP BY date_id,sgsn
                     UNION
                     SELECT date_id,sgsn,'2g' tip,MAX(gb_mode_maximum_attached_users) sau,COUNT(hour_id) sampling FROM gb_mode_radio_resource_117440556 WHERE date_id >= CURDATE() - INTERVAL 7 DAY AND sgsn LIKE '%BDG%'
                     GROUP BY date_id,sgsn ) a
                    GROUP BY a.date_id,sgsn");
					$output['title'] = 'SGSN Utility';
					$output['yaxisVal'] = 'Percent';
                    $point = array();
                    $output_sgsn = array();
                    $series = array();
                    //while($result = mysqli_fetch_object($sql_sgsn)){
                    foreach ($sql_sgsn as $sql_sgsn => $result) {
                        date_default_timezone_set("UTC");
                        if($result->y_value <>0){
                        $the_date = ($result->date_id);
                        $mydate[] = $result->date_id;
                        $point[$result->sgsn][]=strtotime($the_date)*1000;
                        $point[$result->sgsn][]=(float)$result->y_value;
                        $series1[$result->sgsn][] = $point[$result->sgsn];
                        $series[$result->sgsn] = $series1[$result->sgsn];
                        $objectname[]=$result->sgsn;
                        $point = array();
                        }
                    }
                    foreach(array_unique($mydate) as $xas){
                        $xaxis[]=$xas;
                    };
                    foreach(array_unique($objectname) as $sgsn){
                        $output_sgsn['name'] = $sgsn;
                        $output_sgsn['data'] = $series[$sgsn];
                        $output_data[]=$output_sgsn;
                    }
                    $output['series'] = ($series);
                    $output['series'] = json_encode($output_data, JSON_NUMERIC_CHECK);
                return Core::setResponse("success",$output);
                break;
            case 'sgsn_util':
                    $sql_sgsn = \DB::connection("mysql145")->select("SELECT a.date_id,SUM(a.sampling) sample, IFNULL(sgsn,'Total Jabar') AS sgsn, SUM(a.sau) y_value 
                    FROM (
                     SELECT date_id,sgsn,'4g' tip,MAX(maximum_attached_users) sau,COUNT(hour_id) sampling FROM s1_mode_user_resource WHERE date_id >= CURDATE() - INTERVAL 7 DAY AND sgsn LIKE '%BDG%'
                     GROUP BY date_id,sgsn
                     UNION
                     SELECT date_id,sgsn,'3g' tip,MAX(iu_mode_maximum_attached_users) sau,COUNT(hour_id) sampling FROM iu_mode_radio_resource_117440578 WHERE date_id >= CURDATE() - INTERVAL 7 DAY AND sgsn LIKE '%BDG%'
                     GROUP BY date_id,sgsn
                     UNION
                     SELECT date_id,sgsn,'2g' tip,MAX(gb_mode_maximum_attached_users) sau,COUNT(hour_id) sampling FROM gb_mode_radio_resource_117440556 WHERE date_id >= CURDATE() - INTERVAL 7 DAY AND sgsn LIKE '%BDG%'
                     GROUP BY date_id,sgsn ) a
                    GROUP BY a.date_id,sgsn WITH rollup limit 40");
					$output['title'] = 'SAU Usage';
					$output['yaxisVal'] = 'Subscriber';
                    $point = array();
                    $output_sgsn = array();
                    $series = array();
                    //while($result = mysqli_fetch_object($sql_sgsn)){
                    foreach ($sql_sgsn as $sql_sgsn => $result) {
                        date_default_timezone_set("UTC");
                        if($result->y_value <>0){
                        $the_date = ($result->date_id);
                        $mydate[] = $result->date_id;
                        $point[$result->sgsn][]=strtotime($the_date)*1000;
                        $point[$result->sgsn][]=(float)$result->y_value;
                        $series1[$result->sgsn][] = $point[$result->sgsn];
                        $series[$result->sgsn] = $series1[$result->sgsn];
                        $objectname[]=$result->sgsn;
                        $point = array();
                        }
                    }
                    foreach(array_unique($mydate) as $xas){
                        $xaxis[]=$xas;
                    };
                    foreach(array_unique($objectname) as $sgsn){
                        $output_sgsn['name'] = $sgsn;
                        $output_sgsn['data'] = $series[$sgsn];
                        $output_data[]=$output_sgsn;
                    }
                    $output['series'] = ($series);
                    $output['series'] = json_encode($output_data, JSON_NUMERIC_CHECK);
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corecgr(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'data_mss':
                $output = \DB::connection("mysql145")->select("select distinct MSS_NAME from `N_CORE_REF_CGR_HOURLY` where (MSS_NAME LIKE '%BDG%' or MSS_NAME IN ('MSSCRB1'))");
                return Core::setResponse("success",$output);
                break;
            case 'data_fields':
                $output = \DB::connection("mysql145")->select("show columns from `N_CORE_REF_CGR_HOURLY` where field not in ('MSS_NAME', 'DATE_ID', 'TIME_ID', 'HOUR_ID', 'CGR_ID', 'CGR_NAME_ID')");
                return Core::setResponse("success",$output);
                break;
            case 'grafik_cgr':
                $output = \DB::connection("mysql145")->select("SELECT a.DATE_ID
                    FROM N_CORE_REF_CGR_HOURLY a
                    WHERE DATE_ID BETWEEN '" . $_POST['start_date'] . "' and '" . $_POST['end_date'] . "'
                    GROUP BY a.DATE_ID");
                return Core::setResponse("success",$output);
                break;
            case 'grafik_cgr1':
                $output = \DB::connection("mysql145")->select("SELECT a.HOUR_ID
                    FROM N_CORE_REF_CGR_HOURLY a
                    WHERE DATE_ID = '" . $result_date->DATE_ID . "'
                    AND MSS_NAME  = '" . $_POST['mss_name'] . "'
                    GROUP BY a.HOUR_ID");
                return Core::setResponse("success",$output);
                break;
            case 'grafik_cgr2':
                $output = \DB::connection("mysql145")->select("select MSS_NAME, CGR_NAME_ID, DATE_ID," . $_POST['counter_names'] . " , HOUR_ID from `N_CORE_REF_CGR_HOURLY` 
                    WHERE mss_name = '" . $_POST['mss_name'] . "' and CGR_NAME_ID  = '" . $value . "'
                    and DATE_ID BETWEEN '" . $_POST['start_date'] . "' and '" . $_POST['end_date'] . "'
                    GROUP BY DATE_ID,HOUR_ID");
                return Core::setResponse("success",$output);
                break;
            case 'ajax_get_cgr':
                $mss_name = $_GET['mss_name'];
                $sql_detail = \DB::connection("mysql145")->select("
                select distinct cgr_name_id from `N_CORE_REF_CGR_HOURLY` 
                where mss_name = '" . $mss_name . "'
                ");
                $count = mysqli_num_rows($sql_detail);
                $output = array();
                $output["sql_detail"] = $query;
                $output['content_count'] = $count;
                $output['content'] = '';
                $array = array();
                while ($result = mysqli_fetch_object($sql_detail)) {
                $array[] = $result;
                }
                $output['output_array'] = $array;
                $output['output_json'] = json_encode($array);
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corepue(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'ajax-pue-load':
                $dt = $request->all();
                if (isset($dt['export'])) {
                    $ttc        = $dt['ttc'];
                    $year       = $dt['year'];
                    $start_week = $dt['start_week'];
                    $until_week = $dt['until_week'];
                    //region PUE LOAD
                    $query_pue_load = \DB::connection("mysql170")->select("SELECT it_load, cooling_infra, ps_load, lighting
                            FROM pue_jabar_final
                            WHERE location_ttc = '$ttc'
                            AND `year` = '$year'
                            AND `week` BETWEEN $start_week AND $until_week");
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename=pue_load_' . strtolower($ttc) . '_year_' . $year . '_week_' . $start_week . '_to_' . $until_week . '.csv');
                    $output = fopen("php://output", "w");
                    fputcsv($output, array('IT Load', 'Cooling Infra', 'PS Load', 'Lighting'), ';');
                    //mengumpulkan data it load, cooling infra, ps load dan lighting ke array
                    while ($data = mysqli_fetch_assoc($query_pue_load)) {
                        fputcsv($output, $data, ';');
                    }
                    fclose($output);
                } //end untuk export ke csv
                else {
                    //untuk ajax chart
                    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
                        $ttc        = $dt['ttc'];
                        $year       = $dt['year'];
                        $start_week = $dt['start_week'];
                        $until_week = $dt['until_week'];
                        //region PUE LOAD
                        $query_pue_load = \DB::connection("mysql170")->select("SELECT it_load, cooling_infra, ps_load, lighting
                                FROM pue_jabar_final
                                WHERE location_ttc = '$ttc'
                                AND `year` = '$year'
                                AND `week` BETWEEN $start_week AND $until_week");
                        //mengumpulkan data it load, cooling infra, ps load dan lighting ke array
                        while ($data = mysqli_fetch_object($query_pue_load)) {
                            $it_load[]          = (float) round(str_replace(',', '.', $data->it_load), 2);
                            $cooling_infra[]    = (float) round(str_replace(',', '.', $data->cooling_infra), 2);
                            $ps_load[]          = (float) round(str_replace(',', '.', $data->ps_load), 2);
                            $lighting[]         = (float) round(str_replace(',', '.', $data->lighting), 2);
                        }
                        //menggabungkan array data untuk series chart
                        $pue_load = array(
                            array(
                                'name'  => 'IT LOAD',
                                'data'  => $it_load
                            ),
                            array(
                                'name'  => 'COOLING INFRA',
                                'data'  => $cooling_infra
                            ),
                            array(
                                'name'  => 'PS LOAD',
                                'data'  => $ps_load
                            ),
                            array(
                                'name'  => 'LIGHTING',
                                'data'  => $lighting
                            )
                        );
                        //endregion PUE LOAD
                        echo json_encode(
                            array(
                                'ttc'       => $ttc,
                                'result'    => $pue_load,
                                'point'     => (int) $start_week
                            )
                        );
                    } else {
                        echo json_encode(
                            array('msg' => 'Request method not allowed')
                        );
                    }
                    //end untuk ajax chart
                }
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pue-ttc':
                $dt = $request->all();
                if (isset($dt['export'])) {
                    $year       = $dt['year'];
                    $start_week = $dt['start_week'];
                    $until_week = $dt['until_week'];
                
                    //region PUE TTC
                    $query_pue_ttc = \DB::connection("mysql170")->select("SELECT
                            pue.`year`, pue.`week`,
                            dago.pue AS dago, 
                            soetta.pue AS soetta 
                            FROM pue_jabar_final pue
                            INNER JOIN
                            (
                                SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'DAGO'
                            ) AS dago ON dago.`week` = pue.`week`
                            INNER JOIN 
                            (
                                SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'SOETTA'
                            ) AS soetta ON soetta.`week` = pue.`week`
                            WHERE pue.`year` = '$year'
                            AND pue.`week` BETWEEN $start_week AND $until_week
                            GROUP BY pue.`week`");
                
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename=pue_ttc_year_' . $year . '_week_' . $start_week . '_to_' . $until_week . '.csv');
                    $output = fopen("php://output", "w");
                
                    fputcsv($output, array('Tahun', 'Minggu', 'Dago', 'Soetta'), ';');
                
                    //mengumpulkan data ttc
                    while ($data = mysqli_fetch_assoc($query_pue_ttc)) {
                        fputcsv($output, $data, ';');
                    }
                
                    fclose($output);
                } //end untuk export csv
                else {
                    //untuk ajax chart
                    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
                        $year       = $_POST['year'];
                        $start_week = $_POST['start_week'];
                        $until_week = $_POST['until_week'];
                
                        //region PUE TTC
                        $query_pue_ttc = \DB::connection("mysql170")->select("SELECT
                                pue.`year`, pue.`week`,
                                dago.pue AS dago, 
                                soetta.pue AS soetta 
                                FROM pue_jabar_final pue
                                INNER JOIN
                                (
                                    SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'DAGO'
                                ) AS dago ON dago.`week` = pue.`week`
                                INNER JOIN 
                                (
                                    SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'SOETTA'
                                ) AS soetta ON soetta.`week` = pue.`week`
                                WHERE pue.`year` = '$year'
                                AND pue.`week` BETWEEN $start_week AND $until_week
                                GROUP BY pue.`week`");
                
                        //mengumpulkan data ttc
                        while ($data = mysqli_fetch_object($query_pue_ttc)) {
                            $dago[]     = (float) round(str_replace(',', '.', $data->dago), 2);
                            $soetta[]   = (float) round(str_replace(',', '.', $data->soetta), 2);
                        }
                
                        //menggabungkan array data untuk series chart
                        $pue_ttc = array(
                            array(
                                'name'  => 'DAGO',
                                'data'  => $dago
                            ),
                            array(
                                'name'  => 'SOETTA',
                                'data'  => $soetta
                            )
                        );
                        //endregion PUE TTC
                
                        echo json_encode(
                            array(
                                'result'    => $pue_ttc,
                                'point'     => (int) $start_week
                            )
                        );
                    } else {
                        echo json_encode(
                            array('msg' => 'Request method not allowed')
                        );
                    }
                    //end untuk ajax chart
                }
                return Core::setResponse("success",$output);
                break;
            case 'pue-load-ttc':
                $output = \DB::connection("mysql170")->select("SELECT location_ttc FROM pue_jabar_final GROUP BY location_ttc");
                return Core::setResponse("success",$output);
                break;
            case 'pue-load-year':
                $output = \DB::connection("mysql170")->select("SELECT `year` FROM pue_jabar_final GROUP BY `year`");
                return Core::setResponse("success",$output);
                break;
            case 'pue-load-start-week':
                $output = \DB::connection("mysql170")->select("SELECT `week` FROM pue_jabar_final GROUP BY `week`");
                return Core::setResponse("success",$output);
                break;
            case 'pue-load-until-week':
                $output = \DB::connection("mysql170")->select("SELECT `week` FROM pue_jabar_final GROUP BY `week`
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function corescr(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'ajax_scr':
                $dt = $request->all();
                
                #### set condition
                if ($_REQUEST['nsa'] == 'all') {
                    $condition = "";
                } else {
                    $condition = " and nsa='" . $_REQUEST['nsa'] . "'";
                }
                if (empty($_REQUEST['tanggal'])) {
                    $tanggal = date('Y-m-d', strtotime("-1 days"));
                } else {
                    $tanggal = $_REQUEST['tanggal'];
                }

                $query = \DB::connection("mysql170")->select("select a.*,(a.call_attempt-a.call_success) as call_fail,round(a.call_success/a.call_attempt*100,2) as scr from sai_scr_ccr a 
                where mydate='" . $tanggal . "' $condition
                order by (a.call_attempt-a.call_success) desc limit 0,100");
                $i = 1;

                $dt = array();

                while ($data = mysqli_fetch_object($query)) {
                    $row = '';
                    //    $row[] = $i;
                    $row[] = $data->mydate;
                    $row[] = $data->lac . "-" . $data->cell_sac;
                    $row[] = $data->bsc_rnc;
                    //    $row[] = $data->siteid;
                    //    $row[] = $data->neid;
                    $row[] = $data->sectorname;
                    $row[] = $data->band;
                    $row[] = $data->nsa;
                    $row[] = $data->rtp;
                    $row[] = $data->grp4_1;
                    $row[] = $data->grp4_2;
                    $row[] = $data->grp4_3;
                    $row[] = $data->grp4_4;
                    $row[] = $data->grp8_0;
                    $row[] = $data->grp8_1;
                    $row[] = $data->grp8_2;
                    $row[] = $data->grp8_3;
                    $row[] = $data->grp8_4;
                    $row[] = $data->grp8_5;
                    $row[] = $data->grp8_6;
                    $row[] = $data->grp8_7;
                    $row[] = $data->call_fail;
                    $row[] = $data->scr;
                    $dt[] = $row;
                    $i++;
                }

                $output['data'] = $dt;
                echo JSON_ENCODE($output, JSON_NUMERIC_CHECK);
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pue-ttc':
                $dt = $request->all();
                if (!empty($_GET['nsa'])) {
                    if ($_GET['nsa'] == 'all') {
                        $whereand = '';
                    } else {
                        $nsa = $_GET['nsa'];
                        $whereand = "and nsa like %'" . $nsa . "'%";
                    }
                } else {
                    $whereand = "";
                }
                $tanggal = $_GET['tanggal'];
                
                //define array
                //we need two arrays - "male" and "female" so $arr and $arr1 respectively!
                $arr 	= array();
                $arr1 	= array();
                $arr2 	= array();
                $arr3 	= array();
                $q = \DB::connection("mysql170")->select("select mydate, sum(grp4_1) as grp4_1,sum(grp4_2) as grp4_2,sum(grp4_3) as grp4_3,sum(grp4_4) as grp4_4 from sai_scr_ccr 
                where mydate = '" . $tanggal . "' $whereand ");
                $j = 0;
                while ($row = mysqli_fetch_assoc($q)) {
                    $arr['name'] = "4 Group 1";
                    $arr['y']	= $row['grp4_1'];
                    $arr1['name'] = "4 Group 2";
                    $arr1['y']	= $row['grp4_2'];
                    $arr2['name'] = "4 Group 3";
                    $arr2['y']	= $row['grp4_3'];
                    $arr3['name'] = "4 Group 4";
                    $arr3['y']	= $row['grp4_4'];
                }
                array_push($result, $arr);
                array_push($result, $arr1);
                array_push($result, $arr2);
                array_push($result, $arr3);
                
                $query_pue_ttc = \DB::connection("mysql170")->select("SELECT
                        pue.`year`, pue.`week`,
                        dago.pue AS dago, 
                        soetta.pue AS soetta 
                        FROM pue_jabar_final pue
                        INNER JOIN
                        (
                            SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'DAGO'
                        ) AS dago ON dago.`week` = pue.`week`
                        INNER JOIN 
                        (
                            SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'SOETTA'
                        ) AS soetta ON soetta.`week` = pue.`week`
                        WHERE pue.`year` = '$year'
                        AND pue.`week` BETWEEN $start_week AND $until_week
                        GROUP BY pue.`week`");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pie-scr':
                if (!empty($_GET['nsa'])) {
                    if ($_GET['nsa'] == 'all') {
                        $whereand = '';
                    } else {
                        $nsa = $_GET['nsa'];
                        $whereand = "and nsa like %'" . $nsa . "'%";
                    }
                } else {
                    $whereand = "";
                }
                $tanggal = $_GET['tanggal'];
                //define array
                //we need two arrays - "male" and "female" so $arr and $arr1 respectively!
                $arr 	= array();
                $arr1 	= array();
                $arr2 	= array();
                $arr3 	= array();
                $result = array();
                //get the result from the table "highcharts_data"
                $q = \DB::connection("mysql170")->select("select mydate, sum(grp4_1) as grp4_1,sum(grp4_2) as grp4_2,sum(grp4_3) as grp4_3,sum(grp4_4) as grp4_4 from sai_scr_ccr 
                where mydate = '" . $tanggal . "' $whereand ");
                //echo $sql;
                $j = 0;
                while ($row = mysqli_fetch_assoc($q)) {
                    //highcharts needs name, but only once, so give a IF condition
                    //and the data for male and female is here ['data']
                    $arr['name'] = "4 Group 1";
                    $arr['y']	= $row['grp4_1'];
                    $arr1['name'] = "4 Group 2";
                    $arr1['y']	= $row['grp4_2'];
                    $arr2['name'] = "4 Group 3";
                    $arr2['y']	= $row['grp4_3'];
                    $arr3['name'] = "4 Group 4";
                    $arr3['y']	= $row['grp4_4'];

                }
                array_push($result, $arr);
                array_push($result, $arr1);
                array_push($result, $arr2);
                array_push($result, $arr3);
                //now create the json result using "json_encode"
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pie-scr8':
                if (!empty($_GET['nsa'])) {
                    if ($_GET['nsa'] == 'all') {
                        $whereand = '';
                    } else {
                        $nsa = $_GET['nsa'];
                        $whereand = "and nsa like %'" . $nsa . "'%";
                    }
                } else {
                    $whereand = "";
                }
                $tanggal = $_GET['tanggal'];
                //define array
                //we need two arrays - "male" and "female" so $arr and $arr1 respectively!
                $arr 	= array();
                $arr1 	= array();
                $arr2 	= array();
                $arr3 	= array();
                $result = array();
                
                //get the result from the table "highcharts_data"
                $q = \DB::connection("mysql170")->select("select mydate, sum(grp8_1) as grp8_1,sum(grp8_2) as grp8_2,sum(grp8_3) as grp8_3,sum(grp8_4) as grp8_4 from sai_scr_ccr 
                where mydate = '" . $tanggal . "' $whereand ");
                //echo $sql;
                $j = 0;
                while ($row = mysqli_fetch_assoc($q)) {
                
                    //highcharts needs name, but only once, so give a IF condition
                    //and the data for male and female is here ['data']
                    $arr['name'] = "8 Group 1";
                    $arr['y']	= $row['grp8_1'];
                    $arr1['name'] = "8 Group 2";
                    $arr1['y']	= $row['grp8_2'];
                    $arr2['name'] = "8 Group 3";
                    $arr2['y']	= $row['grp8_3'];
                    $arr3['name'] = "8 Group 4";
                    $arr3['y']	= $row['grp8_4'];
                }
                
                //after get the data for male and female, push both of them to an another array called result
                //array_push($result,$arr0);
                //array_push($result,$arr00);
                array_push($result, $arr);
                array_push($result, $arr1);
                array_push($result, $arr2);
                array_push($result, $arr3);
                $output = \DB::connection("mysql170")->select("SELECT `year` FROM pue_jabar_final GROUP BY `year`");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function coremonthlyreport(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'ajax_scr':
                $dt = $request->all();
                
                #### set condition
                if ($_REQUEST['nsa'] == 'all') {
                    $condition = "";
                } else {
                    $condition = " and nsa='" . $_REQUEST['nsa'] . "'";
                }
                if (empty($_REQUEST['tanggal'])) {
                    $tanggal = date('Y-m-d', strtotime("-1 days"));
                } else {
                    $tanggal = $_REQUEST['tanggal'];
                }

                $query = \DB::connection("mysql170")->select("select a.*,(a.call_attempt-a.call_success) as call_fail,round(a.call_success/a.call_attempt*100,2) as scr from sai_scr_ccr a 
                where mydate='" . $tanggal . "' $condition
                order by (a.call_attempt-a.call_success) desc limit 0,100");
                $i = 1;

                $dt = array();

                while ($data = mysqli_fetch_object($query)) {
                    $row = '';
                    //    $row[] = $i;
                    $row[] = $data->mydate;
                    $row[] = $data->lac . "-" . $data->cell_sac;
                    $row[] = $data->bsc_rnc;
                    //    $row[] = $data->siteid;
                    //    $row[] = $data->neid;
                    $row[] = $data->sectorname;
                    $row[] = $data->band;
                    $row[] = $data->nsa;
                    $row[] = $data->rtp;
                    $row[] = $data->grp4_1;
                    $row[] = $data->grp4_2;
                    $row[] = $data->grp4_3;
                    $row[] = $data->grp4_4;
                    $row[] = $data->grp8_0;
                    $row[] = $data->grp8_1;
                    $row[] = $data->grp8_2;
                    $row[] = $data->grp8_3;
                    $row[] = $data->grp8_4;
                    $row[] = $data->grp8_5;
                    $row[] = $data->grp8_6;
                    $row[] = $data->grp8_7;
                    $row[] = $data->call_fail;
                    $row[] = $data->scr;
                    $dt[] = $row;
                    $i++;
                }

                $output['data'] = $dt;
                echo JSON_ENCODE($output, JSON_NUMERIC_CHECK);
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pue-ttc':
                $dt = $request->all();
                if (!empty($_GET['nsa'])) {
                    if ($_GET['nsa'] == 'all') {
                        $whereand = '';
                    } else {
                        $nsa = $_GET['nsa'];
                        $whereand = "and nsa like %'" . $nsa . "'%";
                    }
                } else {
                    $whereand = "";
                }
                $tanggal = $_GET['tanggal'];
                
                //define array
                //we need two arrays - "male" and "female" so $arr and $arr1 respectively!
                $arr 	= array();
                $arr1 	= array();
                $arr2 	= array();
                $arr3 	= array();
                $q = \DB::connection("mysql170")->select("select mydate, sum(grp4_1) as grp4_1,sum(grp4_2) as grp4_2,sum(grp4_3) as grp4_3,sum(grp4_4) as grp4_4 from sai_scr_ccr 
                where mydate = '" . $tanggal . "' $whereand ");
                $j = 0;
                while ($row = mysqli_fetch_assoc($q)) {
                    $arr['name'] = "4 Group 1";
                    $arr['y']	= $row['grp4_1'];
                    $arr1['name'] = "4 Group 2";
                    $arr1['y']	= $row['grp4_2'];
                    $arr2['name'] = "4 Group 3";
                    $arr2['y']	= $row['grp4_3'];
                    $arr3['name'] = "4 Group 4";
                    $arr3['y']	= $row['grp4_4'];
                }
                array_push($result, $arr);
                array_push($result, $arr1);
                array_push($result, $arr2);
                array_push($result, $arr3);
                
                $query_pue_ttc = \DB::connection("mysql170")->select("SELECT
                        pue.`year`, pue.`week`,
                        dago.pue AS dago, 
                        soetta.pue AS soetta 
                        FROM pue_jabar_final pue
                        INNER JOIN
                        (
                            SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'DAGO'
                        ) AS dago ON dago.`week` = pue.`week`
                        INNER JOIN 
                        (
                            SELECT `week`, pue FROM pue_jabar_final WHERE location_ttc = 'SOETTA'
                        ) AS soetta ON soetta.`week` = pue.`week`
                        WHERE pue.`year` = '$year'
                        AND pue.`week` BETWEEN $start_week AND $until_week
                        GROUP BY pue.`week`");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pie-scr':
                if (!empty($_GET['nsa'])) {
                    if ($_GET['nsa'] == 'all') {
                        $whereand = '';
                    } else {
                        $nsa = $_GET['nsa'];
                        $whereand = "and nsa like %'" . $nsa . "'%";
                    }
                } else {
                    $whereand = "";
                }
                $tanggal = $_GET['tanggal'];
                //define array
                //we need two arrays - "male" and "female" so $arr and $arr1 respectively!
                $arr 	= array();
                $arr1 	= array();
                $arr2 	= array();
                $arr3 	= array();
                $result = array();
                //get the result from the table "highcharts_data"
                $q = \DB::connection("mysql170")->select("select mydate, sum(grp4_1) as grp4_1,sum(grp4_2) as grp4_2,sum(grp4_3) as grp4_3,sum(grp4_4) as grp4_4 from sai_scr_ccr 
                where mydate = '" . $tanggal . "' $whereand ");
                //echo $sql;
                $j = 0;
                while ($row = mysqli_fetch_assoc($q)) {
                    //highcharts needs name, but only once, so give a IF condition
                    //and the data for male and female is here ['data']
                    $arr['name'] = "4 Group 1";
                    $arr['y']	= $row['grp4_1'];
                    $arr1['name'] = "4 Group 2";
                    $arr1['y']	= $row['grp4_2'];
                    $arr2['name'] = "4 Group 3";
                    $arr2['y']	= $row['grp4_3'];
                    $arr3['name'] = "4 Group 4";
                    $arr3['y']	= $row['grp4_4'];

                }
                array_push($result, $arr);
                array_push($result, $arr1);
                array_push($result, $arr2);
                array_push($result, $arr3);
                //now create the json result using "json_encode"
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pie-scr8':
                if (!empty($_GET['nsa'])) {
                    if ($_GET['nsa'] == 'all') {
                        $whereand = '';
                    } else {
                        $nsa = $_GET['nsa'];
                        $whereand = "and nsa like %'" . $nsa . "'%";
                    }
                } else {
                    $whereand = "";
                }
                $tanggal = $_GET['tanggal'];
                //define array
                //we need two arrays - "male" and "female" so $arr and $arr1 respectively!
                $arr 	= array();
                $arr1 	= array();
                $arr2 	= array();
                $arr3 	= array();
                $result = array();
                
                //get the result from the table "highcharts_data"
                $q = \DB::connection("mysql170")->select("select mydate, sum(grp8_1) as grp8_1,sum(grp8_2) as grp8_2,sum(grp8_3) as grp8_3,sum(grp8_4) as grp8_4 from sai_scr_ccr 
                where mydate = '" . $tanggal . "' $whereand ");
                //echo $sql;
                $j = 0;
                while ($row = mysqli_fetch_assoc($q)) {
                
                    //highcharts needs name, but only once, so give a IF condition
                    //and the data for male and female is here ['data']
                    $arr['name'] = "8 Group 1";
                    $arr['y']	= $row['grp8_1'];
                    $arr1['name'] = "8 Group 2";
                    $arr1['y']	= $row['grp8_2'];
                    $arr2['name'] = "8 Group 3";
                    $arr2['y']	= $row['grp8_3'];
                    $arr3['name'] = "8 Group 4";
                    $arr3['y']	= $row['grp8_4'];
                }
                
                //after get the data for male and female, push both of them to an another array called result
                //array_push($result,$arr0);
                //array_push($result,$arr00);
                array_push($result, $arr);
                array_push($result, $arr1);
                array_push($result, $arr2);
                array_push($result, $arr3);
                $output = \DB::connection("mysql170")->select("SELECT `year` FROM pue_jabar_final GROUP BY `year`");
                return Core::setResponse("success",$output);
                break;
            case 'pue-load-start-week':
                $output = \DB::connection("mysql170")->select("SELECT `week` FROM pue_jabar_final GROUP BY `week`");
                return Core::setResponse("success",$output);
                break;
            case 'pue-load-until-week':
                $output = \DB::connection("mysql170")->select("SELECT `week` FROM pue_jabar_final GROUP BY `week`
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $output = \DB::connection("mysql145")->select("");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function coreperform3g(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'getlistdrop':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'getlistdropweekly':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-eas':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-data-graph-alarm':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql170")->select("SELECT nsa,rtp FROM daily_monitoring_packetloss4g
                    WHERE regional LIKE '%JAWA BARAT%' AND nsa IS NOT null
                    GROUP BY nsa,rtp
                    ORDER BY nsa,rtp
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql225")->select("SELECT minggu,nsa,rtpo,COUNT(IF(degraded_times>=3,1,NULL)) AS Degraded,COUNT(IF(degraded_times < 3,1,NULL)) AS Clear,round(COUNT(IF(degraded_times<3,1,NULL))/(COUNT(IF(degraded_times>=3,1,NULL))+COUNT(IF(degraded_times < 3,1,NULL))) *100,2) as percen
                    FROM (
                    SELECT minggu,nsa,rtpo,rnc,ani,site_id,site_name,COUNT(IF(remark_packetloss='Degraded',1,NULL)) AS degraded_times
                    FROM
                    (
                    SELECT concat(YEAR(tanggal),WEEK(tanggal)) AS minggu,a.* 
                    FROM daily_packet_loss a 
                    WHERE WEEK(tanggal) >= WEEK(CURDATE())-8
                    ) aa
                    GROUP BY minggu,nsa,rtpo,rnc,ani,site_id,site_name
                    ) aa
                    GROUP BY minggu,nsa,rtpo");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql225")->select("select @EndWeek := DATE(date(now()) + INTERVAL (-1 - WEEKDAY(date(now()))) DAY) as tanggal
                    union
                    select @EndWeek := DATE(date(now()) + INTERVAL (0 - WEEKDAY(date(now()))) DAY)
                    union
                    select @EndWeek := DATE(date(now()) + INTERVAL (1 - WEEKDAY(date(now()))) DAY)
                    union
                    select @EndWeek := DATE(date(now()) + INTERVAL (2 - WEEKDAY(date(now()))) DAY)
                    union
                    select @EndWeek := DATE(date(now()) + INTERVAL (3 - WEEKDAY(date(now()))) DAY)
                    union
                    select @EndWeek := DATE(date(now()) + INTERVAL (4 - WEEKDAY(date(now()))) DAY)
                    union
                    select @EndWeek := DATE(date(now()) + INTERVAL (5 - WEEKDAY(date(now()))) DAY)");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql225")->select("SELECT minggu,tanggal,nsa,rtpo,COUNT(IF(remark_packetloss='Degraded',1,NULL)) AS Degraded,COUNT(IF(remark_packetloss='Clear',1,NULL)) AS Clear
                    FROM (
                    SELECT WEEK(tanggal) AS minggu,a.* 
                    FROM daily_packet_loss a 
                    WHERE WEEK(tanggal)=WEEK(CURDATE()-1)
                    ) aa
                    GROUP BY minggu, tanggal,nsa,rtpo
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-packetloss-graph':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-hourly-monitoring-packetoss':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-detail-hourly-monitoring-packetloss':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-more-detail-hourly-monitoring-packetloss':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function coreperform4g(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql170")->select("SELECT nsa,rtp FROM daily_monitoring_packetloss4g
                    WHERE regional LIKE '%JAWA BARAT%' AND nsa IS NOT null
                    GROUP BY nsa,rtp
                    ORDER BY nsa,rtp
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql170")->select("select tahun as years from weekly_packetloss_rtp4g order by tahun DESC LIMIT 1
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql170")->select("SELECT MAX(minggu) AS week_num FROM weekly_packetloss_rtp4g WHERE tahun = (select tahun from weekly_packetloss_rtp4g order by tahun DESC LIMIT 1)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql170")->select("SELECT distinct(tanggal) AS tanggal, minggu AS week from daily_monitoring_packetloss4g where substring(tanggal,1,4) = $year_now AND minggu = '$week_now' order by tanggal asc
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql170")->select("select 
                    DATE_SUB(DATE_ADD(MAKEDATE($year_now, 1), INTERVAL $week_last WEEK),interval 3 day) as tanggal,
                    week(DATE_SUB(DATE_ADD(MAKEDATE($year_now, 1), INTERVAL $week_last WEEK),interval 3 day) + interval $offsetday_year[$year_now] day) as minggu,'awal_cari' as remark
                    union
                    select 
                    DATE_ADD(DATE_SUB(DATE_ADD(MAKEDATE($year_now, 1), INTERVAL $week_off WEEK),interval 3 day),interval 6 DAY) as tanggal,
                    week(DATE_ADD(DATE_SUB(DATE_ADD(MAKEDATE($year_now, 1), INTERVAL $week_off WEEK),interval 3 day),interval 6 DAY) + interval $offsetday_year[$year_now] day) as week,'akhir_cari' as remark
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql170")->select("select tanggal,week(tanggal + interval 9 day) as minggu,nsa,rtp as rtpo, 
                    COUNT(if(Cat2WayPL='Consecutive',1,null)) as count_drop, 
                    COUNT(if(Cat2WayPL='Spike',1,null)) as count_spike, 
                    COUNT(if(Cat2WayPL='Consecutive' or Cat2WayPL='Spike',1,null)) as Degraded, 
                    COUNT(if(Cat2WayPL='Clear',1,null)) as Clear, 
                    count(siteid) as jml_site 
                    FROM daily_monitoring_packetloss4g
                    where tanggal in ($carirangetanggal) AND nsa IS NOT NULL
                    group by tanggal,nsa,rtp
                    order by tanggal asc
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $output = \DB::connection("mysql170")->select("select tahun,minggu,nsa,rtp as rtpo,num_drop as degraded,num_spike as spike,num_clear as clear from weekly_packetloss_rtp4g where 
                     tahun = '" . $year_now . "' and minggu >= $week_last and minggu <= $week_now
                ");
                return Core::setResponse("success",$output);
                break;
            case 'getlistdropdaily-pl4g':
                $output = \DB::connection("mysql170")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'getlistdropweekly-pl4g':
                $output = \DB::connection("mysql170")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-eas':
                $output = \DB::connection("mysql170")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-data-graph-alarm':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'export_packetloss':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-packetloss-graph-daily-rtp-bar':
                $output = \DB::connection("mysql170")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-packetloss-graph-new4g':
                $output = \DB::connection("mysql170")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-hourly-monitoring-packetoss4g':
                $output = \DB::connection("mysql170")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-weekly-monitoring-packetoss4g':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-detail-hourly-monitoring-packetloss4g':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-more-detail-hourly-monitoring-packetloss4g':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-packetloss-per-hub-telkom4g':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function transportdapot(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql222b")->select("SELECT OWNER as own,COUNT(site_id) as jumlah FROM tbl_dapot_transport_persite where OWNER<>'-' GROUP BY OWNER ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222b")->select("SELECT transport as transport,COUNT(site_id) as jumlah FROM tbl_dapot_transport_persite Where transport <>'-' GROUP BY transport
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql222b")->select("SELECT connection as connection,COUNT(site_id) as jumlah FROM tbl_dapot_transport_persite where connection <> '-' GROUP BY connection");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql222b")->select("SELECT bw_2g,COUNT(bw_2g) AS num FROM tbl_dapot_transport_persite
                    WHERE bw_2g !='-'
                    GROUP BY bw_2g
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql222b")->select("SELECT bw_3g,COUNT(bw_3g) AS num FROM tbl_dapot_transport_persite
                    WHERE bw_3g !='-'
                    GROUP BY bw_3g
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql222b")->select("SELECT bw_4g,COUNT(bw_4g) AS num FROM tbl_dapot_transport_persite
                    WHERE bw_4g !='-'
                    GROUP BY bw_4g
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query7':
                $output = \DB::connection("mysql222b")->select("SELECT SUM(bw_2g) AS bw2g,SUM(bw_3g) AS bw3g,SUM(bw_4g) AS bw4g FROM tbl_dapot_transport_persite
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function transportdapot2(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query_transport':
                $output = \DB::connection("mysql222b")->select("SELECT TRANSPORT_Actual FROM dapot_transport_new
                    WHERE (TRANSPORT_Actual = 'IP-FO-TELKOM'
                    OR TRANSPORT_Actual = 'IP-FO-TELKOMSEL'
                    OR TRANSPORT_Actual = 'IP-RADIO-TELKOM'
                    OR TRANSPORT_Actual = 'IP-RADIO-TELKOMSEL')
                    GROUP BY TRANSPORT_Actual
                    ORDER BY TRANSPORT_Actual DESC");
                return Core::setResponse("success",$output);
                break;
            case 'query_transport_total':
                $output = \DB::connection("mysql222b")->select("SELECT COUNT(TRANSPORT_Actual) AS total
                    FROM dapot_transport_new
                    WHERE TRANSPORT_Actual = '$transport->TRANSPORT_Actual'
                    GROUP BY NSA
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query_transport_variable':
                $output = \DB::connection("mysql222b")->select("SELECT TRANSPORT_Actual, COUNT(TRANSPORT_Actual) AS total
                    FROM dapot_transport_new
                    WHERE (TRANSPORT_Actual = 'IP-FO-TELKOM'
                    OR TRANSPORT_Actual = 'IP-FO-TELKOMSEL'
                    OR TRANSPORT_Actual = 'IP-RADIO-TELKOM'
                    OR TRANSPORT_Actual = 'IP-RADIO-TELKOMSEL'
                    OR TRANSPORT_Actual = 'TRANSPORT TP')
                    GROUP BY TRANSPORT_Actual
                    ORDER BY TRANSPORT_Actual ASC");
                return Core::setResponse("success",$output);
                break;
            case 'query_long_lat':
                $output = \DB::connection("mysql222b")->select("SELECT longitude AS lng, lat, site_name AS site, REPLACE(TRANSPORT_Actual, '-', '_') AS legend
                    FROM dapot_transport_new
                    WHERE (TRANSPORT_Actual = 'IP-FO-TELKOM'
                    OR TRANSPORT_Actual = 'IP-FO-TELKOMSEL'
                    OR TRANSPORT_Actual = 'IP-RADIO-TELKOM'
                    OR TRANSPORT_Actual = 'IP-RADIO-TELKOMSEL')
                ");
                return Core::setResponse("success",$output);
                break;
            case 'data_not_4g':
                $output = \DB::connection("mysql222b")->select("SELECT * FROM t_transport_access WHERE technology != 'Ready 4G' ORDER BY `service` ASC
                ");
                return Core::setResponse("success",$output);
                break;
            case 'data_total_not_4g':
                $output = \DB::connection("mysql222b")->select("SELECT technology, `service`, SUM(fo_telkom) AS fo_telkom, SUM(fo_tsel) AS fo_tsel, SUM(radio_telkom) AS radio_telkom, SUM(radio_tsel) AS radio_tsel, SUM(transport_tp) AS transport_tp, (SUM(fo_telkom) + SUM(fo_tsel) + SUM(radio_telkom) + SUM(radio_tsel) + SUM(transport_tp)) AS grand_total FROM t_transport_access WHERE technology != 'Ready 4G'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_count_not_4g':
                $output = \DB::connection("mysql222b")->select("SELECT COUNT(*) AS total FROM t_transport_access WHERE technology != 'Ready 4G'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'data_4g':
                $output = \DB::connection("mysql145")->select("SELECT * FROM t_transport_access WHERE technology = 'Ready 4G' ORDER BY `service` ASC
                ");
                return Core::setResponse("success",$output);
                break;
            case 'data_total_4g':
                $output = \DB::connection("mysql145")->select("SELECT technology, `service`, SUM(fo_telkom) AS fo_telkom, SUM(fo_tsel) AS fo_tsel, SUM(radio_telkom) AS radio_telkom, SUM(radio_tsel) AS radio_tsel, SUM(transport_tp) AS transport_tp, (SUM(fo_telkom) + SUM(fo_tsel) + SUM(radio_telkom) + SUM(radio_tsel) + SUM(transport_tp)) AS grand_total FROM t_transport_access WHERE technology = 'Ready 4G'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_count_4g':
                $output = \DB::connection("mysql145")->select("SELECT COUNT(*) AS total FROM t_transport_access WHERE technology = 'Ready 4G'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'data_total_all':
                $output = \DB::connection("mysql145")->select("SELECT technology, `service`, SUM(fo_telkom) AS fo_telkom, SUM(fo_tsel) AS fo_tsel, SUM(radio_telkom) AS radio_telkom, SUM(radio_tsel) AS radio_tsel, SUM(transport_tp) AS transport_tp, (SUM(fo_telkom) + SUM(fo_tsel) + SUM(radio_telkom) + SUM(radio_tsel) + SUM(transport_tp)) AS grand_total FROM t_transport_access
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_ready_4g':
                $output = \DB::connection("mysql145")->select("SELECT TECH, TYPE_TRANSPORT, COUNT(*) AS total, 
                    ROUND((COUNT(*) / transport.total) * 100) AS percent 
                    FROM dapot_transport_new, 
                    (
                        SELECT COUNT(*) AS total FROM dapot_transport_new
                        WHERE TECH = 'Ready 4G'AND STATUS = 'ON AIR'
                        AND (TYPE_TRANSPORT = 'Fiber' OR TYPE_TRANSPORT = 'Radio')
                    ) AS transport
                    WHERE TECH = 'Ready 4G'AND STATUS = 'ON AIR'
                    AND (TYPE_TRANSPORT = 'Fiber' OR TYPE_TRANSPORT = 'Radio')
                    GROUP BY TYPE_TRANSPORT
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query_not_ready_4g':
                $output = \DB::connection("mysql145")->select("SELECT TECH, TYPE_TRANSPORT, COUNT(*) AS total, 
                    ROUND((COUNT(*) / transport.total) * 100) AS percent 
                    FROM dapot_transport_new, 
                    (
                        SELECT COUNT(*) AS total FROM dapot_transport_new
                        WHERE TECH = 'Not Ready 4G'AND STATUS = 'ON AIR'
                        AND (TYPE_TRANSPORT = 'Fiber' OR TYPE_TRANSPORT = 'Radio')
                    ) AS transport
                    WHERE TECH = 'Not Ready 4G'AND STATUS = 'ON AIR'
                    AND (TYPE_TRANSPORT = 'Fiber' OR TYPE_TRANSPORT = 'Radio')
                    GROUP BY TYPE_TRANSPORT
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function transportachievementnation(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql225a")->select("select * from t_trend_kpi_new where region NOT IN ('NATIONAL WIDE','11-PUMA')  order by year desc,week desc limit 10
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pl-nation':
                date_default_timezone_set("Asia/Jakarta");
                $doquery = \DB::connection("mysql225a")->select("select * from t_trend_kpi_new where region NOT IN ('NATIONAL WIDE','11-PUMA') and year ='" . date('Y') . "' ");
                if ($type == '3G') {
                    foreach ($doquery as $doquery => $data) {
                    //while ($data = mysqli_fetch_object($doquery)) {
                        $ada = array();
                        $ada[] = $data->week;
                        $datapercent = $data->packetloss_3G_percent * 100;
                        $ada[] = $datapercent;
                        $categori[] = $data->week;
                        $output[$data->region][] = $ada;
                        $region[] = $data->region;
                    }
                } else {
                    foreach ($doquery as $doquery => $data) {
                    //while ($data = mysqli_fetch_object($doquery)) {
                        $ada = array();
                        $ada[] = $data->week;
                        $datapercent = $data->packetloss_4G_percent * 100;
                        $ada[] = $datapercent;
                        $categori[] = $data->week;
                        $output[$data->region][] = $ada;
                        $region[] = $data->region;
                    }
                }
                $category = array_unique($categori);
                foreach ($category as $key => $catnya) {
                    $cat[] = $catnya;
                }
                sort($cat);
                $regional = array_unique($region);
                //print_r($objectname);
                foreach ($regional as $key => $regionnya) {
                    //echo $mss."<br>";
                    $output2['name'] = $regionnya;
                    $output2['data'] = $output[$regionnya];
                    $outputfin[] = $output2;
                }
                $output3['category'] = $cat;
                $output3['series'] = $outputfin;
                return Core::setResponse("success",$output3);
                break;
        }
    }

    public function transportachievementregional(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql225a")->select("SELECT year
                    FROM t_pl_3G 
                    GROUP BY year DESC
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql225a")->select("SELECT t1.week, t1.nsa AS region, t2.rtpo,
                            (t2.total-t3.total) AS 'pl_site',t2.total AS total, (t3.total) * 100 / t2.total AS percentage
                    FROM t_pl_3G AS t1
                    JOIN (
                    SELECT week, nsa, rtpo, count(*) AS total 
                    FROM t_pl_3G
                    where YEAR = '2020' AND week = (SELECT MAX(week) FROM t_pl_3G WHERE YEAR = '2020')
                    GROUP BY week, nsa, rtpo
                    ) AS t2
                    JOIN (
                    SELECT WEEK, nsa, rtpo, COUNT(*) AS total
                        FROM t_pl_3G
                        WHERE YEAR = '2020' AND pl_status NOT like 'CONSECUTIVE' AND week = (SELECT MAX(week) FROM t_pl_3G WHERE YEAR = '2020')
                        GROUP BY WEEK, nsa, rtpo
                    ) AS t3
                    ON t1.week = t2.week and t1.nsa = t2.nsa AND t1.week = t3.week AND t1.nsa = t3.nsa AND t1.rtpo = t2.rtpo AND t1.rtpo = t3.rtpo
                    WHERE t1.nsa NOT LIKE '#N/A' 
                        and YEAR ='2020' 
                        and t1.week = (SELECT MAX(week) FROM t_pl_3G WHERE YEAR = '2020')
                    GROUP BY t1.week, t1.nsa, t1.rtpo
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql225a")->select("SELECT year
                    FROM t_pl_3G 
                    GROUP BY year DESC limit 10
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql225a")->select("SELECT week
                    FROM t_pl_3G 
                    WHERE YEAR ='" . date("Y") . "'
                    GROUP BY week DESC
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query5':
                $output = \DB::connection("mysql225a")->select("SELECT year
                    FROM t_pl_4G 
                    GROUP BY year DESC limit 10
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query6':
                $output = \DB::connection("mysql225a")->select("SELECT week
                    FROM t_pl_4G 
                    WHERE YEAR ='" . date("Y") . "'
                    GROUP BY week DESC
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-pl-region-new':
                $dt = $request->all();
                $type = $dt['type'];
                $year = $dt['year'];
                date_default_timezone_set("Asia/Jakarta");
                if(!empty($year)){
                    $year = $year;
                }
                else{
                    $year = date('Y');
                }
                switch($type){
                    case '3G_latency':	
                        //$query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_3G where pl_status = 'CONSECUTIVE' and year ='".date('Y')."' group by week";
                        $doquery = \DB::connection("mysql225a")->select("
                            SELECT week,'jawa  barat' as region,count(if(lat_status='NOT CLEAR',1,NULL)) as jumlah,
                            (COUNT(siteid) - count(if(lat_status='NOT CLEAR',1,NULL)))/COUNT(siteid)*100 AS total
                            from t_pl_3G
                            where YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                            group by WEEK,reg_name
                        ");
                        break;
                    case '4G_latency':
                        //$query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and year ='".date('Y')."' group by week";
                        $doquery = \DB::connection("mysql225a")->select("
                            SELECT week,'jawa  barat' as region,count(if(lat_status='NOT-CLEAR',1,NULL)) as jumlah,
                            (COUNT(siteid) - count(if(lat_status='NOT-CLEAR',1,NULL)))/COUNT(siteid)*100 AS total
                            from t_pl_4G
                            where YEAR ='".$year."' AND region LIKE '%JAWA BARAT'
                            group by WEEK,region
                        ");
                        break;
                    case '3G':	
                        //$query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_3G where pl_status = 'CONSECUTIVE' and year ='".date('Y')."' group by week";
                        $doquery = \DB::connection("mysql225a")->select("
                            SELECT t1.week,'jawa  barat' as region,count(*) as jumlah ,100 - ( count(*) * 100 /t2.jumlah) AS total
                            from t_pl_3G AS t1
                            JOIN (
                                SELECT week,'jawa  barat' as region,count(*) as jumlah 
                                from t_pl_3G 
                                where YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                                GROUP BY WEEK 
                            ) AS t2
                            ON t1.week = t2.week
                            where pl_status = 'CONSECUTIVE' and YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                            group by WEEK,reg_name
                        ");
                        break;
                    case '4G':
                        //$query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and year ='".date('Y')."' group by week";
                        $doquery = \DB::connection("mysql225a")->select("
                            SELECT t1.week,'jawa  barat' as region,count(*) as jumlah ,100 - ( count(*) * 100 /t2.jumlah) AS total
                            from t_pl_4G AS t1
                            JOIN (
                                SELECT week,'jawa  barat' as region,count(*) as jumlah 
                                from t_pl_4G 
                                where YEAR ='".$year."'
                                GROUP BY WEEK 
                            ) AS t2
                            ON t1.week = t2.week
                            where pl_status = 'CONSECUTIVE' and YEAR ='".$year."'
                            group by WEEK
                        ");
                        break;
                    default:
                        echo "data not found";
                        $doquery = "";
                }
                    foreach ($doquery as $doquery => $data) {
                    //while($data = mysqli_fetch_object($doquery)){
                        $ada = array();
                        $ada2 = array();
                        $ada[] = $data->week;
                        $ada2[] = $data->week;
                        $datapercent = $data->jumlah;
                        $ada[] = $datapercent;
                        $totalpercent = $data->total;
                        $ada2[] = $totalpercent;
                        $categori[] = $data->week;
                        $output[$data->region][] = $ada;
                        $output_[$data->region][] = $ada2;
                        $region[] = $data->region;
                    }
                $category = array_unique($categori);
                foreach($category as $key => $catnya){
                    $cat[]=$catnya;
                }
                sort($cat);
                $regional = array_unique($region);
                //print_r($objectname);
            // $output2['type'] = 'column';
                foreach($regional as $key => $regionnya){
                    //echo $mss."<br>";
                // $output2['name'] = $regionnya;
                    if($type=='3G_latency' or $type=='4G_latency'){
                    $output2['name'] = "Latency Jawa Barat Value";
                    }else{
                        $output2['name'] = "PL Jawa Barat Value";
                    }
                    $output2['type'] = "column";
                    $output2['color'] = "#2F5EAA";
                    $output2['yAxis'] = "0";
                    $output2['data'] = $output[$regionnya];
                    $outputfin[] = $output2;
                }
                $output3['series'] = $outputfin;
                foreach($regional as $key => $regionnya){
                    //echo $mss."<br>";
                // $output2['name'] = $regionnya;
                    if($type=='3G_latency' or $type=='4G_latency'){
                    $output2['name'] = "Latency Jawa Barat Percentage";
                    }else{
                        $output2['name'] = "PL Jawa Barat Percentage";
                    }
                    $output2['type'] = "spline";
                    $output2['color'] = "#FA2C16";
                    $output2['yAxis'] = "1";
                    $output2['data'] = $output_[$regionnya];
                    $outputfin[] = $output2;
                }
                
                $output3['series'] = $outputfin;
                $output3['category']=$cat;
                return Core::setResponse("success",$output3);
                break;
            case 'ajax-pl-region-tracker':
                $dt = $request->all();
                $year = $_GET['year'];
                $week = $_GET['week'];
                $tracker = $_GET['tracker'];
                $type = $_GET['type'];
                $meas = $_GET['meas'];
                
                if($tracker == 't_monthly'){$mod='1';}else{$mod='2';}
                if($type == '3G'){$table = 't_pl_3G';}elseif($type=='4G'){$table='t_pl_4G';}else{$table='';}
                if(empty($week)){$week = '1';}else{}
                if($meas == 'latency'){$option_meas = 't1.lat_status = "NOT CLEAR"';}else{$option_meas = 'pl_status = "CONSECUTIVE"';}

                date_default_timezone_set("Asia/Jakarta");
                switch($mod){
                    case '1':	
                        //$query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_3G where pl_status = 'CONSECUTIVE' and year ='".date('Y')."' group by week";
                        $query = \DB::connection("mysql225a")->select('SET @tracker := "'.$tracker.'";');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('SET @tahun := "'.$year.'";');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('SET @weeknow := "'.$week.'";');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('SET @weekmax := (SELECT MAX(WEEK) FROM '.$table.' WHERE YEAR = @tahun);');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('
                            SET @baseline := (SELECT COUNT(if('.$option_meas.',1,NULL)) FROM '.$table.' AS t1
                                JOIN (
                                    SELECT siteid,t_jpp_batam,t_supreme,t_red_transport_q2,t_covid19,t_monthly FROM t_tracker_site
                                ) AS t2 ON t1.siteid=t2.siteid
                                WHERE @tracker <> "null" AND YEAR = @tahun AND WEEK = @weeknow);');
                        //mysqli_query($link, $query);
                        $doquery =\DB::connection("mysql225a")->select('		
                            SELECT t1.YEAR,t1.WEEK,t1.pl_status,t2.'.$tracker.' AS tracker,
                            COUNT(if('.$option_meas.',1,NULL)) AS baseline_new,@baseline-COUNT(if('.$option_meas.',1,NULL))  AS clear,@baseline AS baseline FROM '.$table.' AS t1
                            JOIN (
                                SELECT siteid,t_jpp_batam,t_supreme,t_red_transport_q2,t_covid19,t_monthly FROM t_tracker_site
                            ) AS t2 ON t1.siteid=t2.siteid
                            WHERE YEAR = @tahun 
                                AND '.$tracker.' <> "null"
                                AND WEEK between @weeknow AND @weekmax
                            GROUP BY week
                            ORDER BY t1.WEEK ASC
                        ');
                        break;
                    case '2':
                        $query = \DB::connection("mysql225a")->select('SET @tracker := "'.$tracker.'";');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('SET @tahun := "'.$year.'";');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('SET @weeknow := "'.$week.'";');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('SET @weekmax := (SELECT MAX(WEEK) FROM '.$table.' WHERE YEAR = @tahun);');
                        //mysqli_query($link, $query);
                        $query = \DB::connection("mysql225a")->select('
                            SET @baseline := (SELECT COUNT(if('.$option_meas.',1,NULL))  FROM '.$table.' AS t1
                                JOIN (
                                    SELECT siteid,t_jpp_batam,t_supreme,t_red_transport_q2,t_covid19,t_monthly FROM t_tracker_site
                                ) AS t2 ON t1.siteid=t2.siteid
                                WHERE @tracker <> "null" AND YEAR = @tahun AND WEEK = @weeknow);');
                        //mysqli_query($link, $query);
                        $doquery =\DB::connection("mysql225a")->select('		
                            SELECT t1.YEAR as year,t1.WEEK as week,t1.pl_status,t2.'.$tracker.' AS tracker,
                            COUNT(if('.$option_meas.',1,NULL)) AS baseline_new,@baseline-COUNT(if('.$option_meas.',1,NULL))  AS clear,@baseline AS baseline FROM '.$table.' AS t1
                            JOIN (
                                SELECT siteid,t_jpp_batam,t_supreme,t_red_transport_q2,t_covid19,t_monthly FROM t_tracker_site
                            ) AS t2 ON t1.siteid=t2.siteid
                            WHERE YEAR = @tahun 
                                AND '.$tracker.' <> "null"
                                AND WEEK between @weeknow AND @weekmax
                            GROUP BY week
                            ORDER BY t1.WEEK ASC
                        ');
                        break;
                    default:
                        echo "data not found";
                        $query = "";
                }
                
                $doquery = mysqli_query($link, $query) or die(mysqli_error($link));
                $count = 0;
                //while($data = mysqli_fetch_object($doquery)){
                foreach ($doquery as $doquery => $data) {
                    $ada = array();
                    $ada2 = array();
                    $tracker = array();
                    if($count == 0){
                        $categori[] = $data->week;
                        $baseline_new = $data->baseline;
                        $ada[] = $baseline_new;
                        $clear = 0;
                        $ada2[] = $clear;
                        $categori[] = "";
                        $output[$data->tracker][] = $ada;
                        $output_[$data->tracker][] = $ada2;			
                        $tracker[] = $data->tracker;
                        $count++;
                        break;
                    }
                }
                
                //while($data = mysqli_fetch_object($doquery)){
                foreach ($doquery as $doquery => $data) {    
                    $ada = array();
                    $ada2 = array();
                    $tracker = array();
                    $baseline_new = $data->baseline_new;
                    $ada[] = $baseline_new;
                    $clear = $data->clear;
                    $ada2[] = $clear;
                    $categori[] = $data->week;
                    $output[$data->tracker][] = $ada;
                    $output_[$data->tracker][] = $ada2;
                    $tracker[] = $data->tracker;
                }
                    
                
                
                $category = array_unique($categori);
                foreach($category as $key => $catnya){
                    $cat[]=$catnya;
                }
                sort($cat);
                $tracker_type = array_unique($tracker);
                //print_r($objectname);
            // $output2['type'] = 'column';
                foreach($tracker_type as $key => $trackernya){
                    //echo $mss."<br>";
                // $output2['name'] = $regionnya;
                    $output2['name'] = $trackernya." Clear";
                    $output2['color'] = "#00B0F0";
                    $output2['stack'] = "male";
                    $output2['data'] = $output_[$trackernya];
                    $outputfin[] = $output2;
                }
                $output3['series'] = $outputfin;
                foreach($tracker_type as $key => $trackernya){
                    //echo $mss."<br>";
                // $output2['name'] = $regionnya;
                    $output2['name'] = $trackernya." Baseline";
                    $output2['color'] = "#FA2C16";
                    $output2['stack'] = "male";
                    $output2['data'] = $output[$trackernya];
                    $outputfin[] = $output2;
                }
                $output3['series'] = $outputfin;
                $output3['category']=$cat;
                $output3['query']=$query;
                return Core::setResponse("success",$output3);
                break;
            case 'ajax-pl-region':
                $dt = $request->all();
                $type = $_GET['type'];
                $year = $_GET['year'];
                date_default_timezone_set("Asia/Jakarta");              
                if(!empty($year)){
                    $year = $year;
                }
                else{
                    $year = date('Y');
                }          
                switch($type){
                    case '3G':	
                        $query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_3G where pl_status = 'CONSECUTIVE' and year ='".$year."' group by week";
                        break;
                    case '4G':
                        $query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and year ='".$year."' group by week";
                        break;
                    case '3G_latency':	
                        $query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_3G where lat_status = 'NOT CLEAR' and year ='".$year."' group by week";
                        break;
                    case '4G_latency':
                        $query = "select week,'jawa  barat' as region,count(*) as jumlah from t_pl_4G where  lat_status = 'NOT-CLEAR' and year ='".$year."' group by week";
                        break;
                    case '3G_dep_latency':
                        //$query = "select week,nsa as region,COUNT(*) AS jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and YEAR ='".date('Y')."' group by week,nsa";
                        /*$query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, nsa, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' 
                                    GROUP BY week, nsa
                                ) AS t2
                                ON t1.week = t2.week and t1.nsa = t2.nsa
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' and t1.nsa NOT LIKE '#N/A'
                                GROUP BY t1.week, t1.nsa";*/
                        $query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                            FROM t_pl_3G AS t1
                            JOIN (
                            SELECT week, nsa, count(*) AS total 
                            FROM t_pl_3G
                                where YEAR = '".$year."'  
                            GROUP BY week, nsa
                            ) AS t2
                            ON t1.week = t2.week and t1.nsa = t2.nsa
                            WHERE lat_status NOT LIKE 'NOT CLEAR' and YEAR ='".$year."'  and t1.nsa NOT LIKE '#N/A'
                            GROUP BY t1.week, t1.nsa
                            UNION all
                            SELECT t1.week,'jawa  barat' as region,'dummy' as dummy1 ,'dummy' as dummy2 ,100 - ( count(*) * 100 /t2.jumlah) AS total
                            from t_pl_3G AS t1
                            JOIN (
                                SELECT week,'jawa  barat' as region,count(*) as jumlah 
                                from t_pl_3G 
                                where YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                                GROUP BY WEEK 
                            ) AS t2
                            ON t1.week = t2.week
                            where lat_status = 'NOT CLEAR'  and YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                            group by WEEK
                            ORDER BY WEEK asc";
                        break;
                    case '4G_dep_latency':
                        //$query = "select week,nsa as region,COUNT(*) AS jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and YEAR ='".$year."' group by week,nsa";
                        /*$query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, nsa, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' 
                                    GROUP BY week, nsa
                                ) AS t2
                                ON t1.week = t2.week and t1.nsa = t2.nsa
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' and t1.nsa NOT LIKE '#N/A'
                                GROUP BY t1.week, t1.nsa";*/
                        $query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                            FROM t_pl_4G AS t1
                            JOIN (
                            SELECT week, nsa, count(*) AS total 
                            FROM t_pl_4G
                                where YEAR = '".$year."'  
                            GROUP BY week, nsa
                            ) AS t2
                            ON t1.week = t2.week and t1.nsa = t2.nsa
                            WHERE lat_status NOT LIKE 'NOT-CLEAR' and YEAR ='".$year."'  and t1.nsa NOT LIKE '#N/A'
                            GROUP BY t1.week, t1.nsa
                            UNION all
                            SELECT t1.week,'jawa  barat' as region,'dummy' as dummy1 ,'dummy' as dummy2 ,100 - ( count(*) * 100 /t2.jumlah) AS total
                            from t_pl_4G AS t1
                            JOIN (
                                SELECT week,'jawa  barat' as regional,count(*) as jumlah 
                                from t_pl_4G 
                                where YEAR ='".$year."' AND region LIKE '%JAWA BARAT'
                                GROUP BY WEEK 
                            ) AS t2
                            ON t1.week = t2.week
                            where lat_status = 'NOT-CLEAR' and YEAR ='".$year."' AND region LIKE '%JAWA BARAT'
                            group by WEEK
                            ORDER BY WEEK asc";
                        break;
                    case '3G_dep':
                        //$query = "select week,nsa as region,COUNT(*) AS jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and YEAR ='".date('Y')."' group by week,nsa";
                        /*$query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, nsa, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' 
                                    GROUP BY week, nsa
                                ) AS t2
                                ON t1.week = t2.week and t1.nsa = t2.nsa
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' and t1.nsa NOT LIKE '#N/A'
                                GROUP BY t1.week, t1.nsa";*/
                        $query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                            FROM t_pl_3G AS t1
                            JOIN (
                            SELECT week, nsa, count(*) AS total 
                            FROM t_pl_3G
                                where YEAR = '".$year."'  
                            GROUP BY week, nsa
                            ) AS t2
                            ON t1.week = t2.week and t1.nsa = t2.nsa
                            WHERE pl_status NOT LIKE 'CONSECUTIVE' and YEAR ='".$year."'  and t1.nsa NOT LIKE '#N/A'
                            GROUP BY t1.week, t1.nsa
                            UNION all
                            SELECT t1.week,'jawa  barat' as region,'dummy' as dummy1 ,'dummy' as dummy2 ,100 - ( count(*) * 100 /t2.jumlah) AS total
                            from t_pl_3G AS t1
                            JOIN (
                                SELECT week,'jawa  barat' as region,count(*) as jumlah 
                                from t_pl_3G 
                                where YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                                GROUP BY WEEK 
                            ) AS t2
                            ON t1.week = t2.week
                            where pl_status = 'CONSECUTIVE' and YEAR ='".$year."' AND reg_name LIKE '%JAWA BARAT'
                            group by WEEK
                            ORDER BY WEEK asc";
                        break;
                    case '4G_dep':
                        //$query = "select week,nsa as region,COUNT(*) AS jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and YEAR ='".$year."' group by week,nsa";
                        /*$query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, nsa, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' 
                                    GROUP BY week, nsa
                                ) AS t2
                                ON t1.week = t2.week and t1.nsa = t2.nsa
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' and t1.nsa NOT LIKE '#N/A'
                                GROUP BY t1.week, t1.nsa";*/
                        $query = "SELECT t1.week, t1.nsa AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                            FROM t_pl_4G AS t1
                            JOIN (
                            SELECT week, nsa, count(*) AS total 
                            FROM t_pl_4G
                                where YEAR = '".$year."'  
                            GROUP BY week, nsa
                            ) AS t2
                            ON t1.week = t2.week and t1.nsa = t2.nsa
                            WHERE pl_status NOT LIKE 'CONSECUTIVE' and YEAR ='".$year."'  and t1.nsa NOT LIKE '#N/A'
                            GROUP BY t1.week, t1.nsa
                            UNION all
                            SELECT t1.week,'jawa  barat' as region,'dummy' as dummy1 ,'dummy' as dummy2 ,100 - ( count(*) * 100 /t2.jumlah) AS total
                            from t_pl_4G AS t1
                            JOIN (
                                SELECT week,'jawa  barat' as regional,count(*) as jumlah 
                                from t_pl_4G 
                                where YEAR ='".$year."' AND region LIKE '%JAWA BARAT'
                                GROUP BY WEEK 
                            ) AS t2
                            ON t1.week = t2.week
                            where pl_status = 'CONSECUTIVE' and YEAR ='".$year."' AND region LIKE '%JAWA BARAT'
                            group by WEEK
                            ORDER BY WEEK asc";
                        break;
                    case '3G_ns_srg':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%SOREANG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*)* 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%SOREANG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%SOREANG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '3G_ns_bdg':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%BANDUNG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%BANDUNG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%BANDUNG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '3G_ns_tsk':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%TASIKMALAYA' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '3G_ns_crb':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%CIREBON' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%CIREBON' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%CIREBON' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '4G_ns_srg':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%SOREANG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%SOREANG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%SOREANG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '4G_ns_bdg':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%BANDUNG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%BANDUNG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%BANDUNG' 
                                GROUP BY t1.week, t1.rtpo";
                        
                        break;
                    case '4G_ns_tsk':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%TASIKMALAYA' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                GROUP BY t1.week, t1.rtpo";
                        
                        break;
                    case '4G_ns_crb':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%CIREBON' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%CIREBON' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE pl_status NOT LIKE 'CONSECUTIVE' and year ='".$year."' AND  nsa LIKE '%CIREBON' 
                                GROUP BY t1.week, t1.rtpo";
                        
                        break;
                    case '3G_ns_srg_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%SOREANG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*)* 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%SOREANG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT CLEAR' and year ='".$year."' AND  nsa LIKE '%SOREANG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '3G_ns_bdg_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%BANDUNG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%BANDUNG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT CLEAR' and year ='".$year."' AND  nsa LIKE '%BANDUNG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '3G_ns_tsk_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%TASIKMALAYA' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT CLEAR' and year ='".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '3G_ns_crb_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_3G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%CIREBON' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_3G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_3G
                                        where YEAR = '".$year."' AND  nsa LIKE '%CIREBON' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT CLEAR' and year ='".$year."' AND  nsa LIKE '%CIREBON' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '4G_ns_srg_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%SOREANG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%SOREANG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT-CLEAR' and year ='".$year."' AND  nsa LIKE '%SOREANG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '4G_ns_bdg_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%BANDUNG' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%BANDUNG' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT-CLEAR' and year ='".$year."' AND  nsa LIKE '%BANDUNG' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '4G_ns_tsk_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%TASIKMALAYA' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT-CLEAR' and year ='".$year."' AND  nsa LIKE '%TASIKMALAYA' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    case '4G_ns_crb_latency':
                        //$query = "select week,rtpo as region,COUNT(*) as jumlah from t_pl_4G where  pl_status = 'CONSECUTIVE' and nsa LIKE '%CIREBON' and YEAR ='".$year."' group by week,rtpo";
                        $query = "SELECT t1.week, t1.rtpo AS region, count(*) AS `pl_site`, t2.total AS total, count(*) * 100 / t2.total AS jumlah
                                FROM t_pl_4G AS t1
                                JOIN (
                                    SELECT week, rtpo, count(*) AS total 
                                    FROM t_pl_4G
                                        where YEAR = '".$year."' AND  nsa LIKE '%CIREBON' 
                                    GROUP BY week, rtpo
                                ) AS t2
                                ON t1.week = t2.week and t1.rtpo = t2.rtpo
                                WHERE lat_status NOT LIKE 'NOT-CLEAR' and year ='".$year."' AND  nsa LIKE '%CIREBON' 
                                GROUP BY t1.week, t1.rtpo";
                        break;
                    default:
                        echo "data not found";
                        $query = "";
                }
                $doquery = \DB::connection("mysql225a")->select($query);
                    //while($data = mysqli_fetch_object($doquery)){
                    foreach ($doquery as $doquery => $data) {
                        $ada = array();
                        $ada[] = $data->week;
                        $datapercent = $data->jumlah;
                        $ada[] = $datapercent;
                        $categori[] = $data->week;
                        $output[$data->region][] = $ada;
                        $region[] = $data->region;
                    }
                $category = array_unique($categori);
                foreach($category as $key => $catnya){
                    $cat[]=$catnya;
                }
                sort($cat);
                $regional = array_unique($region);
                //print_r($objectname);
                foreach($regional as $key => $regionnya){
                    //echo $mss."<br>";
                    $output2['name'] = $regionnya;
                    $output2['data'] = $output[$regionnya];
                    $outputfin[] = $output2;
                }
                $output3['category']=$cat;
                $output3['series'] = $outputfin;
                return Core::setResponse("success",$output3);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function transportlvcq(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'ajax-get-lvqc':
                $dt = $request->all();
                $tech = $dt['tech'];
                $type = $dt['type'];
                $tanggal = $dt['tanggal'];
                $tanggallv = date('y-m-d', strtotime('-3 day', strtotime($tanggal)));
                $tgl =  date("d-m-y", strtotime($tanggal));
                $tgllv =  date('d-m-y', strtotime('-3 day', strtotime($tanggal)));
                $tgl2 = date('d-m-y', strtotime('-1 day', strtotime($tanggal)));
                $tgl3 = date('d-m-y', strtotime('-2 day', strtotime($tanggal)));
                $siteid = $dt['siteid'];
                $tanggal2 = date('Y-m-d', strtotime('-1 day', strtotime($tanggal)));
                $tanggal3 = date('Y-m-d', strtotime('-2 day', strtotime($tanggal)));
                date_default_timezone_set("Asia/Jakarta");
                switch ($type) {
                    case 'lv':
                        $query = "SELECT 'L2 Packet Loss' AS kpi,
                            max(case when jam = '17:00:00' then round(packetloss,2) END) 't1',
                            max(case when jam = '18:00:00' then round(packetloss,2) END) 't2',
                            max(case when jam = '19:00:00' then round(packetloss,2) END) 't3',
                            max(case when jam = '20:00:00' then round(packetloss,2) END) 't4',
                            max(case when jam = '21:00:00' then round(packetloss,2) END) 't5',
                            if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',null))) AS result,
                            'Clear/Spike' AS target,
                            '<= 0.1%' AS threshold,
                            if(SUM(if(packetloss>0.1,1,0))=0,'pass',if(SUM(if(packetloss>0.1,1,0))<3,'fail',if(SUM(if(packetloss>0.1,1,0))>2,'fail',null))) AS pass,
                            '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')
                            UNION 
                            SELECT 'L2 Latency' AS kpi,
                            max(case when jam = '17:00:00' then round(latency,2) END) 't1',
                            max(case when jam = '18:00:00' then round(latency,2) END) 't2',
                            max(case when jam = '19:00:00' then round(latency,2) END) 't3',
                            max(case when jam = '20:00:00' then round(latency,2) END) 't4',
                            max(case when jam = '21:00:00' then round(latency,2) END) 't5',
                            round(AVG(latency),2) AS result,
                            '<= 20 ms' AS target,
                            '<= 20 ms' AS threshold,
                            if( round(AVG(latency),2)>20,'fail',if( round(AVG(latency),2)<21,'pass',null)) AS pass,
                                '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')";
                        break;
                    case 'lv_backup':
                        $query = "SELECT 'L2 Packet Loss' AS kpi,
                            max(case when jam = '17:00:00' then round(packetloss,2) END) 't1',
                            max(case when jam = '18:00:00' then round(packetloss,2) END) 't2',
                            max(case when jam = '19:00:00' then round(packetloss,2) END) 't3',
                            max(case when jam = '20:00:00' then round(packetloss,2) END) 't4',
                            max(case when jam = '21:00:00' then round(packetloss,2) END) 't5',
                            if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',null))) AS result,
                            'Clear/Spike' AS target,
                            '<= 0.1%' AS threshold,
                            if(if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike','consec'))='consec','fail','pass') AS pass,
                            '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')
                            UNION 
                            SELECT 'L2 Latency' AS kpi,
                            max(case when jam = '17:00:00' then round(latency,2) END) 't1',
                            max(case when jam = '18:00:00' then round(latency,2) END) 't2',
                            max(case when jam = '19:00:00' then round(latency,2) END) 't3',
                            max(case when jam = '20:00:00' then round(latency,2) END) 't4',
                            max(case when jam = '21:00:00' then round(latency,2) END) 't5',
                            round(AVG(latency),2) AS result,
                            '<= 20 ms' AS target,
                            '<= 20 ms' AS threshold,
                            if( round(AVG(latency),2)>20,'fail',if( round(AVG(latency),2)<21,'pass',null)) AS pass,
                                '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')";
                        break;
                    case 'qc':
                        $query = "SELECT 'L2 Packet Loss' AS kpi,
                                    (SELECT if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) FROM sum_packetloss_hourly_$tech WHERE siteid = '$siteid'
                                            AND tanggal IN ('$tanggal3')
                                            AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')) AS 'DAY1',
                                    (SELECT if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) FROM sum_packetloss_hourly_$tech WHERE siteid = '$siteid'
                                            AND tanggal IN ('$tanggal2')
                                            AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')) AS 'DAY2',
                                    (SELECT if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) FROM sum_packetloss_hourly_$tech WHERE siteid = '$siteid'
                                            AND tanggal IN ('$tanggal')
                                            AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')) AS 'DAY3',
                                    if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) AS Result,
                                    'Clear/Spike' AS Target,
                                    '<= 0.1%' AS Threshold,
                                    if(if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike','consec'))='consec','fail','pass') AS Pass,
                                    '$tgl' as tgl,'$tgl2' as tgl2,'$tgl3' as tgl3
                                    FROM sum_packetloss_hourly_$tech
                                    WHERE siteid = '$siteid'
                                    AND tanggal IN ('$tanggal3','$tanggal2','$tanggal')
                                    AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')
                                    UNION all
                                    SELECT 'L2 Latency' AS kpi,
                                    round(avg(case when tanggal = '$tanggal3' then round(latency,2) END),2) 'DAY1',
                                    round(avg(case when tanggal = '$tanggal2' then round(latency,2) END),2) 'DAY2',
                                    round(avg(case when tanggal = '$tanggal' then round(latency,2) END),2) 'DAY3',
                                    round(AVG(latency),2) AS result,
                                    '<= 20 ms' AS Target,
                                    '<= 20 ms' AS Threshold,
                                    if( round(AVG(latency),2)>20,'fail',if( round(AVG(latency),2)<21,'pass',null)) AS pass,
                                    '$tgl' as tgl,'$tgl2' as tgl2,'$tgl3' as tgl3
                                    FROM sum_packetloss_hourly_$tech
                                    WHERE siteid = '$siteid'
                                    AND tanggal IN ('$tanggal3','$tanggal2','$tanggal')
                                    AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')";
                        break;
                    default:
                        echo "data not found";
                        $query = "";
                }
                $doquery = \DB::connection("mysql144")->select($query);
                $week = array();
                $nsa = array();
                $rtp = array();
                $pl_site = array();
                $percentage = array();
                $id = 0;
                if ($type == 'qc') {
                    //while ($data = mysqli_fetch_object($doquery)) {
                    foreach ($doquery as $doquery => $data) {
                        $tmp[$id][] = $data->kpi;
                        $tmp[$id][] = $data->DAY1;
                        $tmp[$id][] = $data->DAY2;
                        $tmp[$id][] = $data->DAY3;
                        $tmp[$id][] = $data->Result;
                        $tmp[$id][] = $data->Target;
                        $tmp[$id][] = $data->Threshold;
                        $tmp[$id][] = $data->Pass;
                        $tgl = $data->tgl;
                        $tgl2 = $data->tgl2;
                        $tgl3 = $data->tgl3;
                        $id++;
                    }
                } else {
                    //while ($data = mysqli_fetch_object($doquery)) {
                    foreach ($doquery as $doquery => $data) {
                        $tmp[$id][] = $data->kpi;
                        $tmp[$id][] = $data->t1;
                        $tmp[$id][] = $data->t2;
                        $tmp[$id][] = $data->t3;
                        $tmp[$id][] = $data->t4;
                        $tmp[$id][] = $data->t5;
                        $tmp[$id][] = $data->result;
                        $tmp[$id][] = $data->target;
                        $tmp[$id][] = $data->threshold;
                        $tmp[$id][] = $data->pass;
                        $tgl =  $data->tgl;
                        $id++;
                    }
                }
                $output3['data'] = $tmp;
                $output3['query'] = $query;
                $output3['tanggal'] = $tgl;
                $output3['tanggal2'] = $tgl2;
                $output3['tanggal3'] = $tgl3;  
                //echo json_encode($output3, JSON_NUMERIC_CHECK);
                $output = \DB::connection("mysql144")->select("
                ");
                return Core::setResponse("success",$output3);
                break;
        }
    }

    public function transportmappingperform(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'ajax-get-lvqc':
                $dt = $request->all();
                $tech = $dt['tech'];
                $type = $dt['type'];
                $tanggal = $dt['tanggal'];
                $tanggallv = date('y-m-d', strtotime('-3 day', strtotime($tanggal)));
                $tgl =  date("d-m-y", strtotime($tanggal));
                $tgllv =  date('d-m-y', strtotime('-3 day', strtotime($tanggal)));
                $tgl2 = date('d-m-y', strtotime('-1 day', strtotime($tanggal)));
                $tgl3 = date('d-m-y', strtotime('-2 day', strtotime($tanggal)));
                $siteid = $dt['siteid'];
                $tanggal2 = date('Y-m-d', strtotime('-1 day', strtotime($tanggal)));
                $tanggal3 = date('Y-m-d', strtotime('-2 day', strtotime($tanggal)));
                date_default_timezone_set("Asia/Jakarta");
                switch ($type) {
                    case 'lv':
                        $query = "SELECT 'L2 Packet Loss' AS kpi,
                            max(case when jam = '17:00:00' then round(packetloss,2) END) 't1',
                            max(case when jam = '18:00:00' then round(packetloss,2) END) 't2',
                            max(case when jam = '19:00:00' then round(packetloss,2) END) 't3',
                            max(case when jam = '20:00:00' then round(packetloss,2) END) 't4',
                            max(case when jam = '21:00:00' then round(packetloss,2) END) 't5',
                            if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',null))) AS result,
                            'Clear/Spike' AS target,
                            '<= 0.1%' AS threshold,
                            if(SUM(if(packetloss>0.1,1,0))=0,'pass',if(SUM(if(packetloss>0.1,1,0))<3,'fail',if(SUM(if(packetloss>0.1,1,0))>2,'fail',null))) AS pass,
                            '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')
                            UNION 
                            SELECT 'L2 Latency' AS kpi,
                            max(case when jam = '17:00:00' then round(latency,2) END) 't1',
                            max(case when jam = '18:00:00' then round(latency,2) END) 't2',
                            max(case when jam = '19:00:00' then round(latency,2) END) 't3',
                            max(case when jam = '20:00:00' then round(latency,2) END) 't4',
                            max(case when jam = '21:00:00' then round(latency,2) END) 't5',
                            round(AVG(latency),2) AS result,
                            '<= 20 ms' AS target,
                            '<= 20 ms' AS threshold,
                            if( round(AVG(latency),2)>20,'fail',if( round(AVG(latency),2)<21,'pass',null)) AS pass,
                                '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')";
                        break;
                    case 'lv_backup':
                        $query = "SELECT 'L2 Packet Loss' AS kpi,
                            max(case when jam = '17:00:00' then round(packetloss,2) END) 't1',
                            max(case when jam = '18:00:00' then round(packetloss,2) END) 't2',
                            max(case when jam = '19:00:00' then round(packetloss,2) END) 't3',
                            max(case when jam = '20:00:00' then round(packetloss,2) END) 't4',
                            max(case when jam = '21:00:00' then round(packetloss,2) END) 't5',
                            if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',null))) AS result,
                            'Clear/Spike' AS target,
                            '<= 0.1%' AS threshold,
                            if(if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike','consec'))='consec','fail','pass') AS pass,
                            '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')
                            UNION 
                            SELECT 'L2 Latency' AS kpi,
                            max(case when jam = '17:00:00' then round(latency,2) END) 't1',
                            max(case when jam = '18:00:00' then round(latency,2) END) 't2',
                            max(case when jam = '19:00:00' then round(latency,2) END) 't3',
                            max(case when jam = '20:00:00' then round(latency,2) END) 't4',
                            max(case when jam = '21:00:00' then round(latency,2) END) 't5',
                            round(AVG(latency),2) AS result,
                            '<= 20 ms' AS target,
                            '<= 20 ms' AS threshold,
                            if( round(AVG(latency),2)>20,'fail',if( round(AVG(latency),2)<21,'pass',null)) AS pass,
                                '$tgllv' as tgl
                            FROM sum_packetloss_hourly_$tech
                            WHERE siteid = '$siteid'
                            and tanggal = '$tanggallv' 
                            AND jam IN ('17:00:00','18:00:00','19:00:00','20:00:00','21:00:00')";
                        break;
                    case 'qc':
                        $query = "SELECT 'L2 Packet Loss' AS kpi,
                                    (SELECT if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) FROM sum_packetloss_hourly_$tech WHERE siteid = '$siteid'
                                            AND tanggal IN ('$tanggal3')
                                            AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')) AS 'DAY1',
                                    (SELECT if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) FROM sum_packetloss_hourly_$tech WHERE siteid = '$siteid'
                                            AND tanggal IN ('$tanggal2')
                                            AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')) AS 'DAY2',
                                    (SELECT if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) FROM sum_packetloss_hourly_$tech WHERE siteid = '$siteid'
                                            AND tanggal IN ('$tanggal')
                                            AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')) AS 'DAY3',
                                    if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike',if(SUM(if(packetloss>0.1,1,0))>2,'consec',NULL))) AS Result,
                                    'Clear/Spike' AS Target,
                                    '<= 0.1%' AS Threshold,
                                    if(if(SUM(if(packetloss>0.1,1,0))=0,'clear',if(SUM(if(packetloss>0.1,1,0))<3,'spike','consec'))='consec','fail','pass') AS Pass,
                                    '$tgl' as tgl,'$tgl2' as tgl2,'$tgl3' as tgl3
                                    FROM sum_packetloss_hourly_$tech
                                    WHERE siteid = '$siteid'
                                    AND tanggal IN ('$tanggal3','$tanggal2','$tanggal')
                                    AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')
                                    UNION all
                                    SELECT 'L2 Latency' AS kpi,
                                    round(avg(case when tanggal = '$tanggal3' then round(latency,2) END),2) 'DAY1',
                                    round(avg(case when tanggal = '$tanggal2' then round(latency,2) END),2) 'DAY2',
                                    round(avg(case when tanggal = '$tanggal' then round(latency,2) END),2) 'DAY3',
                                    round(AVG(latency),2) AS result,
                                    '<= 20 ms' AS Target,
                                    '<= 20 ms' AS Threshold,
                                    if( round(AVG(latency),2)>20,'fail',if( round(AVG(latency),2)<21,'pass',null)) AS pass,
                                    '$tgl' as tgl,'$tgl2' as tgl2,'$tgl3' as tgl3
                                    FROM sum_packetloss_hourly_$tech
                                    WHERE siteid = '$siteid'
                                    AND tanggal IN ('$tanggal3','$tanggal2','$tanggal')
                                    AND jam not IN ('00:00:00','01:00:00','02:00:00','03:00:00','04:00:00','05:00:00')";
                        break;
                    default:
                        echo "data not found";
                        $query = "";
                }
                $doquery = \DB::connection("mysql144")->select($query);
                $week = array();
                $nsa = array();
                $rtp = array();
                $pl_site = array();
                $percentage = array();
                $id = 0;
                if ($type == 'qc') {
                    //while ($data = mysqli_fetch_object($doquery)) {
                    foreach ($doquery as $doquery => $data) {
                        $tmp[$id][] = $data->kpi;
                        $tmp[$id][] = $data->DAY1;
                        $tmp[$id][] = $data->DAY2;
                        $tmp[$id][] = $data->DAY3;
                        $tmp[$id][] = $data->Result;
                        $tmp[$id][] = $data->Target;
                        $tmp[$id][] = $data->Threshold;
                        $tmp[$id][] = $data->Pass;
                        $tgl = $data->tgl;
                        $tgl2 = $data->tgl2;
                        $tgl3 = $data->tgl3;
                        $id++;
                    }
                } else {
                    //while ($data = mysqli_fetch_object($doquery)) {
                    foreach ($doquery as $doquery => $data) {
                        $tmp[$id][] = $data->kpi;
                        $tmp[$id][] = $data->t1;
                        $tmp[$id][] = $data->t2;
                        $tmp[$id][] = $data->t3;
                        $tmp[$id][] = $data->t4;
                        $tmp[$id][] = $data->t5;
                        $tmp[$id][] = $data->result;
                        $tmp[$id][] = $data->target;
                        $tmp[$id][] = $data->threshold;
                        $tmp[$id][] = $data->pass;
                        $tgl =  $data->tgl;
                        $id++;
                    }
                }
                $output3['data'] = $tmp;
                $output3['query'] = $query;
                $output3['tanggal'] = $tgl;
                $output3['tanggal2'] = $tgl2;
                $output3['tanggal3'] = $tgl3;  
                //echo json_encode($output3, JSON_NUMERIC_CHECK);
                $output = \DB::connection("mysql144")->select("
                ");
                return Core::setResponse("success",$output3);
                break;
            case 'ajax-get-log-alarm-based-on-site-id':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $site_id = $dt['site_id'];
                $query = "
                        SELECT *, a1.nsa AS NSA, a1.rtp AS RTP, case when (select count(*) from 85152_trafficability.ran_alarm where siteid=d1.siteid and band='EAS' and string_alarm like '%65033%') > 0 then 'yes' else 'no' end as eas_mainfail
                        FROM 16010754_dapot_site.dapot_site a1 
                        JOIN 16010754_dapot_site.dapot_ne b1 ON (a1.site_id = b1.site_id)
                        JOIN 16010754_dapot_site.dapot_cell c1 ON (b1.ne_id = c1.ne_id)
                        JOIN 85152_trafficability.ran_alarm d1  ON (c1.sector_name = d1.cell_name)
                        WHERE a1.site_id = '" . $site_id . "'
                    ";
                $output = array();
                $count = \DB::connection("mysql222c")->select($query)->count();
                $data = \DB::connection("mysql222c")->select($query);
                if ($count > 0) {
                    $output['status'] = TRUE;
                    $output['message'] = "Data Found";
                    $output['result'] = $data;
                } else {
                    $output['status'] = FALSE;
                    $output['message'] = "Data Not Found";
                    $output['result'] = 0;
                }
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql170")->select("select distinct(rtp) from hourly_monitoring_packetloss
                    where tanggal = (select max(tanggal) from hourly_monitoring_packetloss) and rtp is not null
                ");
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-map-site-down-rtp':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');
                $timefilter = $_REQUEST['timefilter'];
                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1 . ":" . $minutes . ":00";
                if (!empty($_GET['rtp'])) {
                    $condition = "and rtp='" . $_GET['rtp'] . "'";
                } else {
                    $condition = "";
                }
                $query = "
                        select siteid,neid,band,sitename,count(cell_name) as jml_celldown,max(mydatetime) as alarm_awal,d1.nsa,d1.rtp,d1.class_revenue,longitude,latitude,
                        case 
                        WHEN 
                            (TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) < 1800) THEN 'less_30m'
                        WHEN 
                            (TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) >= 1800 && TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) <= 3600 ) THEN 'between_30m_1h'
                        WHEN 
                            (TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) > 3600 && TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) <= 7200 ) THEN 'between_1h_2h'
                        WHEN 
                            (TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) > 7200 && TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) <= 21600 ) THEN 'between_2h_6h' 
                        WHEN 
                            (TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) > 21600 && TIME_TO_SEC(TIMEDIFF(NOW() ,MAX(d1.mydatetime))) <= 86400 ) THEN 'between_6h_1d'
                        ELSE 'more_1d' 
                        end as remark_down
                        FROM 16010754_dapot_site.dapot_site a1 
                        JOIN 85152_trafficability.ran_alarm d1  ON (a1.site_id=d1.siteid)
                        where d1.band in ('2G','3G','4G')
                        and mydatetime >='" . $timefilter . "'
                        group by neid
                    ";
                $data = \DB::connection("mysql170")->select($query);
                $count_site = \DB::connection("mysql170")->select($query)->count();

                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach ($data as $result) {
                    $text = "Site Id : " . $result->siteid . " Site Name : " . $result->sitename . "";
                    $results[$counter]['title'] = "Site Id : " . $result->siteid;
                    $results[$counter]['site_id'] = $result->siteid;
                    $results[$counter]['site_name'] = $result->sitename;
                    $results[$counter]['ne_id'] = $result->neid;
                    $results[$counter]['band'] = $result->band;
                    $results[$counter]['rtp'] = $result->rtp;
                    $results[$counter]['nsa'] = $result->nsa;
                    $results[$counter]['class_revenue'] = $result->class_revenue;
                    $results[$counter]['lat'] = (float)$result->latitude;
                    $results[$counter]['lng'] = (float)$result->longitude;
                    $results[$counter]['data'] = $text;
                    $results[$counter]['legend'] = $result->remark_down;
                    $results[$counter]['tanggal'] = $result->alarm_awal;

                    $counter++;
                }
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
            case 'ajax-get-map-site-down-table':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                include("../../../../../function/fungsi-sql.php");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');
                $timefilter = $_REQUEST['timefilter'];
                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1 . ":" . $minutes . ":00";
                if (!empty($_GET['rtp'])) {
                    $condition = "and rtp='" . $_GET['rtp'] . "'";
                } else {
                    $condition = "";
                }
                $query = "select mydate  + interval 15 minute as waktu,count(neid) as jml_ne_down,
                    count(case when class ='platinum' then 1 else null end) as jml_platinum,
                    count(case when class ='gold' then 1 else null end) as jml_gold,
                    count(case when class ='silver' then 1 else null end) as jml_silver,
                    count(case when class ='bronze' then 1 else null end) as jml_bronze,
                    count(case when class is null then 1 else null end) as jml_unknown,
                    count(case when band ='2G' then 1 else null end) as jml_2g,
                    count(case when band ='3G' then 1 else null end) as jml_3g,
                    count(case when band ='4G' then 1 else null end) as jml_4g
                    from 85152_trafficability.tracking_ne_down
                    where mydate >='" . $timefilter . "'
                    group by mydate";
                $data = \DB::connection("mysql222c")->select($query);
                foreach ($data as $res) {
                    $timestamp = strtotime($res->waktu);
                    $awalstr = 1000 * $timestamp + (3600 * 7 * 1000);
                    $str['ne_down'][] = array($awalstr, $res->jml_ne_down);
                    $str['platinum'][] = array($awalstr, $res->jml_platinum);
                    $str['gold'][] = array($awalstr, $res->jml_gold);
                    $str['silver'][] = array($awalstr, $res->jml_silver);
                    $str['bronze'][] = array($awalstr, $res->jml_bronze);
                    $str['unknown'][] = array($awalstr, $res->jml_unknown);
                    $str['jml_2g'][] = array($awalstr, $res->jml_2g);
                    $str['jml_3g'][] = array($awalstr, $res->jml_3g);
                    $str['jml_4g'][] = array($awalstr, $res->jml_4g);
                }
                $array_kolom = array('ne_down', 'platinum', 'gold', 'silver', 'bronze', 'unknown', 'jml_2g', 'jml_3g', 'jml_4g');
                for ($i = 0; $i < count($array_kolom); $i++) {
                    $series['name'] = $array_kolom[$i];
                    $series['data'] = $str[$array_kolom[$i]];
                    $output['series'][] = $series;
                }
                $results['new_date'] = $new_date;
                $results['query'] = $query;
                $results['data'] = $output;
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
            case 'ajax-get-map-site-iubdrop-rtp':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                include("../../../../../function/fungsi-sql.php");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');

                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1 . ":" . $minutes . ":00";
                if (!empty($_GET['rtp'])) {
                    $condition = "and rtp='" . $_GET['rtp'] . "'";
                } else {
                    $condition = "";
                }
                if (!empty($_GET['tanggal']) and $_GET['type'] == '3G') {
                    $query = "select a.tanggal,a.nsa,a.rtp,a.site_id as siteid,a.site_name,if(remarkULDrop = 'DROP' or remarkDlDrop='DROP','CONSECUTIVE','CLEAR') as remarkiubdrop,remarkUlDrop,remarkDlDrop,b.Lat as latitude,b.`Long` as longitude 
                        from hourly_monitoring_iubdrop a left join dapot_transport b on a.site_id=b.Site_ID 
                        where tanggal = '" . $_GET['tanggal'] . "' and a.site_id is not null and lat is not null 
                        " . $condition . "
                        group by siteid
                        ";
                    $results['tanggal'] = $_GET['tanggal'];
                } else if (empty($_GET['tanggal']) and $_GET['type'] == '3G') {
                    $query = "select a.tanggal,a.nsa,a.rtp,a.site_id as siteid,a.site_name,if(remarkULDrop = 'DROP' or remarkDlDrop='DROP','CONSECUTIVE','CLEAR') as remarkiubdrop,remarkUlDrop,remarkDlDrop,b.Lat as latitude,b.`Long` as longitude 
                        from hourly_monitoring_iubdrop a left join dapot_transport b on a.site_id=b.Site_ID 
                        where tanggal = (select max(tanggal) from hourly_monitoring_packetloss) and a.site_id is not null and lat is not null 
                        " . $condition . "
                        group by siteid
                        ";
                    $results['tanggal'] = 'latest';
                } else {
                    $query = "";
                    $results['tanggal'] = 'data tidak tersedia';
                    $results['data'] = 'data tidak tersedia';
                }

                $data = \DB::connection("mysql225")->select($query);
                $count_site = \DB::connection("mysql225")->select($query)->count();

                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach ($data as $result) {

                    $text = "Site Id : " . $result->siteid . " Site Name : " . $result->site_name . "";

                    $results[$counter]['title'] = "Site Id : " . $result->siteid;
                    $results[$counter]['site_id'] = $result->siteid;
                    $results[$counter]['site_name'] = $result->site_name;
                    $results[$counter]['lat'] = (float)$result->latitude;
                    $results[$counter]['lng'] = (float)$result->longitude;
                    $results[$counter]['data'] = $text;
                    $results[$counter]['legend'] = $result->remarkiubdrop;
                    $results[$counter]['tanggal'] = $result->tanggal;

                    $counter++;
                }
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
            case 'ajax-get-map-site-neighbor-longlat':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                include("../../../../../function/fungsi-sql.php");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');

                $new_date = $new1 . ":" . $minutes . ":00";
                if (!empty($_GET['coordinate'])) {
                    list($lat, $long) = explode(",", $_REQUEST['coordinate'], 2);
                } else {
                    $lat = -6.9245556;
                    $long = 107.671978;
                }
                $query = "SELECT
                        b.siteid as neighbor
                        , a.siteid as home
                        , 6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat))) AS DISTANCE,b.lng,b.lat,('neighbor') as remark
                    FROM
                        (select $lat as lat ,$long as lng,'lokasi' as siteid) a
                        ,gcell_sitelonglat b
                    WHERE  6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat)))<=4 
                    ORDER BY distance asc";
                $data = db_query2listCON($link, $query);
                $count_site = db_num_rowsCON($link, $query);
                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach ($data as $result) {
                    $results[$counter]['site'] = $result->neighbor;
                    $results[$counter]['neighbor'] = $result->neighbor;
                    $results[$counter]['distance'] = (float)$result->DISTANCE;
                    $results[$counter]['longitude'] = (float)$result->lng;
                    $results[$counter]['latitude'] = (float)$result->lat;
                    $results[$counter]['remark'] = $result->remark;
                    $counter++;
                }
                $query2 = "select minimal.*,source.neighbor,sudut_sebenarnya,lat_a,long_a,lat_b,long_b from (
                        select kelompok_sudut,MIN(jarak_meter) as min_jarak from (
                        select *,
                        case 
                        when (sudut_sebenarnya >= 0 and sudut_sebenarnya < 30) then 'kel000'
                        when (sudut_sebenarnya >= 30 and sudut_sebenarnya < 60) then 'kel030'
                        when (sudut_sebenarnya >= 60 and sudut_sebenarnya < 90) then 'kel060'
                        when (sudut_sebenarnya >= 90 and sudut_sebenarnya < 120) then 'kel090'
                        when (sudut_sebenarnya >= 120 and sudut_sebenarnya < 150) then 'kel120'
                        when (sudut_sebenarnya >= 150 and sudut_sebenarnya < 180) then 'kel150'
                        when (sudut_sebenarnya >= 180 and sudut_sebenarnya < 210) then 'kel180'
                        when (sudut_sebenarnya >= 210 and sudut_sebenarnya < 240) then 'kel210'
                        when (sudut_sebenarnya >= 240 and sudut_sebenarnya < 270) then 'kel240'
                        when (sudut_sebenarnya >= 270 and sudut_sebenarnya < 300) then 'kel270'
                        when (sudut_sebenarnya >= 300 and sudut_sebenarnya < 330) then 'kel300'
                        when (sudut_sebenarnya >= 330 and sudut_sebenarnya < 360) then 'kel330'
                        end
                        as kelompok_sudut
                        from (
                        select *,
                        case 
                        when (long_a < long_b and lat_a < lat_b) then sudut
                        when (long_a < long_b and lat_a > lat_b) then 180 - sudut
                        when (long_a > long_b and lat_a > lat_b) then 180 + sudut
                        when (long_a > long_b and lat_a < lat_b) then 360 - sudut
                        end
                        as sudut_sebenarnya

                        from (
                        select *,
                        6371*ACOS(COS(RADIANS(lat_a))*COS(RADIANS(lat_a))*COS(RADIANS(long_a)-RADIANS(long_b))+SIN(RADIANS(lat_a))*SIN(RADIANS(lat_a))) as norm_b,
                        round(DEGREES(asin((6371*ACOS(COS(RADIANS(lat_a))*COS(RADIANS(lat_a))*COS(RADIANS(long_a)-RADIANS(long_b))+SIN(RADIANS(lat_a))*SIN(RADIANS(lat_a))))/DISTANCE)),2) as sudut,
                        round(DISTANCE * 1000,2) as jarak_meter
                        from (
                        select b.siteid as neighbor
                                , a.siteid as home,b.site_name
                                , 6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat))) AS DISTANCE,b.lng as long_b,b.lat as lat_b,a.lng as long_a,a.lat as lat_a
                        FROM
                                (select $lat as lat ,$long as lng,'lokasi' as siteid ) a
                                , gcell_sitelonglat b
                                WHERE  6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat)))<=4 
                        ORDER BY distance asc
                        ) aa
                        ) pp

                        ) zz
                        ) xx
                        group by kelompok_sudut
                        ) minimal
                        left join 
                        (
                        select *,
                        case 
                        when (long_a < long_b and lat_a < lat_b) then sudut
                        when (long_a < long_b and lat_a > lat_b) then 180 - sudut
                        when (long_a > long_b and lat_a > lat_b) then 180 + sudut
                        when (long_a > long_b and lat_a < lat_b) then 360 - sudut
                        end
                        as sudut_sebenarnya

                        from (
                        select *,
                        6371*ACOS(COS(RADIANS(lat_a))*COS(RADIANS(lat_a))*COS(RADIANS(long_a)-RADIANS(long_b))+SIN(RADIANS(lat_a))*SIN(RADIANS(lat_a))) as norm_b,
                        round(DEGREES(asin((6371*ACOS(COS(RADIANS(lat_a))*COS(RADIANS(lat_a))*COS(RADIANS(long_a)-RADIANS(long_b))+SIN(RADIANS(lat_a))*SIN(RADIANS(lat_a))))/DISTANCE)),2) as sudut,
                        round(DISTANCE * 1000,2) as jarak_meter
                        from (
                        select b.siteid as neighbor
                                , a.siteid as home,b.site_name
                                , 6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat))) AS DISTANCE,b.lng as long_b,b.lat as lat_b,a.lng as long_a,a.lat as lat_a
                        FROM
                                (select $lat as lat ,$long as lng,'lokasi' as siteid ) a
                                , gcell_sitelonglat b
                                WHERE  6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat)))<=4 
                        ORDER BY distance asc
                        ) aa
                        ) pp

                        ) source
                        on source.jarak_meter = minimal.min_jarak
                        order by min_jarak";
                $data2 = \DB::connection("mysql222d")->select($query2);
                $count_site2 = \DB::connection("mysql222d")->select($query2)->count();
                $co = 0;
                $list_neighbor = array();
                $results2['count_site'] = $count_site;
                foreach ($data2 as $result) {
                    $res[$co]['kelompok_sudut'] = $result->kelompok_sudut;
                    $res[$co]['min_jarak'] = $result->min_jarak;
                    $res[$co]['sudut'] = $result->sudut_sebenarnya;
                    $res[$co]['neighbor'] = $result->neighbor;
                    $res[$co]['lat_a'] = $result->lat_a;
                    $res[$co]['lat_b'] = $result->lat_b;
                    $res[$co]['long_a'] = $result->long_a;
                    $res[$co]['long_b'] = $result->long_b;
                    $list_neighbor[] = $result->neighbor;
                    $co++;
                }
                foreach ($list_neighbor as $siteid) {
                    $query = "select '" . $siteid . "' as siteid,alarm_update_time,
                        count(if(severity='Major',1,null)) as Major_alarm,
                        count(if(severity='Minor',1,null)) as Minor_alarm,
                        count(if(severity='Warning',1,null)) as Warning_alarm,
                        count(if(severity='Critical',1,null)) as Critical_alarm
                        from 85152_regionjabo.dump_alarm2G3G where location_info like '%" . $siteid . "%' and regional='Regional_4'";
                    $doquery = \DB::connection("mysql222d")->select($query);
                    //while ($dt = mysqli_fetch_object($doquery)) {
                    foreach ($doquery as $doquery => $dt) {
                        $alm['siteid'] = $dt->siteid;
                        $alm['critical'] = $dt->Critical_alarm;
                        $alm['major'] = $dt->Major_alarm;
                        $alm['minor'] = $dt->Minor_alarm;
                        $alm['warning'] = $dt->Warning_alarm;
                        $alm['lastupdate'] = $dt->alarm_update_time;
                        $alarm[] = $alm;
                    }
                }
                $output['neighbor'] = $results;
                $output['nearest'] = $res;
                $output['alarm'] = $alarm;
                //echo json_encode($output, JSON_NUMERIC_CHECK);
                return Core::setResponse("success",$output);
                break;
            case 'ajax-get-map-site-neighbor':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');

                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1 . ":" . $minutes . ":00";
                if (!empty($_GET['siteid'])) {
                    $siteid = $_GET['siteid'];
                } else {
                    $siteid = 'BDG500';
                }
                $query = "SELECT
                        b.siteid as neighbor
                        , a.siteid as home
                        , 6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat))) AS DISTANCE,b.lng,b.lat,if(b.siteid=a.siteid,'home','neighbor') as remark
                    FROM
                        (select * from gcell_sitelonglat where siteid='" . $siteid . "' ) a
                        INNER JOIN gcell_sitelonglat b
                    ON (a.regional = b.regional) WHERE  6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat)))<=4 
                    ORDER BY distance asc";
                $data = \DB::connection("mysql222d")->select($query);
                $count_site = \DB::connection("mysql222d")->select($query)->count();
                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach ($data as $result) {
                    $results[$counter]['site'] = $result->neighbor;
                    $results[$counter]['neighbor'] = $result->neighbor;
                    $results[$counter]['distance'] = (float)$result->DISTANCE;
                    $results[$counter]['longitude'] = (float)$result->lng;
                    $results[$counter]['latitude'] = (float)$result->lat;
                    $results[$counter]['remark'] = $result->remark;
                    $counter++;
                }
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
            case 'ajax-get-map-site-packetloss-rtp':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');

                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1.":".$minutes.":00";
                if(!empty($_GET['rtp']) and !empty($_GET['type']))
                {
                    $condition = "and rtp='".$_GET['rtp']."'";
                }else{
                    $condition = "";
                }
                if(!empty($_GET['type']) ){
                    if($_GET['type'] == '3G'){
                        $table = 'hourly_monitoring_packetloss';
                    }else{
                        $table = 'hourly_monitoring_packetloss4g';
                    }
                }
                else{
                    $table = 'hourly_monitoring_packetloss';
                }
                if(!empty($_GET['tanggal'])){
                    if($_GET['type'] == '3G'){
                        $query = "
                        select a.tanggal as datetime,a.nsa,a.rtp,a.siteid,a.site_name,remarkCount,b.Lat as latitude,b.`Longitude` as longitude 
                        from $table a left join dapot_transport_new b on a.siteid=b.Site_ID 
                        where tanggal = '".$_GET['tanggal']."' and siteid is not null and lat is not null ".$condition."
                        group by siteid
                    ";
                        $results['tanggal'] = $_GET['tanggal'];
                    }
                    else{
                        $query = "
                        select concat(a.tanggal,' ',a.jam) as datetime,a.nsa,a.rtp,a.siteid,a.sitename as site_name,if(packetloss>0.1,'CONSECUTIVE','CLEAR')  as remarkCount,b.Lat as latitude,b.`Longitude` as longitude 
                        from $table a left join dapot_transport_new b on a.siteid=b.Site_ID 
                        where concat(tanggal,' ',jam) = '".$_GET['tanggal']."' and siteid is not null and lat is not null ".$condition."
                        group by siteid
                    ";
                        $results['tanggal'] = $_GET['tanggal'];			
                    }
                }
                else{
                    if($_GET['type'] == '3G'){
                        $query = "
                        select a.tanggal as datetime,a.nsa,a.rtp,a.siteid,a.site_name,remarkCount,b.Lat as latitude,b.`Longitude` as longitude 
                        from $table a left join dapot_transport_new b on a.siteid=b.Site_ID 
                        where tanggal = (select max(tanggal) from $table) and siteid is not null and lat is not null ".$condition."
                        group by siteid
                    ";
                        $results['tanggal'] = 'latest';
                    }
                    else {
                        $query = "
                        select concat(a.tanggal,' ',a.jam) as datetime,a.nsa,a.rtp,a.siteid,a.sitename as site_name,if(packetloss>0.1,'CONSECUTIVE','CLEAR') as remarkCount,b.Lat as latitude,b.`Longitude` as longitude 
                        from $table a left join dapot_transport_new b on a.siteid=b.Site_ID 
                        where concat(tanggal,' ',jam) = (select max(concat(tanggal,' ',jam)) from $table) and siteid is not null and lat is not null ".$condition."
                        group by siteid
                    ";
                        $results['tanggal'] = 'latest';;
                    }
                }
                $results['query'] = $query;
                $data = \DB::connection("mysql170")->select($query);
                $count_site = \DB::connection("mysql170")->select($query)->count();
            
                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach($data as $result){
                    $text = "Site Id : ".$result->siteid." Site Name : ".$result->site_name."";
                    $results[$counter]['title'] = "Site Id : ".$result->siteid;
                    $results[$counter]['site_id'] = $result->siteid;
                    $results[$counter]['site_name'] = $result->site_name;
                    $results[$counter]['lat'] = (float)$result->latitude;
                    $results[$counter]['lng'] = (float)$result->longitude;
                    $results[$counter]['data'] = $text;
                    $results[$counter]['legend'] = $result->remarkCount;
                    $results[$counter]['tanggal'] = $result->datetime;
                    $counter++;
                }
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
            case 'ajax-get-map-site-sector-longlat':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');

                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1 . ":" . $minutes . ":00";
                //	if(!empty($_GET['siteid']))
                //    {
                //        $condition = "where siteid='".$_GET['siteid']."'";
                //    }else{
                //        $condition = "";
                //    }
                if (!empty($_GET['coordinate'])) {
                    list($lat, $long) = explode(",", $_REQUEST['coordinate'], 2);
                } else {
                    $lat = -6.9245556;
                    $long = 107.671978;
                }

                $query = "select bb.*,aa.remark,
                        longitude - (0.0007 * size)*cos(RADIANS(90 - DIR - (bw/2))) as x1,
                        Latitude - (0.0007 * size)*sin(RADIANS(90 - DIR - (bw/2))) as y1,
                        longitude - (0.0007 * size)*cos(RADIANS(90 - DIR + (bw/2))) as x2,
                        Latitude - (0.0007 * size)*sin(RADIANS(90 - DIR + (bw/2))) as y2 from 
                    (SELECT
                        b.siteid as neighbor
                        , a.siteid as home
                        , 6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat))) AS DISTANCE,b.lng,b.lat,
                            ('neighbor') as remark
                    FROM
                        (select $lat as lat ,$long as lng,'lokasi' as siteid ) a
                    , gcell_sitelonglat b
                    WHERE  6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat)))<=4 
                    ORDER BY distance asc) aa
                    INNER JOIN
                    (select CellName,SiteID,DIR,BEAM,Concat(lac,'-',Ci) as lac_ci,BAND,
                        Longitude,Latitude,
                                case  
                                        when LEFT(right(CellName,2),1) = 'D' then 30
                                        when LEFT(right(CellName,2),1) = 'G' then 20
                                        when LEFT(right(CellName,2),1) in ('W','Y','Z','X','B') then 60
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then 40
                                end as bw,
                                case  
                                        when LEFT(right(CellName,2),1) = 'D' then '1.75'
                                        when LEFT(right(CellName,2),1) = 'G' then '2'
                                        when LEFT(right(CellName,2),1) in ('W','Y','Z','X','B') then '1'
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then '1.5'
                                end as size,
                                case  
                                        when LEFT(right(CellName,2),1) = 'D' then 'DCS'
                                        when LEFT(right(CellName,2),1) = 'G' then 'GSM'
                                        when LEFT(right(CellName,2),1) in ('W','Y','Z','X','B') then '3G'
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then '4G'
                                end as kode,
                                case
                                        when LEFT(right(CellName,2),1) = 'D' then '#0000ff'
                                        when LEFT(right(CellName,2),1) = 'G' then '#00ff00'
                                        when LEFT(right(CellName,2),1) in ('W','Y','X','Z','B') then '#ff0000'
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then '#ffff66'
                                end as color
                        from GCELL_WJ20200205) bb
                    on aa.neighbor=bb.siteid";
                $data = \DB::connection("mysql222d")->select($query);
                $count_site = \DB::connection("mysql222d")->select($query)->count();
                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach ($data as $result) {
                    $results[$counter]['siteid'] = $result->SiteID;
                    $results[$counter]['cellname'] = $result->CellName;
                    $results[$counter]['direction'] = (float)$result->DIR;
                    $results[$counter]['beam'] = (float)$result->BEAM;
                    $results[$counter]['longitude'] = (float)$result->Longitude;
                    $results[$counter]['latitude'] = (float)$result->Latitude;;
                    $results[$counter]['x1'] = (float)$result->x1;
                    $results[$counter]['y1'] = (float)$result->y1;
                    $results[$counter]['x2'] = (float)$result->x2;
                    $results[$counter]['y2'] = (float)$result->y2;
                    $results[$counter]['color'] = $result->color;
                    $results[$counter]['kode'] = $result->kode;
                    $counter++;
                }
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
            case 'ajax-get-map-site-sector':
                $dt = $request->all();
                date_default_timezone_set("Asia/Jakarta");
                include("../../../../../function/fungsi-sql.php");
                $date = date('Y-m-d H:i:s');
                $minutes_curr = date('i');
                $minutes = $minutes_curr - ($minutes_curr % 15);
                $new1 = date('Y-m-d H');

                //$new_date = date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($date)));
                $new_date = $new1 . ":" . $minutes . ":00";
                if (!empty($_GET['siteid'])) {
                    $siteid = $_GET['siteid'];
                } else {
                    $siteid = 'BDG500';
                }
                $query = "select bb.*,aa.remark,
                        longitude - (0.0007 * size)*cos(RADIANS(90 - DIR - (bw/2))) as x1,
                        Latitude - (0.0007 * size)*sin(RADIANS(90 - DIR - (bw/2))) as y1,
                        longitude - (0.0007 * size)*cos(RADIANS(90 - DIR + (bw/2))) as x2,
                        Latitude - (0.0007 * size)*sin(RADIANS(90 - DIR + (bw/2))) as y2 from 
                    (SELECT
                        b.siteid as neighbor
                        , a.siteid as home
                        , 6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat))) AS DISTANCE,b.lng,b.lat,
                            if(b.siteid=a.siteid,'home','neighbor') as remark
                    FROM
                        (select * from gcell_sitelonglat where siteid='" . $siteid . "' ) a
                        INNER JOIN gcell_sitelonglat b
                    ON (a.regional = b.regional) WHERE  6371*ACOS(COS(RADIANS(a.lat))*COS(RADIANS(b.lat))*COS(RADIANS(a.lng)-RADIANS(b.lng))+SIN(RADIANS(a.lat))*SIN(RADIANS(b.lat)))<=4 
                    ORDER BY distance asc) aa
                    INNER JOIN
                    (select CellName,SiteID,DIR,BEAM,Concat(lac,'-',Ci) as lac_ci,BAND,
                        Longitude,Latitude,
                                case  
                                        when LEFT(right(CellName,2),1) = 'D' then 30
                                        when LEFT(right(CellName,2),1) = 'G' then 20
                                        when LEFT(right(CellName,2),1) in ('W','Y','Z','X','B') then 60
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then 40
                                end as bw,
                                case  
                                        when LEFT(right(CellName,2),1) = 'D' then '1.75'
                                        when LEFT(right(CellName,2),1) = 'G' then '2'
                                        when LEFT(right(CellName,2),1) in ('W','Y','Z','X','B') then '1'
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then '1.5'
                                end as size,
                                case  
                                        when LEFT(right(CellName,2),1) = 'D' then 'DCS'
                                        when LEFT(right(CellName,2),1) = 'G' then 'GSM'
                                        when LEFT(right(CellName,2),1) in ('W','Y','Z','X','B') then '3G'
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then '4G'
                                end as kode,
                                case
                                        when LEFT(right(CellName,2),1) = 'D' then '#0000ff'
                                        when LEFT(right(CellName,2),1) = 'G' then '#00ff00'
                                        when LEFT(right(CellName,2),1) in ('W','Y','X','Z','B') then '#ff0000'
                                        when LEFT(right(CellName,2),1) in ('L','E','F','T','R')  then '#ffff66'
                                end as color
                        from GCELL_WJ20200205) bb
                    on aa.neighbor=bb.siteid";
                $data = \DB::connection("mysql222d")->select($query);
                $count_site = \DB::connection("mysql222d")->select($query)->count();
                $counter = 0;
                $output = array();
                $results['new_date'] = $new_date;
                $results['count_site'] = $count_site;
                foreach ($data as $result) {
                    $results[$counter]['siteid'] = $result->SiteID;
                    $results[$counter]['cellname'] = $result->CellName;
                    $results[$counter]['direction'] = (float)$result->DIR;
                    $results[$counter]['beam'] = (float)$result->BEAM;
                    $results[$counter]['longitude'] = (float)$result->Longitude;
                    $results[$counter]['latitude'] = (float)$result->Latitude;;
                    $results[$counter]['x1'] = (float)$result->x1;
                    $results[$counter]['y1'] = (float)$result->y1;
                    $results[$counter]['x2'] = (float)$result->x2;
                    $results[$counter]['y2'] = (float)$result->y2;
                    $results[$counter]['color'] = $result->color;
                    $results[$counter]['kode'] = $result->kode;
                    $counter++;
                }
                //echo json_encode($results);
                return Core::setResponse("success",$results);
                break;
        }
    }

    public function transportperformsite(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'new_iubdrop_site_hourly':
                $start = $_REQUEST['tanggal_start'];
                $stop = $_REQUEST['tanggal_stop'];
                $siteid = $_REQUEST['siteid'];
                $sql_iubdrop = \DB::connection("mysql225")->select("select tanggal as mydate,object_name,sum(ul_drop) as ul_drop,sum(dl_drop) as dl_drop from hourly_monitoring_iubdrop where site_id='" . $siteid . "' and tanggal >='" . $start . " 00:00:00' and tanggal <='" . $stop . " 23:00:00' group by tanggal,object_name order by tanggal");    
                $output = array();
                //while ($data = mysqli_fetch_object($sql_iubdrop)) {
                foreach ($sql_iubdrop as $sql_iubdrop => $data) {
                    date_default_timezone_set("UTC");
                    $point['ul_drop'][] = strtotime($data->mydate) * 1000;
                    $point['ul_drop'][] = $data->dl_drop;
                    $point['dl_drop'][] = strtotime($data->mydate) * 1000;
                    $point['dl_drop'][] = $data->ul_drop;
                    $series[$data->object_name]['ul'][] = $point['ul_drop'];
                    $series[$data->object_name]['dl'][] = $point['dl_drop'];
                    $point = array();
                    $objectname[] = $data->object_name;
                    //$output[] = $data;
                }
                foreach (array_unique($objectname) as $nodename) {
                    $output_dl['name'] = $nodename . ' DL Drop';
                    $output_dl['data'] = $series[$nodename]['dl'];
                    $output_ul['name'] = $nodename . ' UL Drop';
                    $output_ul['data'] = $series[$nodename]['ul'];
                    $output_iub[] = $output_dl;
                    $output_iub[] = $output_ul;
                }
                //echo json_encode($output);
                $output_data['data'] = $output;
                //header("Content-type: application/json");
                $output['series'] = $output_iub;
                //$output['series'] = json_encode($output_iub, JSON_NUMERIC_CHECK);
                //echo json_encode($output);
                return Core::setResponse("success",$output);
                break;
            case 'packetloss-data':
                $start = $_REQUEST['tanggal_start'];
                $stop = $_REQUEST['tanggal_stop'];
                $siteid = $_REQUEST['siteid'];
                $tgl = substr(str_replace("-", "", $start), 0, 6);
                $table = "monitoring_ippm_" . $tgl;
                //ambil ani dan nama site nya dulu 🙂
                $sql_packetloss = \DB::connection("mysql144")->select("select concat(tanggal,' ',jam) as mydate, object as  rnc,a.ani,a.siteid as SITE_ID,a.sitename as SITE_NAME,a.packetloss as packet_loss,a.latency as latency
                            from sum_packetloss_hourly_3g a where concat(tanggal,' ',jam) >= '" . $start . " 00:00:00' and concat(tanggal,' ',jam) < '" . $stop . " 23:00:00' and siteid='" . $siteid . "' order by mydate");
                $output = array();
                $objectname = array();
                if ($sql_packetloss->count() > 0) {
                    foreach ($sql_packetloss as $sql_packetloss => $data) {
                    //while ($data = mysqli_fetch_object($sql_packetloss)) {
                        date_default_timezone_set("UTC");
                        $point['packet_loss'][] = strtotime($data->mydate) * 1000;
                        $point['latency'][] = strtotime($data->mydate) * 1000;
                        $point['packet_loss'][] = $data->packet_loss;
                        $point['latency'][] = $data->latency;
                        $object_name = $data->rnc . "_" . $data->SITE_ID . "_" . $data->SITE_NAME . "_packetloss";
                        $object_name_1 = $data->rnc . "_" . $data->SITE_ID . "_" . $data->SITE_NAME . "_Latency";
                        $series[$object_name]['packet_loss'][] = $point['packet_loss'];
                        $series_1[$object_name_1]['latency'][] = $point['latency'];
                        $point = array();
                        $objectname[] = $object_name;
                        $objectname_1[] = $object_name_1;
                        //$output[] = $data;
                    }
                }
                $output_iub = array();
                foreach (array_unique($objectname_1) as $nodename) {
                    $output_packet_loss['name'] = $nodename;
                    $output_packet_loss['data'] = $series_1[$nodename]['latency'];
                    $output_packet_loss['yAxis'] = "1";
                    $output_packet_loss['color'] = "#3C1874";
                    $output_iub[] = $output_packet_loss;
                }
                foreach (array_unique($objectname) as $nodename) {
                    $output_packet_loss['name'] = $nodename;
                    $output_packet_loss['data'] = $series[$nodename]['packet_loss'];
                    $output_packet_loss['yAxis'] = "0";
                    $output_packet_loss['color'] = "#ff3a22";
                    $output_iub[] = $output_packet_loss;
                }
                //echo json_encode($output);
                $output_data['data'] = $output;
                //header("Content-type: application/json");
                //$output['series'] = json_encode($output_iub, JSON_NUMERIC_CHECK); 
                $output['series'] = $output_iub;
                //echo json_encode($output);
                return Core::setResponse("success",$output);
                break;
            case 'packetlostratio-4g':
                $start = $_REQUEST['tanggal_start'];
                $stop = $_REQUEST['tanggal_stop'];
                $siteid = $_REQUEST['siteid'];
                $tgl = substr(str_replace("-", "", $start), 0, 6);
                $table = "raw_twamp_netsense_hourly";
                $table2 = "_old_raw_twamp_netsense_hourly";

                $sql_packetloss = \DB::connection("mysql144")->select("select concat(tanggal,' ',jam) as mydate,'packetloss' as tipedata,source_device_name,target_device_name,siteid,avg_two_way_packet_loss_ratio_percent as packet_loss,avg_two_way_delay_us as latency,avg_two_way_jitter_us as jitter
                    from $table where tanggal >='" . $start . "' and tanggal <='" . $stop . "' and siteid='" . $siteid . "'
                    order BY mydate ASC
                    ");
                $output = array();
                $objectname = array();
                if ($sql_packetloss->count() > 0) {
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
                }
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
                $output_data['data'] = $output;
                header("Content-type: application/json");
                //$output['series'] = json_encode($output_iub, JSON_NUMERIC_CHECK);
                $output['series'] = $output_iub;
                //echo json_encode($output);
                return Core::setResponse("success",$output);
                break;
            case 'ajax-radio-performance':
                $query = \DB::connection("mysql144")->select("SELECT CONCAT(tanggal,' ',jam) AS mydate, CONCAT(tipe,'-',NEName) AS object_name,SiteID, REPLACE(SUBSTRING(SUBSTRING_INDEX(NEName, '_', 2),LENGTH(SUBSTRING_INDEX(NEName, '_', 2 - 1)) + 1),'_', '') AS neid,
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
                
                    $output = array();
                    $objectname = array();
                    if ($query->count() > 0) {
                        while ($data = mysqli_fetch_object($sql)) {
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
                    }
                    $xaxis_per = array_unique($xaxis_perjuangan);
                    $output_data_array = array();
                    foreach (array_unique($objectname) as $nodename) {
                        $objnya[] = $nodename;
                        $output_data['name'] = $nodename;
                        $output_data['data'] = $series[$nodename]['parameter'];
                        $output_data_array[] = $output_data;
                    }
                    array_unique($objnya);
                    $cl = ['#007CC7', '#ff3a22', '#3C1874',  '#ff8928'];
                    $i = 0;
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
                        $out_baru['color'] = $cl[$i];
                        $out_baru_array[] = $out_baru;
                        $i++;
                    }
                    //            $output['uniq'] = $objnya;
                    //            $output['uniq_time'] = $xaxis;
                    //            $output['uniq_baru'] = json_encode($out_baru_array, JSON_NUMERIC_CHECK);
                    //echo json_encode($output);
                    $output_data['data'] = $output;
                    header("Content-type: application/json");
                    //$output['series'] = json_encode($output_data_array, JSON_NUMERIC_CHECK);
                    $output['test'] = $xaxis_per;
                    $output['series'] = json_encode($out_baru_array, JSON_NUMERIC_CHECK);
                    $output['chart_title'] = $title;
                    $output['chart_axis'] = $axistitle;
                    $output['query'] = $query;
                    $output['error'] = "";
                    $output['status'] = true;
                //echo json_encode($output);
                $output = \DB::connection("mysql144")->select("
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function transporttransusage(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'get-lastdata-transport-usage':
                date_default_timezone_set("Asia/Jakarta");
                //$query = "SELECT tahun,MAX(minggu) as last_week FROM sum_bw_usgidx_weekly WHERE region = 'jabar' and tahun = (select max(tahun) from sum_bw_usgidx_weekly where region = 'jabar')";
                $doquery = \DB::connection("mysql170")->select("SELECT MAX(t.minggu) as last_week,tahun FROM 
                    (SELECT MAX(tahun) AS tahun, minggu FROM monitoring_trans_usage_weekly GROUP BY tahun,minggu)t
                    GROUP BY tahun,t.minggu");
                $counter = 1;
                foreach ($doquery as $doquery => $data) {
                //while($data = mysqli_fetch_object($doquery)){
                    $ada = array();
                    $year = $data->tahun; 
                    $lastdata = $data->last_week; 
                }
                return Core::setResponse("success",$output);
                break;
            case 'ajax-trans-usage-region':
                date_default_timezone_set("Asia/Jakarta");
                if($type == 'Bandwidth Usage 2G'){$option = 'UsageABIS';$warna='#68D9FF';}
                else if($type == 'Bandwidth Usage 3G'){$option = 'UsageIUB';$warna='#5BEC9D';}
                else if($type == 'Bandwidth Usage 4G'){$option = 'UsageTRP';$warna='#EE3851';}
                else{$option = 'UsageTRP';$warna='#ff5200';}
                $year = $_GET['yearusage'];
                $week = $_GET['weekusage'];
                
                if(!empty($year)){ $year = $year;}
                else{$year = "(SELECT MAX(tahun) FROM monitoring_trans_usage_weekly)";}
                //echo $year;
                if(!empty($week)){ $week = $week;}
                else{$week = "(SELECT MAX(minggu) FROM monitoring_trans_usage_weekly WHERE tahun = (SELECT MAX(tahun) FROM monitoring_trans_usage_weekly))";}
                $query = "SELECT tahun,minggu,siteid,avg_value as value 
                FROM monitoring_trans_usage_weekly
                WHERE trans_type = '".$option."' AND tahun = ".$year." 
                and minggu = ".$week."  ";
                $doquery = \DB::connection("mysql170")->select($query);
                $counter = 1;
                $siteid[] = "";
                //while($data = mysqli_fetch_object($doquery)){
                foreach ($doquery as $doquery => $data) {
                    $ada = array();
                    $ada[] =  $counter;
                    $tmp_val2[] = $data->value; 
                    $tmp_val = $data->value; 
                    $value = round(($tmp_val), 2);
                    $ada[] = $value;
                    $ada[] = $data->siteid;
                    $siteid[] = $data->siteid;
                    $week = $data->minggu;
                    $year = $data->tahun;
                    $kategori[] = $counter;
                    $output[$counter] = $ada;
                    $outputfin[] = $output[$counter];
                    $counter++;
                }
            
                $output3['series'][0]['name'] = $type." Week : $week Tahun : $year";
                $output3['series'][0]['type'] = 'scatter';
                $output3['series'][0]['color'] = $warna;
                $output3['series'][0]['marker']['radius'] = 2;
                $output3['series'][0]['data'] = $outputfin;
                $output3['series'][0]['siteid'] = $siteid ;
                $output3['series'][0]['category'] = $kategori ;
                $output3['series'][0]['query'] = $query;
                $output3['series'][0]['week'] = $_GET['weekusage'];
                mysqli_close($link);

            // echo json_encode($output3,JSON_NUMERIC_CHECK);   
                echo json_encode($output3,JSON_NUMERIC_CHECK); 	
                unset($ada);
                unset($output3);
                return Core::setResponse("success",$output3);
                break;
        }
    }

    public function powermpbadmin(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'submit':
                $dt = $request->all();
                if ($_POST['id_mbp'] == '' && $_POST['submit'] == "Save changes") {
                    $query = \DB::connection("mysql225b")->select("insert into mbp (nsa,cluster,rtp) values(
                              '" . $_POST['nsa'] . "',
                              '" . $_POST['cluster'] . "',
                              '" . $_POST['rtp'] . "'
                             )");
                    $msg = "Data MBP telah ditambahkan" . mysqli_error($link);
                  } elseif ($_POST['id_mbp'] <> '' && $_POST['submit'] == "Save changes") {
                    $query = \DB::connection("mysql225b")->select("update mbp set 
                              nsa='" . $_POST['nsa'] . "',
                              cluster='" . $_POST['cluster'] . "',
                              rtp='" . $_POST['rtp'] . "',
                              type_mbp='" . $_POST['type_mbp'] . "',
                              mitra_mbp='" . $_POST['mitra_mbp'] . "',
                              status_mbp='" . $_POST['status_mbp'] . "',
                              operator='" . $_POST['operator'] . "',
                              backup_site='" . $_POST['backup_site'] . "',
                              start_backup='" . $_POST['start_backup'] . "',
                              stop_backup='" . $_POST['stop_backup'] . "'
                             where id_mbp='" . $_POST['id_mbp'] . "'
                             ");
                    $msg = "Data telah diubah";
                  } elseif ($_POST['id_mbp'] <> '' && $_POST['submit'] == "Delete") {
                    $query = \DB::connection("mysql225b")->select("delete from mbp where id_mbp ='" . $_POST['id_mbp'] . "'
                             ");
                    $msg = "Data telah dihapus";
                  }
                return Core::setResponse("success",$msg);
                break;
            case 'select':
                $output = \DB::connection("mysql225b")->select("select * from 85152_power.mbp
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function powermpbdashboard(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'submit':
                $dt = $request->all();
                if ($_POST['id'] == '' && $_POST['submit'] == "Save changes") {
                    $query = "insert into mbp (nsa,cluster,rtp) values(
                              '" . $_POST['nsa'] . "',
                              '" . $_POST['cluster'] . "',
                              '" . $_POST['rtp'] . "'
                             )";
                    $msg = "Data MBP telah ditambahkan" . mysqli_error($link);
                  } elseif ($_POST['id'] <> '' && $_POST['submit'] == "Update") {
                    $query = "update mbp set 
                              type_mbp='" . $_POST['type_mbp'] . "',
                              mitra_mbp='" . $_POST['mitra_mbp'] . "',
                              status_mbp='" . $_POST['status_mbp'] . "',
                              backup_site='" . $_POST['backup_site'] . "',
                              last_update = now(),
                              update_by = '" . $_SESSION[$sessionname]->username . "'
                             where id_mbp='" . $_POST['id'] . "'
                             ";
                    $msg = "Data telah diubah" . $query;
                    $action = mysqli_query($link, $query);
                    if ($action) {
                      echo status("data berhasil diubah");
                      echo redirect('rpa/power/mbpdashboard');
                      return;
                    }
                  }
                return Core::setResponse("success",$msg);
                break;
            case 'select':
                $output = \DB::connection("mysql225b")->select("select *,count(*) as total  from 85152_power.mbp group by cluster order by nsa,cluster
                ");
                return Core::setResponse("success",$output);
                break;
            case 'idle':
                $output = \DB::connection("mysql145")->select("Select count(*) jumlah from 85152_power.mbp where cluster = '" . $val->cluster . "' and status_mbp = 'idle'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'otw':
                $output = \DB::connection("mysql145")->select("Select count(*) jumlah from 85152_power.mbp where cluster = '" . $val->cluster . "' and status_mbp = 'otw'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'backup':
                $output = \DB::connection("mysql145")->select("Select count(*) jumlah from 85152_power.mbp where cluster = '" . $val->cluster . "' and status_mbp = 'backup'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query1':
                $output = \DB::connection("mysql145")->select("select * from 85152_power.mbp where cluster = '" . $_GET['id_mbp'] . "'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql145")->select("select *,count(*) as total  from 85152_power.mbp group by cluster
                ");
                return Core::setResponse("success",$output);
                break;
            case 'idle2':
                $output = \DB::connection("mysql145")->select("Select count(*) jumlah from 85152_power.mbp where cluster = '" . $val->cluster . "' and status_mbp = 'idle'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'otw2':
                $output = \DB::connection("mysql145")->select("Select count(*) jumlah from 85152_power.mbp where cluster = '" . $val->cluster . "' and status_mbp = 'otw'
                ");
                return Core::setResponse("success",$output);
                break;
            case 'backup2':
                $output = \DB::connection("mysql145")->select("Select count(*) jumlah from 85152_power.mbp where cluster = '" . $val->cluster . "' and status_mbp = 'backup'
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function powermpbdapotjabar(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql222")->select("SELECT * FROM dapot_power_pln_genset a JOIN site b ON (a.id_site = b.id_site)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222")->select("SELECT *,
                    (SELECT COUNT(*) FROM detail_nsa b1 WHERE b1.id_nsa = c.id_nsa) AS count_nsa,
                    (SELECT SUM(a1.jumlah) FROM dapot_power_fixed_genset a1 JOIN detail_nsa b1 ON (a1.id_detail_nsa = b1.id_detail_nsa) WHERE b1.id_nsa = c.id_nsa) AS tot_nsa
                    FROM dapot_power_fixed_genset a 
                    JOIN detail_nsa b ON (a.id_detail_nsa = b.id_detail_nsa)
                    JOIN nsa c ON (b.id_nsa = c.id_nsa)
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql222")->select("SELECT * FROM vendor a JOIN dapot_recitifier_battery b ON (a.id_vendor = b.id_vendor)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql222")->select("SELECT * FROM nsa a JOIN dapot_power_mobile_genset b ON (a.id_nsa = b.id_nsa)
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function powermpbdapotsoetta(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql222")->select("SELECT *
                    FROM dapot_power_panel_acpdb a
                    JOIN detail_dapot_power_panel_acpdb b ON (a.id_dapot_power_panel_acpdb = b.id_dapot_power_panel_acpdb)
                    ");
                return Core::setResponse("success",$output);
                break;
        }
    }

    public function powermpbdapotdago(Request $request)
    {
        $dt = $request->all();
        $mode = $dt['mode'];

        switch ($mode) {
            case 'query1':
                $output = \DB::connection("mysql222")->select("SELECT *
                    FROM dapot_power_dc_distribution_connection_ttc
                    WHERE category = 'rack_19'
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query2':
                $output = \DB::connection("mysql222")->select("SELECT *
                    FROM dapot_power_dc_distribution_connection_ttc
                    WHERE category = 'label'
                    ");
                return Core::setResponse("success",$output);
                break;
            case 'query3':
                $output = \DB::connection("mysql222")->select("SELECT * FROM vendor a JOIN dapot_recitifier_battery b ON (a.id_vendor = b.id_vendor)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'query4':
                $output = \DB::connection("mysql222")->select("SELECT * FROM nsa a JOIN dapot_power_mobile_genset b ON (a.id_nsa = b.id_nsa)
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
            case 'container_usage1':
                $output = \DB::connection("mysql145")->select("
                ");
                return Core::setResponse("success",$output);
                break;
        }
    }
}
