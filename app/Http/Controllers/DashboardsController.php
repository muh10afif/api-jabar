<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardsController extends Controller
{
    public function scr_mss()
    {
        $query_scr = \DB::connection('mysql89')->select("(SELECT *,'scr' AS remark FROM scr_mss ORDER BY date_id DESC LIMIT 4) ORDER BY time_id ASC;");

        $query_mss = \DB::connection('mysql89')->select("(SELECT date_id,time_id,mss_name,ROUND (ccr,2) AS ccr, 'ccr' AS remark FROM ccr_mss ORDER BY date_id DESC LIMIT 4) ORDER BY time_id ASC;");

        $data_scr = array();
        foreach ($query_scr as $key => $value) {
            $data_scr[] = $value;
        }

        $data_mss = array();
        foreach ($query_mss as $key2 => $value2) {
            $data_mss[] = $value2;
        }

	    $output['value_scr'] = $data_scr;
        $output['value_ccr'] = $data_mss;

        if (count($query_scr) == 0 && count($query_mss) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        return Core::setResponse("success", $output);
    }

    public function pdp_sr()
    {
        $query_pdp2g = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_2g' AS type_pdp, FORMAT(gb_mode_pdp_context_activation_sr,2) AS value_pdp FROM monitoring_sgsn_2g WHERE mydate = (SELECT MAX(mydate) FROM monitoring_sgsn_2g WHERE mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')) AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10');");

        $query_pdp3g = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_3g' AS type_pdp, FORMAT(iu_mode_pdp_context_activation_sr,2) AS value_pdp FROM monitoring_sgsn_3g WHERE mydate = (SELECT MAX(mydate) FROM monitoring_sgsn_3g WHERE mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')) AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10');");

        $query_pdp4g = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_4g' AS type_pdp, FORMAT(combined_attach_sr,2) AS value_pdp FROM monitoring_sgsn_4g WHERE mydate = (SELECT MAX(mydate) FROM monitoring_sgsn_4g WHERE  mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')) AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10');
        ");

        if (count($query_pdp2g) == 0 && count($query_pdp3g) == 0 && count($query_pdp4g) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        $output = array();

        foreach ($query_pdp2g as $key => $value2g) {
            $data_pdp2g[] = $value2g;
        }

        foreach ($query_pdp3g as $key => $value3g) {
            $data_pdp3g[] = $value3g;
        }

        foreach ($query_pdp4g as $key => $value4g) {
            $data_pdp4g[] = $value4g;
        }

        $output['value_pdp2g'] = $data_pdp2g;
        $output['value_pdp3g'] = $data_pdp3g;
        $output['value_pdp4g'] = $data_pdp4g;

        return Core::setResponse("success", $output);
    }

    public function scr_ccr_graph(Request $request)
    {
        ini_set('max_execution_time', 600);

        $mode = $request->mode;

        if ($mode == '') {
            return Core::setResponse("error", ['mode' => 'Parameter mode tidak boleh kosong.']);
        }

        switch($mode){

            case 'scr':
                $query_pdp_sr = \DB::connection('mysql89')->select("SELECT concat(date_id,' ',time_id) as mydate,mss_name as object_name,'scr' as type_pdp,round (scr,2) as value_pdp from scr_mss where concat(date_id,' ',time_id) > (DATE_SUB(NOW(), INTERVAL 24 HOUR)) and scr > 0 order by date_id,time_id,mss_name");

                $output['title'] = 'SCR Trend';
                break;

            case 'ccr':
                $query_pdp_sr = \DB::connection('mysql89')->select("SELECT concat(date_id,' ',time_id) as mydate,mss_name as object_name,'ccr' as type_pdp,round (ccr,2) as value_pdp from ccr_mss where concat(date_id,' ',time_id) > (DATE_SUB(NOW(), INTERVAL 24 HOUR)) and ccr > 0 order by date_id,time_id,mss_name");

                $output['title'] = 'CCR Trend';
                break;
            default:
                return Core::setResponse("not_found", ['mode' => 'Parameter mode tidak ditemukan.']);

        }

        if (count($query_pdp_sr) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        $output     = array();
        $point      = array();
        $output_pdp = array();
        $series     = array();

        foreach ($query_pdp_sr as $key => $result) {
            $the_date = ($result->mydate);
            $mydate[] = $result->mydate;

            $point[$result->object_name][]      = strtotime($the_date)*1000;
            $point[$result->object_name][]      = (float)$result->value_pdp;

            $series1[$result->object_name][]    = $point[$result->object_name];
            $series[$result->object_name]       = $series1[$result->object_name];

            $objectname[] = $result->object_name;
            $point = array();
        }

        foreach(array_unique($mydate) as $xas){
            $xaxis[]=$xas;
        };

        $colr = ['#007CC7', '#ff3a22', '#3C1874',  '#ff8928', '#46211A', '#A2C523' ,
        '#2E4600', '#d2601a', '#021C1E', '#6FB98F' ,'#8D230F','#F18D9E', '#5D535E',
        '#5BC8AC','#9B4F0F', '#F1F1F2', '#011A27', '#E6DF44', '#2E2300', '#F52549'];
        $i=0;

        foreach(array_unique($objectname) as $sgsn){

            $output_pdp['name']     = $sgsn;
            $output_pdp['data']     = $series[$sgsn];
            $output_pdp['color']    = $colr[$i];

            $output_sgsn[] = $output_pdp;
            $i++;
        }

        $output['series'] = ($series);
        $output['series'] = json_encode($output_sgsn, JSON_NUMERIC_CHECK);

        return Core::setResponse("success", $output);
    }

    public function pdp_sr_graph(Request $request)
    {
        $mode = $request->mode;

        if ($mode == '') {
            return Core::setResponse("error", ['mode' => 'Parameter mode tidak boleh kosong.']);
        }

        switch($mode){
            case '3G':
                $query_pdp_sr = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_3g' AS type_pdp, FORMAT(iu_mode_pdp_context_activation_sr,2) AS value_pdp FROM monitoring_sgsn_3g WHERE mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')");

                $output['title'] = 'KPI 3G PDP Context SR';
                break;
            case '4G-lama':
                $query_pdp_sr = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_4g' AS type_pdp, FORMAT(pdn_connectivity_sr,2) AS value_pdp FROM monitoring_sgsn_4g WHERE mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')");

                $output['title'] = 'KPI 4G PDP Context SR';
                break;
            case '4G':
                $query_pdp_sr = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_4g' AS type_pdp, FORMAT(combined_attach_sr,2) AS value_pdp FROM monitoring_sgsn_4g WHERE mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')");

                $output['title'] = 'KPI 4G PDP Context SR';
                break;
            case '2G':
                $query_pdp_sr = \DB::connection("mysql225")->select("SELECT mydate,object_name,'pdp_2g' AS type_pdp, FORMAT(gb_mode_pdp_context_activation_sr,2) AS value_pdp FROM monitoring_sgsn_2g WHERE mydate >= NOW() - INTERVAL 12 HOUR AND object_name IN ('SGBDG5','SGBDG6','SGBDG7','SGBDG8','SGBDG9','vMMEBDG10')");

                $output['title'] = 'KPI 2G PDP Context SR';
                break;
            default:
                return Core::setResponse("not_found", ['mode' => 'Parameter mode tidak ditemukan. Pilih: 2G, 3G, 4G']);
        }

        if (count($query_pdp_sr) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        $output     = array();
        $point      = array();
        $output_pdp = array();
        $series     = array();

        foreach ($query_pdp_sr as $key => $result) {
            $the_date = ($result->mydate);
            $mydate[] = $result->mydate;

            $point[$result->object_name][] = strtotime($the_date)*1000;
            $point[$result->object_name][] = (float)$result->value_pdp;

            $series1[$result->object_name][]    = $point[$result->object_name];
            $series[$result->object_name]       = $series1[$result->object_name];

            $objectname[] = $result->object_name;
            $point = array();
        }

        foreach(array_unique($mydate) as $xas){
            $xaxis[]=$xas;
        };

        $colr = ['#007CC7', '#ff3a22', '#3C1874',  '#ff8928', '#46211A', '#A2C523' ,
        '#2E4600', '#d2601a', '#021C1E', '#6FB98F' ,'#8D230F','#F18D9E', '#5D535E',
        '#5BC8AC','#9B4F0F', '#F1F1F2', '#011A27', '#E6DF44', '#2E2300', '#F52549'];

        $i=0;
        foreach(array_unique($objectname) as $sgsn){
            $output_pdp['name']  = $sgsn;
            $output_pdp['data']  = $series[$sgsn];
            $output_pdp['color'] = $colr[$i];

            $output_sgsn[] = $output_pdp;
            $i++;
        }

        $output['series'] = ($series);
        $output['series'] = json_encode($output_sgsn, JSON_NUMERIC_CHECK);

        return Core::setResponse("success", $output);
    }

    public function ggsn(Request $request)
    {
        $mode = $request->mode;
        $ggsn = $request->ggsn;
        $apn  = $request->apn;
        $area = $request->area;

        $output = array();

        if ($mode == '') {
            return Core::setResponse("error", ['mode' => 'Parameter mode tidak boleh kosong.']);
        }
        if ($area == '') {
            return Core::setResponse("error", ['area' => 'Parameter area tidak boleh kosong.']);
        }

        if (!empty($area)) {

            if ($area == 'jabo') {
                $where = " and (ggsn_name like '%TBS%' or ggsn_name like '%BRN%')";
                $areanya = "Jabo";
            } elseif ($area == 'jabar') {
                $where = " and (ggsn_name like '%DGO%' or ggsn_name like '%SOE%')";
                $areanya = "Jabar";
            } elseif ($area == 'all') {
                $where = "";
                $areanya = "Area 2";
            }
        } else {
            $where = " and (ggsn_name like '%DGO%' or ggsn_name like '%SOE%')";
            $areanya = "Jabar";
        }

        switch ($mode) {
            case 'pdp_context':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, pdp_context as y_value FROM `ggsn_pdp` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = 'PDP Connection Per GGSN';
                $output['yaxisVal'] = 'Count';
                break;
            case 'pdp_activebearer':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, pdp_activebearer as y_value FROM `ggsn_pdp` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = 'Bearers Connection Per GGSN';
                $output['yaxisVal'] = 'Count';
                break;
            case 'pdp_bearer':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, pdp_activebearer+pdp_context as y_value FROM `ggsn_pdp` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = 'Total PDP & Bearers Connection';
                $output['yaxisVal'] = 'Count';
                break;
            case 'tot_thp':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,ggsn_name, round(sum(y_value),2) as y_value from (SELECT mydate, 'Total Throughput' as ggsn_name, thp_tot as y_value FROM `ggsn_thp_new` where mydate >= date(now()) - interval 7 day and (ggsn_name like '%DGO%' or ggsn_name like '%SOE%') AND ggsn_name NOT LIKE '%SOE3%' AND ggsn_name NOT LIKE '%DGO3%' union
                SELECT mydate, 'Throughput ipv4' as ggsn_name, thp_tot_ipv4 as y_value
                FROM `ggsn_thp_new`
                where mydate >= date(now()) - interval 7 day $where
                union
                SELECT mydate, 'Throughput ipv6' as ggsn_name,thp_tot_ipv6 as y_value
                FROM `ggsn_thp_new`
                where mydate >= date(now()) - interval 7 day $where
                ) a
                group by mydate,ggsn_name
                HAVING
                COUNT(*) > 3");

                $output['title'] = 'Total Throughput (Gbps)';
                $output['yaxisVal'] = 'Gbps';
                break;
            case 'tot_thp_gbps':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, 'Total Throughput' AS ggsn_name, ROUND(SUM(CASE
                WHEN ggsn_name LIKE '%DGO1' THEN  IF(thp_tot>185,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%DGO2' THEN  IF(thp_tot>185,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%DGO3' THEN  IF(thp_tot>185,ROUND(thp_tot/1000,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%SOE1' THEN  IF(thp_tot>145,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%SOE2' THEN  IF(thp_tot>145,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%SOE3' THEN  IF(thp_tot>145,ROUND(thp_tot/1000,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN5' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN6' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN8' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN9' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN10' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'vGGBRN11' THEN IF(thp_tot>150,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS6' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS8' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS9' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS10' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS11' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'vGGTBS12' THEN IF(thp_tot>150,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                END) ,2) y_value
                FROM `ggsn_thp_new`
                WHERE mydate >= DATE(NOW()) - INTERVAL 7 DAY $where
                GROUP BY mydate");

                $output['title']    = 'Total Throughput SPGW (Gbps)';
                $output['yaxisVal'] = 'Gbps';

                break;
            case 'tot_thp_util':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, 'Total Utilization' AS ggsn_name, ROUND(SUM(CASE
                WHEN ggsn_name LIKE '%DGO1' THEN  IF(thp_tot>185,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%DGO2' THEN  IF(thp_tot>185,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%DGO3' THEN  IF(thp_tot>185,ROUND(thp_tot/1000,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%SOE1' THEN  IF(thp_tot>145,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%SOE2' THEN  IF(thp_tot>145,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name LIKE '%SOE3' THEN  IF(thp_tot>145,ROUND(thp_tot/1000,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN5' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN6' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN8' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN9' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGBRN10' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'vGGBRN11' THEN IF(thp_tot>150,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS6' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS8' THEN IF(thp_tot>70,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS9' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS10' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'GGTBS11' THEN IF(thp_tot>120,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                WHEN ggsn_name = 'vGGTBS12' THEN IF(thp_tot>150,ROUND(thp_tot/2,2),ROUND(thp_tot,2))
                END) / 940 * 100 , 2) y_value
                FROM `ggsn_thp_new`
                WHERE mydate >= DATE(NOW()) - INTERVAL 7 DAY $where
                GROUP BY mydate");

                $output['title']    = 'Total Utilization SPGW Jabar (%)';
                $output['yaxisVal'] = '%';

                break;
            case 'thp_4g':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, round(thp_4g/1000,2) as y_value FROM `ggsn_thp_payload` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = '4G Throughput (Gbps)';
                $output['yaxisVal'] = 'Gbps';
                break;
            case 'thp_ipv4':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, round(thp_tot_ipv4,2) as y_value FROM `ggsn_thp_new` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = 'IPV4 Throughput (Gbps)';
                $output['yaxisVal'] = 'Gbps';
                break;
            case 'thp_ipv6':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, round(thp_tot_ipv6,2) as y_value FROM `ggsn_thp_new` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = 'IPV6 Throughput (Gbps)';
                $output['yaxisVal'] = 'Gbps';
                break;
            case 'occ_all':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, CASE
                            WHEN ggsn_name like '%DGO1' THEN if(thp_tot/165*100>100,
                            thp_tot/160*100/2,if(mydate>'2021-06-29',thp_tot/160*100,thp_tot/180*100))
                            WHEN ggsn_name like '%DGO2' THEN if(thp_tot/165*100>100,
                            thp_tot/160*100/2,if(mydate>'2021-06-29',thp_tot/160*100,thp_tot/180*100))
                WHEN ggsn_name LIKE '%DGO3' THEN (thp_tot/1000)/120*100
                            WHEN ggsn_name like '%SOE1' THEN if(if(mydate>'2021-06-29',thp_tot/165*100,thp_tot/65*100)>100,if(mydate>'2021-06-29',thp_tot/160*100,thp_tot/60*100)/2,if(mydate>'2021-06-29',thp_tot/160*100,thp_tot/60*100))
                            WHEN ggsn_name like '%SOE2' THEN if(if(mydate>'2021-06-29',thp_tot/165*100,thp_tot/180*100)>100,if(mydate>'2021-06-29',thp_tot/160*100,thp_tot/180*100)/2,if(mydate>'2021-06-29',thp_tot/160*100,thp_tot/180*100))
                            WHEN ggsn_name like '%SOE3' THEN if(if(mydate>'2021-06-29',round(thp_tot/1000,2)/180*100,round(thp_tot/1000,2)/180*100)>100,if(mydate>'2021-06-29',round(thp_tot/1000,2)/180*100,round(thp_tot/1000,2)/180*100)/2,if(mydate>'2021-06-29',round(thp_tot/1000,2)/180*100,round(thp_tot/1000,2)/180*100))
                            WHEN ggsn_name = 'GGBRN5' THEN if(thp_tot/70*100>100,thp_tot/70*100/2,thp_tot/70*100)
                            WHEN ggsn_name = 'GGBRN6' THEN if(thp_tot/70*100>100,thp_tot/70*100/2,thp_tot/70*100)
                            WHEN ggsn_name = 'GGBRN8' THEN if(thp_tot/120*100>100,thp_tot/120*100/2,thp_tot/120*100)
                            WHEN ggsn_name = 'GGBRN9' THEN if(thp_tot/120*100>100,thp_tot/120*100/2,thp_tot/120*100)
                            WHEN ggsn_name = 'GGBRN10' THEN if(thp_tot/120*100>100,thp_tot/120*100/2,thp_tot/120*100)
                            WHEN ggsn_name = 'vGGBRN11' THEN if(thp_tot/150*100>100,thp_tot/150*100/2,thp_tot/150*100)
                            WHEN ggsn_name = 'GGTBS6' THEN if(thp_tot/70*100>100,thp_tot/70*100/2,thp_tot/70*100)
                            WHEN ggsn_name = 'GGTBS8' THEN if(thp_tot/70*100>100,thp_tot/70*100/2,thp_tot/70*100)
                            WHEN ggsn_name = 'GGTBS9' THEN if(thp_tot/120*100>100,thp_tot/120*100/2,thp_tot/120*100)
                            WHEN ggsn_name = 'GGTBS10' THEN if(thp_tot/120*100>100,thp_tot/120*100/2,thp_tot/120*100)
                            WHEN ggsn_name = 'GGTBS11' THEN if(thp_tot/120*100>100,thp_tot/120*100/2,thp_tot/120*100)
                            WHEN ggsn_name = 'vGGTBS11' THEN if(thp_tot/150*100>100,thp_tot/150*100/2,thp_tot/150*100)
                            END as y_value
                            FROM `ggsn_thp_new`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title']    = 'GGSN Utilization (%)';
                $output['yaxisVal'] = '%';
                break;

            case 'thp_only':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, CASE
                            WHEN ggsn_name like '%DGO1' THEN  if(thp_tot>185,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name like '%DGO2' THEN  if(thp_tot>185,round(thp_tot/2,2),round(thp_tot,2))
                                WHEN ggsn_name LIKE '%DGO3' THEN  IF(thp_tot>185,ROUND(thp_tot/1000,2),ROUND(thp_tot,2))
                            WHEN ggsn_name like '%SOE1' THEN  if(thp_tot>145,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name like '%SOE2' THEN  if(thp_tot>145,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name like '%SOE3' THEN  if(thp_tot>145,round(thp_tot/1000,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGBRN5' THEN if(thp_tot>70,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGBRN6' THEN if(thp_tot>70,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGBRN8' THEN if(thp_tot>120,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGBRN9' THEN if(thp_tot>120,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGBRN10' THEN if(thp_tot>120,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'vGGBRN11' THEN if(thp_tot>150,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGTBS6' THEN if(thp_tot>70,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGTBS8' THEN if(thp_tot>70,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGTBS9' THEN if(thp_tot>120,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGTBS10' THEN if(thp_tot>120,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'GGTBS11' THEN if(thp_tot>120,round(thp_tot/2,2),round(thp_tot,2))
                            WHEN ggsn_name = 'vGGTBS12' THEN if(thp_tot>150,round(thp_tot/2,2),round(thp_tot,2))
                            END as y_value
                            FROM `ggsn_thp_new`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title']    = 'GGSN Throughput';
                $output['yaxisVal'] = 'Gbps';
                break;
            case 'thp_2g3g':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, thp_2g3g as y_value
                            FROM `ggsn_thp_payload`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title'] = '2G 3G Throughput (Mbps)';
                $output['yaxisVal'] = 'Mbps';
                break;
            case 'tot_payload_all':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,ggsn_name, sum(y_value) as y_value from (
                    SELECT mydate, 'Total Payload' as ggsn_name, payload_4g+payload_2g3g as y_value
                    FROM `ggsn_thp_payload`
                    where mydate >= date(now()) - interval 7 day $where
                    union
                    SELECT mydate, 'Payload 4g' as ggsn_name, payload_4g as y_value
                    FROM `ggsn_thp_payload`
                    where mydate >= date(now()) - interval 7 day $where
                    union
                    SELECT mydate, 'Payload 2g3g' as ggsn_name,payload_2g3g as y_value
                    FROM `ggsn_thp_payload`
                    where mydate >= date(now()) - interval 7 day $where
                    ) a
                    group by mydate,ggsn_name");

                $output['title']    = 'Total Payload GGSN ' . $areanya;
                $output['yaxisVal'] = 'Mbit';
                break;
            case 'tot_payload_per_ggsn':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, payload_4g+payload_2g3g as y_value FROM `ggsn_thp_payload` where mydate >= date(now()) - interval 7 day $where group by mydate, ggsn_name");

                $output['title']    = 'Total Payload ';
                $output['yaxisVal'] = 'Mbit';
                break;
            case 'payload_4g':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, payload_4g as y_value
                            FROM `ggsn_thp_payload`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title'] = 'Total Payload 4G';
                $output['yaxisVal'] = 'Mbit';
                break;
            case 'payload_2g3g':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, payload_2g3g as y_value
                                                FROM `ggsn_thp_payload`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title'] = 'Total Payload 2G';
                $output['yaxisVal'] = 'Mbit';
                break;
            case 'gxsr':
                $query_pdp = \DB::connection("mysql170")->select(" SELECT mydate, ggsn_name, gx_trans_sr as y_value
                            FROM `ggsn_gxsr`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title'] = 'Gx Succes Rate';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'gysr':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, gy_trans_sr as y_value
                            FROM `ggsn_gysr`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title'] = 'Gy Succes Rate';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'cpuload':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, MAX(avg_cpu) as y_value
                                                FROM `ggsn_cpuload`
                            where mydate >= date(now()) - interval 7 day $where
                            group by mydate, ggsn_name");

                $output['title'] = 'Average CPU Load';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'peakcpuload':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate, ggsn_name, MAX(peak_cpu) as y_value
                                        FROM `ggsn_cpuload`
                    where mydate >= date(now()) - interval 7 day $where
                    group by mydate, ggsn_name");

                $output['title'] = 'Peak CPU Load';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'cpuloadboard':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,entry_board as ggsn_name,avg_cpu as y_value
                            FROM `ggsn_cpuload`
                            where mydate >= date(now()) - interval 7 day and ggsn_name = '$ggsn'
                            group by mydate, ggsn_name, entry_board");

                $output['title'] = 'Average Cpu Load per board';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'pdp_sr':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,apn as ggsn_name,pdp_sr as y_value
                            FROM `ggsn_pdpsr`
                            where mydate >= date(now()) - interval 7 day and ggsn_name = '$ggsn'
                            GROUP BY mydate,ggsn_name,apn");

                $output['title'] = 'PDP SR Per APN GGSN';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'pdp_sr_apn':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,ggsn_name,pdp_sr as y_value
                            FROM `ggsn_pdpsr`
                            where mydate >= date(now()) - interval 7 day and apn = '$apn' $where
                            GROUP BY mydate,ggsn_name,apn");

                $output['title'] = 'PDP SR Per GGSN Jabar';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'pgw_sr_apn':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,ggsn_name,pgw_sr as y_value
                            FROM `ggsn_pgwsr`
                            where mydate >= date(now()) - interval 7 day and apn = '$apn' $where
                            GROUP BY mydate,ggsn_name,apn");

                $output['title'] = 'PGW SR Per GGSN Jabar';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'pgw_sr':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,apn as ggsn_name,pgw_sr as y_value
                            FROM `ggsn_pgwsr`
                            where mydate >= date(now()) - interval 7 day and ggsn_name = '$ggsn'
                            GROUP BY mydate,ggsn_name,apn");

                $output['title'] = 'PGW SR Per APN GGSN';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            case 'peakcpuloadboard':
                $query_pdp = \DB::connection("mysql170")->select("SELECT mydate,entry_board as ggsn_name, peak_cpu as y_value
                    FROM `ggsn_cpuload`
                    where mydate >= date(now()) - interval 7 day and ggsn_name = '$ggsn'
                    group by mydate, ggsn_name, entry_board");

                $output['title'] = 'Peak Cpu Load per board';
                $output['yaxisVal'] = 'Persentage (%)';
                break;
            default:
                return Core::setResponse("not_found", ['result' => 'Mode tidak ada.']);
        }

        $point      = array();
        $output_pdp = array();
        $series     = array();

        if (count($query_pdp) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        foreach ($query_pdp as $key => $result) {
            if ($result->y_value <> 0) {
                $the_date = ($result->mydate);
                $mydate[] = $result->mydate;
                $point[$result->ggsn_name][] = strtotime($the_date) * 1000;
                $point[$result->ggsn_name][] = (float)$result->y_value;
                /* $point[$result->ggsn_name]['date']=$the_date;
                $point[$result->ggsn_name]['value']=$result->y_value; */
                $series1[$result->ggsn_name][] = $point[$result->ggsn_name];
                $series[$result->ggsn_name] = $series1[$result->ggsn_name];
                $objectname[] = $result->ggsn_name;
                $point = array();
            }
        }

        foreach (array_unique($mydate) as $xas) {
            $xaxis[] = $xas;
        };

        $colr = ['#007CC7', '#ff3a22', '#3C1874',  '#ff8928', '#46211A', '#A2C523' ,
            '#2E4600', '#d2601a', '#021C1E', '#6FB98F' ,'#8D230F','#F18D9E', '#5D535E',
            '#5BC8AC','#9B4F0F', '#F1F1F2', '#011A27', '#E6DF44', '#2E2300', '#F52549'];
        $i=0;

        foreach (array_unique($objectname) as $ggsn) {
            $output_pdp['name'] = $ggsn;
            $output_pdp['data'] = $series[$ggsn];
            if ($_GET['mode'] == 'tot_thp_gbps') {
                $output_pdp['color'] = "#3C1874";
            } else if($_GET['mode'] == 'tot_thp_util') {
                $output_pdp['color'] = "#f76b25";
            } else {
                $output_pdp['color'] = $colr[$i];
            }

            $output_ggsn[] = $output_pdp;
            $i++;
        }

        $output['series'] = ($series);
        $output['series'] = json_encode($output_ggsn, JSON_NUMERIC_CHECK);

        return Core::setResponse("success", $output);

    }

    public function ggsn_fan_temperature(Request $request)
    {
        $mode = $request->mode;
        $ggsn = $request->ggsn;
        $area = $request->area;

        if ($mode == '') {
            return Core::setResponse("error", ['mode' => 'Parameter mode tidak boleh kosong.']);
        }
        if ($ggsn == '') {
            return Core::setResponse("error", ['ggsn' => 'Parameter ggsn tidak boleh kosong.']);
        }
        if ($area == '') {
            return Core::setResponse("error", ['area' => 'Parameter area tidak boleh kosong.']);
        }

        $output = array();
        if(!empty($area)){
                if($area =='jabar'){
                    $where = " and (ne_name like '%DGO%' or ne_name like '%SOE%')";
                    $areanya = "Jabar";
                }
        }else{
            $where = " and (ne_name like '%DGO%' or ne_name like '%SOE%')";
            $areanya = "Jabar";
        }

        if (strpos($ggsn, '%') !== false) {$rules = 'like';}
        else {$rules = '=';}

        switch($mode){
            //weekly
            case 'fantray':
                $query_pdp = \DB::connection('mysql139')->select("SELECT date_id,ne_name,slot as category,inlet_temp as y_value from ggsn_fantray_temp_mon
                where date_id >= date(now()) - interval 7 day and ne_name $rules '$ggsn'
                group by date_id,ne_name,slot");

                $output['title']    = 'Fantray Temperature';
                $output['yaxisVal'] = "'C";
                break;

            case 'modul':
                $query_pdp = \DB::connection('mysql139')->select("SELECT  date_id,ne_name,slot as category,temp as y_value from ggsn_hard_temp_mon where date_id >= date(now()) - interval 7 day and ne_name $rules '$ggsn' group by date_id, ne_name, slot");

                $output['title']    = 'Modul Temperature';
                $output['yaxisVal'] = "'C";
                break;

            case 'speedfanft1':
                $query_pdp = \DB::connection('mysql139')->select("SELECT date_id,ne_name,slot,fan1_speed AS y_value, 'fan1' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT1'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan2_speed AS y_value, 'fan2' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT1'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan3_speed AS y_value, 'fan3' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT1'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan4_speed AS y_value, 'fan4' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT1'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan5_speed AS y_value, 'fan5' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT1'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan6_speed AS y_value, 'fan6' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT1'");

                $output['title']    = 'Fantray Speed';
                $output['yaxisVal'] = "RPM";
                break;

            case 'speedfanft2':
                $query_pdp = \DB::connection('mysql139')->select("SELECT date_id,ne_name,slot,fan1_speed AS y_value, 'fan1' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT2'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan2_speed AS y_value, 'fan2' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT2'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan3_speed AS y_value, 'fan3' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT2'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan4_speed AS y_value, 'fan4' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT2'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan5_speed AS y_value, 'fan5' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT2'
                    UNION ALL
                    SELECT date_id,ne_name,slot,fan6_speed AS y_value, 'fan6' AS category
                    FROM ggsn_fantray_speed_mon
                    WHERE date_id >= DATE(NOW()) - INTERVAL 7 DAY AND ne_name $rules '$ggsn' AND slot = 'FT2'");

                $output['title']    = 'Fantray Speed';
                $output['yaxisVal'] = "RPM";
                break;
            default:
                return Core::setResponse("not_found", ['mode' => 'Parameter mode tidak ada.']);
        }

        $point      = array();
        $output_pdp = array();
        $series     = array();

        if (count($query_pdp) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        foreach ($query_pdp as $key => $result) {
            $the_date   = ($result->date_id);
            $date_id[]  = $result->date_id;

            $point[$result->category][] = strtotime($the_date)*1000;
            $point[$result->category][] = (float)$result->y_value;

            $series1[$result->category][]   = $point[$result->category];
            $series[$result->category]      = $series1[$result->category];

            $objectname[] = $result->category;
            $point = array();
        }

        foreach(array_unique($date_id) as $xas){
            $xaxis[]=$xas;
        };

        $colr = ['#007CC7', '#ff3a22', '#3C1874',  '#ff8928', '#46211A', '#A2C523' ,
        '#2E4600', '#d2601a', '#021C1E', '#6FB98F' ,'#8D230F','#F18D9E', '#5D535E',
        '#5BC8AC','#9B4F0F', '#F1F1F2', '#011A27', '#E6DF44', '#2E2300', '#F52549',  '#3C1874'];
        $i=0;

        foreach(array_unique($objectname) as $ggsn){

            $key = array_rand($colr);

            $output_pdp['name']     = $ggsn;
            $output_pdp['data']     = $series[$ggsn];
            $output_pdp['color']    = $colr[$key];

            $output_ggsn[] = $output_pdp;
            $i++;
        }

        $output['series'] = ($series);
        $output['series'] = json_encode($output_ggsn, JSON_NUMERIC_CHECK);

        return Core::setResponse("success", $output);
    }

    public function ggsn_dropdown(Request $request)
    {
        $areain = $request->areain;
        $mode   = $request->mode;

        if ($mode == '') {
            return Core::setResponse("error", ['mode' => 'Parameter mode tidak boleh kosong.']);
        }
        if ($areain == '') {
            return Core::setResponse("error", ['area' => 'Parameter area tidak boleh kosong.']);
        }

        switch ($mode) {
            case 'PDP SR Per APN':
            case 'PGW SR Per APN':
            case 'AVERAGE CPU LOAD BOARD':
            case 'PEAK CPU LOAD BOARD':

                if($areain == 'jabo'){
                    $condition = "(ggsn_name LIKE '%tbs%' OR ggsn_name LIKE '%brn%') AND ";
                }else if($areain == 'jabar'){
                    $condition = "(ggsn_name LIKE '%soe%' OR ggsn_name LIKE '%dgo%') AND ";
                }else{
                    $condition = "";
                }

                if ($mode == 'PDP SR Per APN') {
                    $table = 'ggsn_pdpsr';
                }

                if ($mode == 'PGW SR Per APN') {
                    $table = 'ggsn_pgwsr';
                }

                if ($mode == 'AVERAGE CPU LOAD BOARD' || 'PEAK CPU LOAD BOARD') {
                    $table = 'ggsn_cpuload';
                }

                $query = \DB::connection('mysql170')->select("SELECT DISTINCT(ggsn_name) FROM $table WHERE $condition DATE(mydate) = DATE(NOW())");

                break;

            case 'PDP SR Per GGSN':
            case 'PGW SR Per GGSN':

                if($areain == 'jabo'){
                    $condition = "(ggsn_name LIKE '%tbs%' OR ggsn_name LIKE '%brn%') AND ";
                }else if($areain == 'jabar'){
                    $condition = "(ggsn_name LIKE '%soe%' OR ggsn_name LIKE '%dgo%') AND ";
                }else{
                    $condition = "(ggsn_name LIKE '%soe%' OR ggsn_name LIKE '%dgo%') AND ";
                }

                if ($mode == 'PDP SR Per GGSN') {
                    $table = 'ggsn_pdpsr';
                }

                if ($mode == 'PGW SR Per GGSN') {
                    $table = 'ggsn_pgwsr';
                }

                $query = \DB::connection('mysql170')->select("SELECT DISTINCT(ggsn_name) FROM $table WHERE $condition DATE(mydate) = DATE(NOW())");

                break;

            default:
                return Core::setResponse("not_found", ['mode' => 'Parameter mode tidak ada.']);
        }

        if (count($query) == 0) {
            return Core::setResponse("not_found", ['result' => 'Data tidak ada.']);
        }

        return Core::setResponse('success', "List Name GGSN $mode", $query);

    }
}
