<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ChamberController extends Controller
{
    public function __construct()
    {
        $this->core = new Core();
    }

    public function cek_login(Request $request)
    {
        $username = $request->username;

        if ($username == ''){
            return $this->core->setResponse('error', ['username' => "username tidak boleh kosong"]);
        }

        $query_db = DB::connection("mysqlChamber")->select("SELECT *,
            (
                SELECT COUNT(*)
                FROM boopati_history a1
                WHERE a1.username = a.username
                AND a1.`status` = 'Sukses'
                AND a1.activity LIKE '%Change Password%'
            ) AS count_history_login
            FROM users a
            WHERE username = '".$username."'
            AND status_active = 'yes'
        ");
        $count = count($query_db);

        if ($count == 0) {
            return $this->core->setResponse('not_found', ['username' => "username tidak ditemukan"]);
        }

        $row = $query_db[0];

        if($row->roles == 'tdc'){
            $qi = DB::connection("mysqlChamber")->select("SELECT *
                FROM users a
                LEFT JOIN tdc b ON (a.id_cluster = b.id_tdc)
                LEFT JOIN cluster c ON (b.id_cluster = c.id_cluster)
                LEFT JOIN branch d ON (c.id_branch = d.id_branch)
                LEFT JOIN region e ON (d.id_region = e.id_region)
                WHERE a.username = '".$username."'
            ");

            $qtdc = DB::connection("mysqlChamber")->select("SELECT *
                FROM tdc a
                LEFT JOIN kecamatan b ON (a.id_tdc = b.id_tdc)
                LEFT JOIN kabupaten c ON (b.id_kabupaten = c.id_kabupaten)
                WHERE a.id_tdc = '".$row->id_cluster."'
            ");
            $data_tdc = $qtdc;

        } else {
            if($row->roles == 'mini_grapari'){
                $qi = DB::connection("mysqlChamber")->select("SELECT *
                    FROM users a
                    LEFT JOIN grapari_mini b ON (a.id_cluster = b.id_grapari)
                    WHERE a.username = '".$username."'");
            }
            elseif($row->roles == 'wareg'){
                $qi = DB::connection("mysqlChamber")->select("SELECT *
                    FROM users a
                    LEFT JOIN branch c ON (a.id_cluster = c.id_branch)
                    LEFT JOIN region d ON (c.id_region = d.id_region)
                    WHERE a.username = '".$username."'");
            }
            elseif($row->roles == 'branch'){
                $qi = DB::connection("mysqlChamber")->select("SELECT *
                    FROM users a
                    LEFT JOIN branch c ON (a.id_cluster = c.id_branch)
                    LEFT JOIN region d ON (c.id_region = d.id_region)
                    WHERE a.username = '".$username."'");
            }
            else {
                $qi = DB::connection("mysqlChamber")->select("SELECT *
                    FROM users a
                    LEFT JOIN cluster b ON (a.id_cluster = b.id_cluster)
                    LEFT JOIN branch c ON (b.id_branch = c.id_branch)
                    LEFT JOIN region d ON (c.id_region = d.id_region)
                    WHERE a.username = '".$username."'");
            }
            $data_tdc = null;
        }

        $row_region_table = $qi;

        $hh = $request->server('REMOTE_ADDR');
        $ha = $request->server('HTTP_USER_AGENT');

        // $query_history = DB::connection("mysqlChamber")->select("insert into boopati_history(username,activity,status,errorcode,datetimelog,ip,ket) values ('".$username."','Login ".$username."','Sukses','',now(),'$hh','$ha')");

        $output['session'] = $row_region_table;
        $output['session'][0]->data_tdc = $data_tdc;

        $message = 'Berhasil';
        if($row->count_history_login == 0){
            $output['session'][0]->roles = "change-password";
            $output['redirect'] = "change-password";
        } else {
            $output['session'][0]->roles = $row->roles;
            $output['redirect'] = "home";
        }

        return $this->core->setResponse("success", $output);

    }

    public function menu(Request $request)
    {
        $roles  = $request->roles;
        $link   = $request->link;

        if ($roles == ''){
            return $this->core->setResponse('error', ['roles' => "roles tidak boleh kosong"]);
        }

        $optionroles = "and status_menu = 'active' and (authorized_roles is null or authorized_roles ='' or authorized_roles LIKE '%".$roles."%')";

        $query = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=0 $optionroles order by order_menu");

        $data0 = $query;

        $txt = "";
        foreach($data0 as $val0){

            if($val0->type_menu == "header"){
                $txt .= '<li class="header">'.$val0->nama_menu.'</li>';

                $query1 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=1 and parent_menu=$val0->id_menu $optionroles order by order_menu");
                $data1 = $query1;

                foreach($data1 as $val1){
                    if($val1->type_menu =="link"){
                        if($val1->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val1->target_menu.'"';}
                        $txt .= '<li><a href="'.$link.'/'.$val1->url_menu.'" '.$target.'><i class="fa '.$val1->icon_menu.' '.$val1->color_menu.'"></i> <span>'.$val1->nama_menu.'</span></a></li>';
                    }
                    elseif($val1->type_menu == "treeview"){

                        $txt .= '<li class="treeview">';
                        $txt .= '  <a href="#"><i class="fa '.$val1->icon_menu.' '.$val1->color_menu.'"></i> <span>'.$val1->nama_menu.'</span>';
                        $txt .= '    <span class="pull-right-container">';
                        $txt .= '       <i class="fa fa-angle-left pull-right"></i>';
                        $txt .= '    </span>';
                        $txt .= '  </a>';
                        $txt .= '  <ul class="treeview-menu">';

                        $query2 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=2 and parent_menu=$val1->id_menu $optionroles order by order_menu");
                        $data2 = $query2;

                        foreach($data2 as $val2){
                            if($val2->type_menu =="link"){
                                if($val2->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val2->target_menu .'"';}
                                 $txt .= '<li><a href="'.$link.'/'.$val2->url_menu.'" '.$target.'><i class="fa '.$val2->icon_menu .' '.$val2->color_menu .'"></i> <span>'.$val2->nama_menu .'</span></a></li>';
                            }
                            elseif($val2->type_menu=="url"){
                                if($val2->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val2->target_menu .'"';}
                                 $txt .= '<li><a href="'.$val2->url_menu .'" '.$target.'><i class="fa '.$val2->icon_menu .' '.$val2->color_menu .'"></i> <span>'.$val2->nama_menu .'</span></a></li>';
                            }
                            elseif($val2->type_menu =="treeview"){
                                $txt .= '<li class="treeview">';
                                $txt .= '  <a href="#"><i class="fa '.$val2->icon_menu .' '.$val2->color_menu .'"></i> <span>'.$val2->nama_menu .'</span>';
                                $txt .= '    <span class="pull-right-container">';
                                $txt .= '       <i class="fa fa-angle-left pull-right"></i>';
                                $txt .= '    </span>';
                                $txt .= '  </a>';
                                $txt .= '  <ul class="treeview-menu">';

                                $query3 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=3 and parent_menu=$val2->id_menu $optionroles order by order_menu");
                                $data3 = $query3;

                                foreach($data3 as $val3){
                                    if($val3->type_menu =="link"){
                                        if($val3->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val3->target_menu .'"';}
                                        $txt .= '<li><a href="'.$link.'/'.$val3->url_menu.'" '.$target.'><i class="fa '.$val3->icon_menu .' '.$val3->color_menu .'"></i> <span>'.$val3->nama_menu .'</span></a></li>';
                                    }
                                    elseif($val3->type_menu=="url"){
                                        if($val3->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val3->target_menu .'"';}
                                        $txt .= '<li><a href="'.$val3->url_menu .'" '.$target.'><i class="fa '.$val3->icon_menu .' '.$val3->color_menu .'"></i> <span>'.$val3->nama_menu .'</span></a></li>';
                                    }
                                    elseif($val3->type_menu =="treeview"){
                                        $txt .= '<li class="treeview">';
                                        $txt .= '  <a href="#"><i class="fa '.$val3->icon_menu .' '.$val3->color_menu .'"></i> <span>'.$val3->nama_menu .'</span>';
                                        $txt .= '    <span class="pull-right-container">';
                                        $txt .= '       <i class="fa fa-angle-left pull-right"></i>';
                                        $txt .= '    </span>';
                                        $txt .= '  </a>';
                                        $txt .= '  <ul class="treeview-menu">';

                                        $query4 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=4 and parent_menu=$val3->id_menu $optionroles order by order_menu");
                                        $data4 = $query4;

                                        foreach($data4 as $val4){
                                            if($val4->type_menu =="link"){
                                                if($val4->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val4->target_menu .'"';}
                                                $txt .= '<li><a href="'.$link.'/'.$val4->url_menu.'" '.$target.'><i class="fa '.$val4->icon_menu .' '.$val4->color_menu .'"></i> <span>'.$val4->nama_menu .'</span></a></li>';
                                            }
                                            elseif($val4->type_menu=="url"){
                                                if($val4->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val4->target_menu .'"';}
                                                $txt .= '<li><a href="'.$val4->url_menu .'" '.$target.'><i class="fa '.$val4->icon_menu .' '.$val4->color_menu .'"></i> <span>'.$val4->nama_menu .'</span></a></li>';
                                            }
                                            elseif($val4->type_menu =="treeview"){
                                                $txt .= '<li class="treeview">';
                                                $txt .= '  <a href="#"><i class="fa '.$val4->icon_menu .' '.$val4->color_menu .'"></i> <span>'.$val4->nama_menu .'</span>';
                                                $txt .= '    <span class="pull-right-container">';
                                                $txt .= '       <i class="fa fa-angle-left pull-right"></i>';
                                                $txt .= '    </span>';
                                                $txt .= '  </a>';
                                                $txt .= '  <ul class="treeview-menu">';

                                                $query5 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=5 and parent_menu=$val4->id_menu $optionroles order by order_menu");
                                                $data5 = $query5;

                                                foreach($data5 as $val5){
                                                    if($val5->type_menu =="link"){
                                                        if($val5->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val5->target_menu .'"';}
                                                        $txt .= '<li><a href="'.$link.'/'.$val5->url_menu.'" '.$target.'><i class="fa '.$val5->icon_menu .' '.$val5->color_menu .'"></i> <span>'.$val5->nama_menu .'</span></a></li>';
                                                    }
                                                    elseif($val5->type_menu=="url"){
                                                        if($val5->target_menu ==''){ $target = 'target="_SELF"';}{$target='target="'.$val5->target_menu .'"';}
                                                        $txt .= '<li><a href="'.$val5->url_menu .'" '.$target.'><i class="fa '.$val5->icon_menu .' '.$val5->color_menu .'"></i> <span>'.$val5->nama_menu .'</span></a></li>';
                                                    }
                                                    elseif($val5->type_menu =="treeview"){
                                                        $txt .= '<li class="treeview">';
                                                        $txt .= '  <a href="#"><i class="fa '.$val5->icon_menu .' '.$val5->color_menu .'"></i> <span>'.$val5->nama_menu .'</span>';
                                                        $txt .= '    <span class="pull-right-container">';
                                                        $txt .= '       <i class="fa fa-angle-left pull-right"></i>';
                                                        $txt .= '    </span>';
                                                        $txt .= '  </a>';
                                                        $txt .= '  <ul class="treeview-menu">';
                                                        // untuk level selanjut nya
                                                        $txt .= '  </ul>';
                                                        $txt .= '</li>';
                                                    }
                                                }
                                                $txt .= '  </ul>';
                                                $txt .= '</li>';
                                            }
                                        }
                                        $txt .= '  </ul>';
                                        $txt .= '</li>';
                                    }
                                }
                                $txt .= '  </ul>';
                                $txt .= '</li>';
                            }
                        }
                        $txt .= '  </ul>';
                        $txt .= '</li>';
                    }
                }
            }
        }

        return $this->core->setResponse("success", $txt);
    }

    public function tableRegion(Request $request)
    {
        $keyword_page = $request->keyword_page;

        if ($keyword_page == ''){
            return $this->core->setResponse('error', ['keyword_page' => "keyword page tidak boleh kosong"]);
        }

        $row = DB::connection("mysqlChamber")->select("SELECT * FROM boopati_table_mapped_by_region WHERE keyword_page = '$keyword_page'");

        if (count($row) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $row);
    }

    public function columnName(Request $request)
    {
        $table_obc_msisdn = $request->table_obc_msisdn;

        if ($table_obc_msisdn == ''){
            return $this->core->setResponse('error', ['table_obc_msisdn' => "table_obc_msisdn tidak boleh kosong"]);
        }

        $row = DB::connection("mysqlChamber")->select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_obc_msisdn'");

        if (count($row) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $row);
    }

    public function hitungRetrieve(Request $request)
    {
        $id_branch          = $request->id_branch;
        $id_users           = $request->id_users;
        $table_obc_msisdn   = $request->table_obc_msisdn;
        $aksi               = $request->aksi;
        $username           = $request->username;

        if ($id_branch == ''){
            return $this->core->setResponse('error', ['id_branch' => "id_branch tidak boleh kosong"]);
        }
        if ($table_obc_msisdn == ''){
            return $this->core->setResponse('error', ['table_obc_msisdn' => "table_obc_msisdn tidak boleh kosong."]);
        }

        $row = DB::connection("mysqlChamber")->select("SELECT * FROM cluster WHERE id_branch = '$id_branch'");

        if (count($row) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        $l_mapping = array();
        foreach($row as $row){
            $cl = $row->cluster_name;
            $l_mapping[] = "'$cl'";
        }

        $mapping = implode(",", $l_mapping);

        $row_blm_retr = DB::connection("mysqlChamber")->select("SELECT COUNT(*) AS count_msisdn FROM $table_obc_msisdn WHERE cluster_lacci IN($mapping) AND temp_user IS NULL AND remark_claim IS NULL");

        $row_sdh_retr = DB::connection("mysqlChamber")->select("SELECT COUNT(*) AS count_msisdn FROM $table_obc_msisdn WHERE cluster_lacci IN($mapping) AND temp_user = '$id_users' AND remark_claim IS NULL");

        if ($aksi == 'generate_retrive') {
            $tanggal        = date("Y-m-d");

            $kondisi = "AND cluster_lacci IN($mapping) LIMIT 50000";

            // insert history
            $query_history  = DB::connection("mysqlChamber")->select("INSERT into boopati_history(username,activity,status,errorcode,datetimelog) values ('".$username."','Retrieve Data for ".$id_users." OBC','Begin','',now())");

            // update wl
            $j = 0;
			$query2 = DB::connection("mysqlChamber")->select("SELECT * FROM $table_obc_msisdn WHERE temp_user IS NULL $kondisi");

			foreach($query2 as $query3){
				$msisdn         = $query3->msisdn;
				$query_update   = DB::connection("mysqlChamber")->select("UPDATE ".$table_obc_msisdn." set temp_user = '".$id_users."', temp_date='".$tanggal."' where msisdn='".$msisdn."'");
                $j++;
            }

            $query_history = DB::connection("mysqlChamber")->select("INSERT into boopati_history(username,activity,status,errorcode,datetimelog) values ('".$username."','Retrieve Data with ".$j." attempt OBC','Sukses','',now())");

            return $this->core->setResponse("success", ['result' => "WL Berhasil di retrive"]);
        }

        return $this->core->setResponse("success", ['belum_retrive' => $row_blm_retr, 'sudah_retrive' => $row_sdh_retr]);
    }

    public function ajaxMsisdn(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','2048M');

        $type               = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->type,ENT_QUOTES));
        $table_obc_msisdn   = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->table_obc_msisdn,ENT_QUOTES));
        $id_users           = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->id_users,ENT_QUOTES));
        $flag               = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->flag,ENT_QUOTES));
        $status_claim       = $request->status_claim;
        $region             = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->region,ENT_QUOTES));
        $column             = $request->column;
        $id_branch          = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->id_branch,ENT_QUOTES));
        $start_date         = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->start_date,ENT_QUOTES));
        $end_date           = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->end_date,ENT_QUOTES));

        // if ($column == '') {
        //     return $this->core->setResponse("error", "Pilih Column Dahulu");
        // }

        $output = array();

        switch ($type) {

            case "achievement":

                $status_claim1 = implode(",", $status_claim);

                $query = DB::connection("mysqlChamber")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan FROM boopati_whitelist_claim a WHERE status_claim in (".$status_claim1.") and a.flag = '".$flag."' and a.id_users = '".$id_users."'");

                break;

            case "non":

                if ($flag == 'family_plan'){
                    $field = "regional_channel as region, cluster_sales as cluster, kabupaten, kecamatan, msisdn, brand, temp_date";
                } else if ($flag == 'mini_grapari'){
                    $field = "grapari, status as status, jarak, msisdn, temp_date";
                } else {
                    $field = "region, cluster_lacci as cluster, kabupaten, kecamatan, msisdn, brand, temp_date";
                }

                // $query_limit = DB::connection("mysqlChamber")->select("SELECT ".$field."
                //     from ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," .htmlspecialchars($params['length'],ENT_QUOTES) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("SELECT ".$field." FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."' and REMARK_CLAIM is null");
                break;

            case "non-combomax":

                $field = "region, cluster_lacci as cluster, kabupaten, kecamatan, msisdn, brand, temp_date";

                // $query_limit = DB::connection("mysqlChamber")->select("SELECT ".$field."
                //     from ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " .htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "non_wacluster":

                $field = implode(',', $column);
                // $field = "region_lacci as region, cluster_lacci as cluster, kecamatan, msisdn, segment_id, campaign_channel, keterangan";

                // $query_limit = DB::connection("mysqlChamber")->select("SELECT ".$field."
                //     from ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " . mysqli_real_escape_string($database->connection, htmlspecialchars($params['start'],ENT_QUOTES)) . " ," . mysqli_real_escape_string($database->connection, htmlspecialchars($params['length'],ENT_QUOTES)) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "uploaded_wacluster":

                $month     = date('m');

                $q_mapping = DB::connection("mysqlChamber")->select("SELECT DISTINCT(c.id_cluster), u.id_users FROM tdc t
                    JOIN cluster c ON c.id_cluster = t.id_cluster
                    JOIN users u ON u.id_cluster = c.id_cluster
                    WHERE c.id_branch = '$id_branch' AND u.roles = 'branch_cluster'
                    ORDER BY c.id_cluster");


                $l_mapping = array();
                foreach($q_mapping as $row){
                    $l_mapping[] = "'$row->id_users'";
                }
                $mapping = implode(',', $l_mapping);

                $query = DB::connection("mysqlChamber")->select("SELECT msisdn, status_claim, tanggal_claim FROM boopati_whitelist_claim
                    WHERE flag = '$flag' AND MONTH(tanggal_claim) = '$month' AND id_users IN ($mapping)");

                break;

            case "non_wabranch":

                $field = "cluster_lacci, msisdn, service, flag";

                // $query_limit = DB::connection("mysqlChamber")->select("
                //     select ".$field."
                //     from ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," .htmlspecialchars($params['length'],ENT_QUOTES) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and (remark_claim IN ('') OR remark_claim IS NULL)
                ");
                break;

            case "achievement-gramin":

                // $query_limit = DB::connection("mysqlChamber")->select("SELECT *
                //     FROM ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("SELECT *
                    FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "achievement-hvc-prior":
                // $query_limit = DB::connection("mysqlChamber")->select("SELECT *
                //     FROM ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("SELECT *
                    FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "achievement-wacluster":

                $query = DB::connection("mysqlChamber")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                    FROM boopati_whitelist_claim a
                    WHERE a.flag = '".$flag."' and a.id_users = '".$id_users."'
                ");
                break;

            case "achievement-list-wacluster":

                $query = DB::connection("mysqlChamber")->select("SELECT '' AS `no`, NOW() AS `date_update`, b.header_page AS `campaign`, u.username AS `cluster`, a.msisdn, a.status_claim, a.tanggal_claim
                    FROM boopati_whitelist_claim a
                    LEFT JOIN boopati_table_mapped_by_region b ON b.flag_page = a.flag
                    LEFT JOIN users_tdc u ON u.id_users = a.id_users
                    WHERE flag = '$flag' AND a.id_users = '$id_users'
                    AND (tanggal_claim BETWEEN '$start_date' AND '$end_date')");
                break;

            case "achievement-wabranch":

                if ($status_claim[0] == 'sukses') {
                    $query = DB::connection("mysqlChamber")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                        FROM boopati_whitelist_claim a
                        WHERE a.flag = '".$flag."' AND a.id_users = '".$id_users."' AND
                        a.status_claim LIKE 'sent on%' OR a.status_claim LIKE 'terkirim%'
                    ");
                } else {
                    $query = DB::connection("mysqlChamber")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                        FROM boopati_whitelist_claim a
                        WHERE a.flag = '".$flag."' AND a.id_users = '".$id_users."' AND
                        a.status_claim NOT LIKE 'sent on%' AND a.status_claim NOT LIKE 'terkirim%'
                    ");
                }

                break;

            case "non-achievement-giganet":
                $field = "cluster_lacci AS cluster, segment_id, msisdn";

                $query = DB::connection("mysqlChamber")->select("SELECT $field
                    FROM $table_obc_msisdn
                    WHERE temp_user = '$id_users'
                    AND remark_claim IS NULL
                ");
                break;
            // sepertinya tidak terpakai
            // case "achievement-giganet":
            // 	$status_claim = htmlspecialchars($_GET['status_claim']);
            // 	$status_claim = mysqli_real_escape_string($database->connection, $status_claim);
            // 	$query = "
            // 			SELECT msisdn, status_claim, tanggal_claim, datetime_claim, keterangan
            // 			FROM boopati_whitelist_claim a
            // 			WHERE status_claim in (".$status_claim.") and a.flag = '".$flag."' and a.id_users = '".$id_users."'
            // 		";
            // break;
            case "non_wambjj":

                $field = "msisdn, branch_lacci, cluster_lacci, npsn, nama_sekolah";

                // $query_limit = DB::connection("mysqlChamber")->select("
                //     select ".$field."
                //     from ".$table_obc_msisdn."
                //     where temp_user='".$id_users."'
                //     and REMARK_CLAIM is null
                //     limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                // ");

                $query = DB::connection("mysqlChamber")->select("
                    select ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;
            case "achievement-wambjj":

                $query = DB::connection("mysqlChamber")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                    FROM boopati_whitelist_claim a
                    WHERE a.flag = '".$flag."' and a.id_users = '".$id_users."' /* and table_name = '".$table_obc_msisdn."'
                ");
                break;

            default:
                return $this->core->setResponse("not_found", ['type' => "Type tidak ditemukan"]);

        }

        if (empty($query)) {
            return $this->core->setResponse("not_found", ["result" => "Data Empty"]);
        }

        // $sql = mysqli_query($database->connection, htmlspecialchars($query));

        $totalRecords  = count($query);

        $counter = 0;
        $no      = 0;

        $result = [];
        foreach($query as $res){
            $no++;
            $result[$counter] = $res;
            $result[$counter]->no = $no;
            $counter++;
        }

        // $json_data = array(
        //     "draw"            => intval( htmlspecialchars($params['draw']) ),
        //     "recordsTotal"    => intval( $totalRecords  ),
        //     "recordsFiltered" => intval($totalRecords ),
        //     "data"            => $result   // total data array
        //     );

        $output['data'] = $result;

        return $this->core->setResponse("success", $output);
    }

    public function saveListMsisdn(Request $request)
    {
        $id_users       = $request->id_users;
        $msisdnkirim    = $request->msisdnkirim;
        $hasil          = $request->hasil;
        $catatan        = $request->catatan;
        $flag           = $request->flag;
        $table_obc_msisdn = $request->table_obc_msisdn;
        $brand          = $request->brand;
        $jamklik        = $request->jamklik;

        $query = DB::connection("mysqlChamber")->select("INSERT into boopati_whitelist_claim (id_users,msisdn,status_claim,keterangan,tanggal_claim,datetime_claim, flag, table_name, brand, datetime_open_form) values ('".$id_users."','".$msisdnkirim."','".$hasil."','".$catatan."',now(),now(), '".$flag."', '".$table_obc_msisdn."', '".$brand."', '".$jamklik."')");

        $query2 = DB::connection("mysqlChamber")->select("UPDATE ".$table_obc_msisdn." set remark_claim='".$id_users."', status_telepon='".$hasil."' where msisdn='".$msisdnkirim."' AND temp_user = '$id_users'");

        return $this->core->setResponse("success", ['query' => count($query), 'query2' => count($query2)]);
    }

    public function updateListMsisdn(Request $request)
    {
        $id_users       = $request->id_users;
        $msisdnkirim    = $request->msisdnkirim;
        $hasil          = $request->hasil;
        $table_obc_msisdn = $request->table_obc_msisdn;
        $jamklik        = $request->jamklik;

        $query = DB::connection("mysqlChamber")->select("UPDATE ".$table_obc_msisdn." set remark_claim='".$id_users."', status_telepon='".$hasil."' where msisdn='".$msisdnkirim."' AND temp_user = '$id_users'");

        $query2 = DB::connection("mysqlChamber")->select("UPDATE boopati_whitelist_claim set status_claim='".$hasil."',keterangan = '".$catatan."',tanggal_claim = now(), datetime_claim=now(), datetime_open_form = '".$jamklik."' where msisdn='".$msisdnkirim."' and id_users='".$id_users."'");

        return $this->core->setResponse("success", ['query' => count($query), 'query2' => count($query2)]);

    }

    public function export_mapping_msisdn(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','2048M');

        $ftype      = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->ftype));
        $flag       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->flag));
        $table      = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->table));
        $id_user    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->id_user));
        $id_branch  = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->id_branch));

        $flag_list          = array('combomax', 'isakobc', 'obc_giganet', 'wacluster', 'isakwacluster', 'hotpromo', 'imax', 'btl_comsak', 'btl_giganet', 'btl_insak', 'btl_inlife', 'btl_promsak', 'btl_winback', 'coreall', 'voucher', 'p2pnon', 'p2pperso', 'ads_hotpromo', 'ads_imax', 'ads_comsak', 'ads_giganet', 'ads_insak', 'ads_inlife', 'ads_promsak', 'ads_winback', 'ads_coreall', 'ads_voucher', 'ads_p2pnon','ads_p2pperso', 'tiktok_hotpromo', 'tiktok_imax', 'tiktok_btl_comsak', 'tiktok_btl_giganet', 'tiktok_btl_insak', 'tiktok_btl_inlife', 'tiktok_btl_promsak', 'tiktok_btl_winback', 'tiktok_coreall', 'tiktok_voucher', 'tiktok_p2pnon','tiktok_p2pperso', 'takers_hotpromo', 'takers_imax', 'takers_btl_comsak', 'takers_btl_giganet', 'takers_btl_insak', 'takers_btl_inlife', 'takers_btl_promsak', 'takers_btl_winback', 'takers_coreall', 'takers_voucher', 'takers_p2pnon', 'takers_p2pperso', 'omni_outlet', 'omni_tokopedia', 'omni_shopee', 'omni_alfamart', 'omni_indomaret', 'omni_alfamidi', 'omni_lini', 'omni_gojek');

        $flag_obc_call      = array('combomax', 'isakobc');

        $flag_wacluster     = array('wacluster', 'isakwacluster', 'hotpromo', 'imax', 'btl_comsak', 'btl_giganet', 'btl_insak', 'btl_inlife', 'btl_promsak', 'btl_winback', 'coreall', 'voucher', 'p2pnon', 'p2pperso', 'ads_hotpromo', 'ads_imax', 'ads_comsak', 'ads_giganet', 'ads_insak', 'ads_inlife', 'ads_promsak', 'ads_winback', 'ads_coreall', 'ads_voucher', 'ads_p2pnon','ads_p2pperso', 'tiktok_hotpromo', 'tiktok_imax', 'tiktok_btl_comsak', 'tiktok_btl_giganet', 'tiktok_btl_insak', 'tiktok_btl_inlife', 'tiktok_btl_promsak', 'tiktok_btl_winback', 'tiktok_coreall', 'tiktok_voucher', 'tiktok_p2pnon','tiktok_p2pperso', 'takers_hotpromo', 'takers_imax', 'takers_btl_comsak', 'takers_btl_giganet', 'takers_btl_insak', 'takers_btl_inlife', 'takers_btl_promsak', 'takers_btl_winback', 'takers_coreall', 'takers_voucher', 'takers_p2pnon', 'takers_p2pperso', 'omni_outlet', 'omni_tokopedia', 'omni_shopee', 'omni_alfamart', 'omni_indomaret', 'omni_alfamidi', 'omni_lini', 'omni_gojek');

        if(!empty($ftype) && $ftype == 'export'){
            $query = '';

            if(in_array($flag, $flag_obc_call)){ // obc call
                $query = DB::connection("mysqlChamber")->select("SELECT * FROM cluster WHERE id_branch = '$id_branch'");
            }elseif(in_array($flag, $flag_wacluster)){ // wacluster
                $query = DB::connection("mysqlChamber")->select("SELECT * FROM cluster WHERE id_branch = '$id_branch'");
            }
            elseif($flag == 'obc_giganet'){ // obc giganet
                $query = DB::connection("mysqlChamber")->select("SELECT DISTINCT(`wl_for`), username FROM $table
                    JOIN users
                    WHERE temp_user = '$id_user'
                    AND id_users = wl_for
                    AND remark_claim IS NULL");
            }
            else{
                $query = '';
            }

            if(in_array($flag, $flag_list)){ // jika flag ada dalam list
                $mapping        = array();
                $mapping_for    = array();
                $result         = $query;

                foreach($result as $val){
                    if(in_array($flag, $flag_obc_call)){ // obc call
                        $mapping[]      = $val->id_cluster;
                        $mapping_for[]  = $val->cluster_name;
                    }elseif(in_array($flag, $flag_wacluster)){ // wacluster
                        $mapping[]      = $val->id_cluster;
                        $mapping_for[]  = $val->cluster_name;
                    }
                    elseif($flag == 'obc_giganet'){ // obc giganet
                        $mapping[] = $val->wl_for;
                        $mapping_for[]  = $val->username;
                    }
                    else{
                        $mapping        = [];
                        $mapping_for    = [];
                    }
                }
            }

            if(in_array($flag, $flag_list)){ // jika flag ada dalam list

                $ar = array('mapping' => $mapping, 'mapping_for' => $mapping_for, 'ftype' => $ftype, 'flag' => strtoupper($flag), 'time' => date('YmdHis'), 'success' => true);

                return $this->core->setResponse("success", $ar);
            }
            else{

                return $this->core->setResponse("error", array("message" => 'Failed to export, flag not in list', 'success' => false));

                // echo json_encode(
                //     array("message" => 'Failed to export, flag not in list', 'success' => false)
                // );
            }
        }
        else if(!empty($ftype) && $ftype == 'export-mapping'){
            $part               = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->part,ENT_QUOTES));

            if(in_array($flag, $flag_obc_call)){ // obc call
                $l_kec    = array();
                $q_kec = DB::connection("mysqlChamber")->select("SELECT cluster_name FROM cluster WHERE id_cluster = '$part'");
                $r_kec = $q_kec;

                foreach($r_kec as $v_kec){
                    $l_kec[] = "'$v_kec->cluster_name'";
                }

                $kec = implode(',', $l_kec);

                $field  = "@i:=@i+1 AS `no`, region_lacci AS `region`, cluster_lacci AS `cluster`, kecamatan, msisdn, segment_id, rank_msisdn, campaign_channel, keterangan";
                $query      = DB::connection("mysqlChamber")->select("SELECT $field FROM $table, (SELECT @i:= 0) AS foo WHERE cluster_lacci IN ($kec) AND temp_user = '$id_user' AND remark_claim IS NULL");
            }
            elseif(in_array($flag, $flag_wacluster)){ // wacluster
                $l_kec = array();
                $q_kec = DB::connection("mysqlChamber")->select("SELECT cluster_name FROM cluster WHERE id_cluster = '$part'");
                $r_kec = $q_kec;

                foreach($r_kec as $v_kec){
                    $l_kec[] = "'$v_kec->cluster_name'";
                }

                $kec = implode(',', $l_kec);

                $field  = "@i:=@i+1 AS `no`, region_lacci AS `region`, cluster_lacci AS `cluster`, kecamatan, msisdn, segment_id, rank_msisdn, campaign_channel, keterangan";
                $query      = DB::connection("mysqlChamber")->select("SELECT $field FROM $table, (SELECT @i:= 0) AS foo WHERE cluster_lacci IN ($kec) AND temp_user = '$id_user' AND remark_claim IS NULL");
            }
            else{ // obc giganet
                $field  = "@i:=@i+1 AS `no`, cluster_lacci AS `cluster`, segment_id, msisdn";
                $query      = DB::connection("mysqlChamber")->select("SELECT $field FROM $table, (SELECT @i:= 0) AS foo WHERE wl_for = '$part' AND temp_user = '$id_user' AND remark_claim IS NULL");
            }

            $result = $query;

            return $this->core->setResponse("success", array('result' => $result, 'ftype' => $ftype));

            // echo json_encode(
            //     array('result' => $result, 'ftype' => $ftype)
            // );
        }
        else{

            return $this->core->setResponse("error", array("message" => 'Failed to export', 'success' => false));

            // echo json_encode(
            //     array("message" => 'Failed to export', 'success' => false)
            // );
        }

    }

    public function list_cluster(Request $request)
    {
        $id_branch = $request->id_branch;

        if ($id_branch == ''){
            return $this->core->setResponse('error', ['id_branch' => "id_branch tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT u.id_users, u.username, c.id_cluster, c.cluster_name, c.id_branch
            FROM users u
            JOIN cluster c ON c.id_cluster = u.id_cluster
            WHERE c.id_branch = '$id_branch' AND u.roles = 'branch_cluster'
            ORDER BY c.id_branch ASC");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function list_wlupload(Request $request)
    {
        $username   = $request->username;
        $flag       = $request->flag;

        if ($username == ''){
            return $this->core->setResponse('error', ['username' => "username tidak boleh kosong"]);
        }
        if ($flag == ''){
            return $this->core->setResponse('error', ['flag' => "flag tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT * from boopati_file_import a
                    JOIN region b ON (a.id_region = b.id_region)
                    JOIN boopati_file_import_summary c on (a.id_file_import=c.id_file_import)
                    WHERE created_by = '$username'
                    AND flag = '$flag'
                    AND DATE_FORMAT(created_date, '%Y-%m') BETWEEN DATE_FORMAT(NOW()  - INTERVAL 1 MONTH, '%Y-%m') AND DATE_FORMAT(NOW(), '%Y-%m') ORDER BY c.date_upload DESC");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function list_wlupload_wb(Request $request)
    {
        $username   = $request->username;
        $flag       = $request->flag;

        if ($username == ''){
            return $this->core->setResponse('error', ['username' => "username tidak boleh kosong"]);
        }
        if ($flag == ''){
            return $this->core->setResponse('error', ['flag' => "flag tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT * from boopati_file_import a
                    JOIN region b ON (a.id_region = b.id_region)
                    JOIN boopati_file_import_summary c on (a.id_file_import=c.id_file_import)
                    WHERE created_by = '$username'
                    AND flag = '$flag'
                    AND MONTH(created_date) = MONTH(NOW())
                    AND YEAR(created_date) = YEAR(NOW()) ORDER BY date_upload DESC");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function list_achive_top10(Request $request)
    {
        $keyword_page = $request->keyword_page;

        if ($keyword_page == ''){
            return $this->core->setResponse('error', ['keyword_page' => "keyword_page tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT id_users, username,
        sum(tot_call) as tot_call,
        sum(call_sukses) as call_sukses,
        sum(call_already_activated) as call_sudah
        from boopati_achievement
        where id_users is not null and flag = '" . $keyword_page . "'
        group by id_users, username
        order by tot_call desc,call_sukses desc
        limit 0,10");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);

    }

    public function detectDelimiter($csvFile)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    public function insert_upload_wl(Request $request)
    {
        $file           = $request->file('file_csv');
        $flag           = $request->flag;
        $id_region      = $request->id_region;
        $tap_user       = $request->tap_user;
        $table_name     = $request->table_name;
        $username       = $request->username;

        if ($file != '') {

            $file_oriname   = $file->getClientOriginalName();
            $file_size      = $file->getSize();
            $fileMimeType   = $file->getClientMimeType();
            $filename       = pathinfo($file_oriname, PATHINFO_FILENAME);
            $extension      = pathinfo($file_oriname, PATHINFO_EXTENSION);

            $tanggal        = date('Y-m-d');
            $waktu          = strtotime(date('H:i:s'));
            $file_import 	= str_replace('/','', preg_replace('/[\/<>]/', '', htmlspecialchars($file_oriname)));
            $flag 			= str_replace('/','', htmlspecialchars($flag));
            $id_region 		= str_replace('/','', htmlspecialchars($id_region));
            $tap_user 		= str_replace('/','', htmlspecialchars($tap_user));

            $eror		    = false;
            $max_upload     = false;
            $pesan          = '';
            $folder		    = 'file_upload/';

            $allowed_file = array('application/vnd.ms-excel','text/plain','text/csv');
            $max_size	= 10 * 1024 * 1024; // 10MB

			$file_size	    = $file_size;
			$extensi	    = $extension;

			$tanggal        = date('Ymd');
			$waktu          = date('His');
			$types          = $fileMimeType;

			$file_name	= str_replace('/','',$table_name."_".$tanggal."_".$waktu.".".$extensi);
			// $file_loc   = $folder.$file_name;

            $ext = $extension;
            if($ext == "csv" && in_array($types, array('text/csv', 'application/vnd.ms-excel'))){
                if($file_size <= $max_size){
                    if($file->move(storage_path('file_csv'), $file_oriname)){

                        $query_file_import = DB::connection("mysqlChamber")->select("INSERT into boopati_file_import (file_import,flag,id_region,table_name,created_by,created_date,read_status) values(
                            '".$file_name."',
                            '".$flag."',
                            '".$id_region."',
                            '".$table_name."',
                            '".$username."',
                        now(), '1')");

                        $id_file_import = DB::connection("mysqlChamber")->getPdo()->lastInsertId();
                        $doquery        = $query_file_import;

                        $msg = "Data telah ditambahkan";

                        $file_loc   = storage_path("/file_csv/$file_oriname");
                        $file       = fopen($file_loc, "r");

                        $count_insert_uploaded  = 0;
                        $count_not_uploaded     = 0;
                        $count_insert_claimed   = 0;

                        $delimiter = $this->detectDelimiter($file_loc);

                        // while(!feof($file)){

                        fgets($file);  // read one line for nothing (skip header)
                        while (($val = fgetcsv($file, 10000, ",")) !== FALSE) {

                            // $val = fgetcsv($file,10000,$delimiter);

                            // $val = explode(',', fgets($file));

                            $date_string = explode('-', $val[1]);
                            $date_length = count($date_string);

                            $msisdn         = str_replace(['\r', '\n', '"'], '', htmlspecialchars($val[0]));
                            $date_remark    = htmlspecialchars(date("Y-m-d", strtotime($val[1])));
                            $status_telepon = str_replace(['\r', '\n', '"'], '', htmlspecialchars($val[2]));

                            $date_remark_bulan = date('Y-m', strtotime($date_remark));
                            $bulan_ini = date('Y-m');

                            $tgl_awal_bulan = $bulan_ini . "-01";
                            $tgl_akhir_bulan = date("Y-m-t", strtotime($tgl_awal_bulan));

                            $v = strtotime(date("Y-m-d", strtotime($tgl_akhir_bulan)) . " +1 weeks");
                            $x = strtotime(date("Y-m-d", strtotime($tgl_awal_bulan)) . " -1 weeks");

                            $seminggu_setelah = date("Y-m-d", $v);
                            $seminggu_sebelum = date("Y-m-d", $x);

                            $cari_myads = strpos("$flag", 'myads_');

                            if ($cari_myads !== FALSE) {
                                $condition = '$date_length == "3" and count($val) == 3 and $msisdn != "" and strlen($msisdn) >= 11  and strpos($msisdn, "E+") == false and $date_remark != "1970-01-01" and $date_remark >= $seminggu_sebelum AND $date_remark <= $seminggu_setelah';
                            } else {
                                $condition = '$date_length == "3" and count($val) == 3 and $msisdn != "" and strlen($msisdn) >= 11  and strpos($msisdn, "E+") == false and $date_remark != "1970-01-01" and $date_remark >= date("Y-m-d", strtotime("-3 weeks")) and $date_remark <= date("Y-m-d",strtotime("+1 weeks"))';
                            }

                            $result = eval('return (' . $condition . ');');

                            if($result){

                                $query_insert = DB::connection("mysqlChamber")->select("INSERT INTO boopati_whitelist_claim (msisdn, status_claim, tanggal_claim, datetime_claim, flag, table_name, id_users, id_file_import, status_telepon, remark_claim, date_upload)
                                    VALUES('$msisdn', '$status_telepon', '$date_remark', '$date_remark', '$flag', '$table_name', '$tap_user', $id_file_import,'$status_telepon', '$tap_user', NOW())");

                                $insert_wl_upload = $query_insert;

                                if(count($insert_wl_upload) == 0){
                                    $count_insert_uploaded++;
                                } else {
                                    $count_insert_claimed++;
                                }

                            }
                            // else {
                            //     $count_not_uploaded = (count($data_import) - $count_insert_uploaded);
                            // }
                        }

                        $import_summary = DB::connection("mysqlChamber")->select("INSERT INTO boopati_file_import_summary (id_file_import, claimed, uploaded, not_uploaded, date_upload) VALUES ($id_file_import, '$count_insert_claimed', '$count_insert_uploaded', '$count_not_uploaded', NOW())");

                        $insert_import_summary = $import_summary;
                        fclose($file);

                        if(count($insert_import_summary) == 0){

                            unlink($file_loc);
                            return $this->core->setResponse("success", ['info' => 'Data telah ditambahkan.', 'alert' => 'success']);

                        } else {
                            unlink($file_loc);

                            return $this->core->setResponse("error", ['info' => 'Data gagal ditambahkan.', 'alert' => 'danger']);
                        }

                    } else {
                        return $this->core->setResponse("error", ['file_upload' => 'File gagal diupload', 'alert' => 'danger']);
                    }

                } else {
                    return $this->core->setResponse("error", ['file_size' => 'Maksimal Upload 10 MB', 'alert' => 'danger']);
                }

            } else {
                return $this->core->setResponse("error", ['format_file' => 'Format file harus .CSV', 'alert' => 'danger']);
            }

        } else {
            return $this->core->setResponse("error", ['file' => 'File Import harus diisi!', 'alert' => 'danger']);
        }

    }

    public function export_achiev_wl(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2048M');

        $ftype = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->ftype);

        if (!empty($ftype) && $ftype == 'export') {
            $array_mode = array('all', 'sukses', 'sudahaktivasi', 'tidakdiangkat', 'menolakaktivasi', 'menolakregistrasi');
            $id_branch	= $request->id_branch;
            $roles 		= $request->roles;
            $username 	= $request->username;

            // mendapatkan list id branch cluster
            $id_branch_cluster	= '';
            if ($roles == 'branch') {
                $l_cluster  = array();
                $q_cluster	= DB::connection("mysqlChamber")->select("SELECT * FROM users_branch_cluster WHERE id_branch = '$id_branch'");
                $r_cluster = $q_cluster;

                foreach ($r_cluster as $v_cluster) {
                    $id_u = $v_cluster->id_users;
                    $l_cluster[] = "'$id_u'";
                }

                $id_branch_cluster = implode(',', $l_cluster);

                $filter = " AND id_users IN ($id_branch_cluster)";
            } else {
                $filter = '';
            }

            $mode 		= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->mode);
            $flag 		= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->flag);
            $start_date = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->start_date);
            $end_date 	= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->end_date);

            $option_date = "";
            if (!empty($start_date)) {
                $option_date = " AND (tanggal_claim BETWEEN '$start_date' AND '$end_date')";
            }

            switch ($mode) {
                case 'all':
                    $option = "where flag = '" . $flag . "'";
                    break;
                case 'sukses':
                    $option = " where status_claim='sukses' and flag = '" . $flag . "'";
                    break;
                case 'sudahaktivasi':
                    $option = " where status_claim='sudahaktivasi' and flag = '" . $flag . "'";
                    break;
                case 'tidakdiangkat':
                    $option = " where status_claim='tidakdiangkat' and flag = '" . $flag . "'";
                    break;
                case 'menolakaktivasi':
                    $option = " where status_claim='menolakaktivasi' and flag = '" . $flag . "'";
                    break;
                case 'menolakregistrasi':
                    $option = " where status_claim='menolakaktivasi' and flag = '" . $flag . "'";
                    break;
            }

            if ($roles == 'branch') {
                $query = DB::connection("mysqlChamber")->select("SELECT COUNT(*) AS total FROM boopati_whitelist_claim a $option $option_date $filter");
            } else {
                $query = DB::connection("mysqlChamber")->select("SELECT COUNT(*) AS total FROM boopati_whitelist_claim a LEFT JOIN users_tdc u ON u.id_users = a.id_users $option $option_date $filter");
            }

            $result = (object)$query[0];
            $part 	= ($result->total / 100000); // dibagi per seratus ribu

            return $this->core->setResponse("success", array("total" => (int)$result->total, 'total_part' => ceil($part), 'ftype' => $ftype, 'time' => date('YmdHis')));

        } else if (!empty($ftype) && $ftype == 'export-partial') {

            $array_mode = array('all', 'sukses', 'sudahaktivasi', 'tidakdiangkat', 'menolakaktivasi', 'menolakregistrasi');
            $id_branch	= $request->id_branch;
            $roles 		= $request->roles;
            $username 	= $request->username;

            // mendapatkan list id branch cluster
            $id_branch_cluster	= '';
            if ($roles == 'branch') {
                $l_cluster  = array();
                $q_cluster	= DB::connection("mysqlChamber")->select("SELECT * FROM users_branch_cluster WHERE id_branch = '$id_branch'");
                $r_cluster = $q_cluster;

                foreach ($r_cluster as $v_cluster) {
                    $id_u = $v_cluster->id_users;
                    $l_cluster[] = "'$id_u'";
                }

                $id_branch_cluster = implode(',', $l_cluster);

                $filter = " AND a.id_users IN ($id_branch_cluster)";
            } else {
                $filter = '';
            }

            $part 				= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $_POST['part']);
            $mode 				= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $_POST['mode']);
            $flag 				= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $_POST['flag']);
            $start_date 		= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $_POST['start_date']);
            $end_date 			= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $_POST['end_date']);

            $q_tablee = DB::connection("mysqlChamber")->select("SELECT * FROM boopati_table_mapped_by_region WHERE keyword_page = '$flag'");
            $q_table = (object)$q_tablee[0];

            $explode_table_name = explode("(id_region)", $q_table->table_name);
            $table_name = $explode_table_name[0];

            $table1				= $table_name . '1';
            $table2				= $table_name . '2';
            $table3				= $table_name . '3';
            $table4				= $table_name . '4';

            $limit_start 		= ($part * 100000);
            $limit_end 			= 100000;

            $option_date = "";
            if (!empty($start_date)) {
                $option_date = " AND (a.tanggal_claim BETWEEN '$start_date' AND '$end_date')";
            }

            switch ($mode) {
                case 'all':
                    $option = "where a.flag = '" . $flag . "'";
                    break;
                case 'sukses':
                    $option = " where a.status_claim='sukses' and a.flag = '" . $flag . "'";
                    break;
                case 'sudahaktivasi':
                    $option = " where a.status_claim='sudahaktivasi' and a.flag = '" . $flag . "'";
                    break;
                case 'tidakdiangkat':
                    $option = " where a.status_claim='tidakdiangkat' and a.flag = '" . $flag . "'";
                    break;
                case 'menolakaktivasi':
                    $option = " where a.status_claim='menolakaktivasi' and a.flag = '" . $flag . "'";
                    break;
                case 'menolakregistrasi':
                    $option = " where a.status_claim='menolakaktivasi' and a.flag = '" . $flag . "'";
                    break;
            }

            if ($roles == 'branch') {
                $id_region	= $request->id_region;
                $table 		= $table_name . $id_region;

                $query = DB::connection("mysqlChamber")->select("SELECT @i:=@i+1 AS `no`, NOW() AS `date_update`, u.username, a.msisdn, REPLACE(a.status_claim, '\r\n', '') AS status_claim, a.tanggal_claim, f.created_date as date_upload,
                    a.datetime_claim, a.datetime_open_form, '$flag' AS `campaign`, a.keterangan, w.flag_combo_sakti AS Flag, w.last_date_activation AS `Last Date Activation`
                FROM boopati_whitelist_claim a
                LEFT JOIN $table w ON w.msisdn = a.msisdn
                LEFT JOIN boopati_file_import f ON f.id_file_import = a.id_file_import
                LEFT JOIN users_branch_cluster u ON u.id_users = a.id_users,
                (SELECT @i:=$limit_start) AS foo
                $option $option_date $filter
                LIMIT $limit_start, $limit_end");
            } else {
                $query = DB::connection("mysqlChamber")->select("SELECT @i:=@i+1 AS `no`, CAST(NOW() as DATE) AS `date_update`, u.username, a.msisdn, REPLACE(a.status_claim, '\r\n', '') AS status_claim, a.tanggal_claim, CAST(f.created_date as DATE) as date_upload,
                CAST(a.datetime_claim as DATE) as datetime_claim, CAST(a.datetime_open_form as DATE) as datetime_open_form, '$flag' AS `campaign`, a.keterangan,
                IF(
                    (a.`table_name` = '$table1'),
                    (SELECT c.flag_combo_sakti FROM $table1 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                    IF(
                        (a.`table_name` = '$table2'),
                        (SELECT c.flag_combo_sakti FROM $table2 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                        IF(
                            (a.`table_name` = '$table3'),
                            (SELECT c.flag_combo_sakti FROM $table3 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                            IF(
                                (a.`table_name` = '$table4'),
                                (SELECT c.flag_combo_sakti FROM $table4 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                                NULL
                            )
                        )
                    )
                ) AS `Flag`,
                IF(
                    (a.`table_name` = '$table1'),
                    (SELECT c.last_date_activation FROM $table1 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                    IF(
                        (a.`table_name` = '$table2'),
                        (SELECT c.last_date_activation FROM $table2 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                        IF(
                            (a.`table_name` = '$table3'),
                            (SELECT c.last_date_activation FROM $table3 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                            IF(
                                (a.`table_name` = '$table4'),
                                (SELECT c.last_date_activation FROM $table4 c WHERE (c.msisdn = c.msisdn) LIMIT 1),
                                NULL
                            )
                        )
                    )
                ) AS `Last Date Activation`
                FROM boopati_whitelist_claim a
                LEFT JOIN boopati_file_import f ON f.id_file_import = a.id_file_import
                LEFT JOIN users_tdc u ON u.id_users = a.id_users,
                        (SELECT @i:=$limit_start) AS foo
                $option $option_date $filter
                LIMIT $limit_start, $limit_end");
            }

            $result = $query;

            return $this->core->setResponse("success", array("result" => $result, 'ftype' => $ftype, 'time' => date('YmdHis')));

        } else {

            return $this->core->setResponse("error", array("message" => 'Failed to export', 'ftype' => $ftype));
        }
    }

    public function tot_info_achiev(Request $request)
    {
        $keyword_page = $request->keyword_page;

        if ($keyword_page == ''){
            return $this->core->setResponse('error', ['keyword_page' => "Keyword page tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT sum(tot_call) as tot_call,
                  sum(call_sukses) as call_sukses,
                  sum(call_notanswer) as call_notanswer,
                  sum(call_already_activated) as call_sudah,
                  sum(call_reject) as call_reject
                  from boopati_achievement
                  where flag = '" . $keyword_page . "'");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function users_branch_cluster()
    {
        $query = DB::connection("mysqlChamber")->select("SELECT id_users, username FROM users_branch_cluster");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function list_achive_top10_wabranch(Request $request)
    {
        $keyword_page = $request->keyword_page;

        if ($keyword_page == ''){
            return $this->core->setResponse('error', ['keyword_page' => "keyword_page tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT id_users, username,
        sum(tot_call) as tot_call,
        sum(call_sukses) as call_sukses,
        sum(call_already_activated) as call_sudah
        from boopati_achievement
        where id_users is not null and flag = '".$keyword_page."'
        AND MONTH(tanggal_claim) = MONTH(NOW())
        AND YEAR(tanggal_claim) = YEAR(NOW())
        group by id_users, username
        order by tot_call desc,call_sukses desc
        limit 0,10");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function export_achiev_wabranch(Request $request)
    {
        $ftype = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->ftype);

        if(!empty($ftype) && $ftype == 'export'){
            $array_mode = array('all','sukses','gagal');

            $mode 		= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->mode);
            $flag 		= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->flag);
            $start_date = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->start_date);
            $end_date 	= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->end_date);

            $option_date = "";
            if(!empty($start_date)){
                $option_date = " AND (tanggal_claim BETWEEN '$start_date' AND '$end_date')";
            }

            switch($mode){
                case 'all':
                    $option = "WHERE flag = '".$flag."'";
                    break;
                case 'sukses':
                    $option = "WHERE (status_claim LIKE '%sent%' OR status_claim LIKE '%terkirim%' OR status_claim LIKE '%success%') AND flag = '".$flag."'";
                    break;
                case 'gagal':
                    $option = "WHERE (status_claim LIKE '%gagal%' OR status_claim LIKE '%failed%' OR status_claim LIKE '%not%') AND flag = '".$flag."'";
                    break;
            }

            $query = DB::connection("mysqlChamber")->select("SELECT @i:=@i+1 AS `no`, NOW() AS `date_update`, u.username, a.msisdn, REPLACE(a.status_claim, '\r\n', '') AS status_claim, a.tanggal_claim,
                a.datetime_claim, '$flag' AS `campaign`,
                IF(
                    (a.`table_name` = 'wl_wabranch1'),
                    (SELECT c.flag FROM wl_wabranch1 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                    IF(
                        (a.`table_name` = 'wl_wabranch2'),
                        (SELECT c.flag FROM wl_wabranch2 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                        IF(
                            (a.`table_name` = 'wl_wabranch3'),
                            (SELECT c.flag FROM wl_wabranch3 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                            IF(
                                (a.`table_name` = 'wl_wabranch4'),
                                (SELECT c.flag FROM wl_wabranch4 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                                NULL
                            )
                        )
                    )
                ) AS `service`,
                IF(
                    (a.`table_name` = 'wl_wabranch1'),
                    (SELECT c.period FROM wl_wabranch1 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                    IF(
                        (a.`table_name` = 'wl_wabranch2'),
                        (SELECT c.period FROM wl_wabranch2 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                        IF(
                            (a.`table_name` = 'wl_wabranch3'),
                            (SELECT c.period FROM wl_wabranch3 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                            IF(
                                (a.`table_name` = 'wl_wabranch4'),
                                (SELECT c.period FROM wl_wabranch4 c WHERE (a.msisdn = c.msisdn) LIMIT 1),
                                NULL
                            )
                        )
                    )
                ) AS `period`
                FROM boopati_whitelist_claim a
                LEFT JOIN users_tdc u ON u.id_users = a.id_users,
                (SELECT @i:= 0) AS foo $option $option_date");

            $result = $query;

            return $this->core->setResponse("success", array("result" => $result, 'ftype' => $ftype, 'time' => date('YmdHis')));

        }
        else{

            return $this->core->setResponse("error", array("message" => 'Failed to export', 'ftype' => $ftype));
        }
    }

    public function tot_info_achiev_wabranch(Request $request)
    {
        $keyword_page = $request->keyword_page;

        if ($keyword_page == ''){
            return $this->core->setResponse('error', ['keyword_page' => "Keyword page tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT sum(tot_call) as tot_call,
                        sum(call_sukses) as call_sukses,
                        sum(call_notanswer) as call_notanswer,
                        sum(call_already_activated) as call_sudah,
                        sum(call_reject) as call_reject
                        from boopati_achievement
                        where flag = '".$keyword_page."'
                        AND MONTH(tanggal_claim) = MONTH(NOW())
                        AND YEAR(tanggal_claim) = YEAR(NOW())");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function export_achiev(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','2048M');

        $array_mode = array('all','sukses','sudahaktivasi','tidakdiangkat','menolakaktivasi', 'menolakregistrasi');
        $roles = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->roles));

        $username = $request->username;
        if(!empty(htmlspecialchars($request->mode)) && in_array(htmlspecialchars($request->mode),$array_mode)){

            if($roles == 'branch'){
                $branch_tmp = str_replace('branch_', '', $username);
                $filter = " and branch_name like '%".$branch_tmp."%'";
                $query = DB::connection("mysqlChamber")->select("select * from user_mapping_branch where user = '$username' limit 1");
                $row = (object)$query[0];
                $filter = " and branch_name like '%".$row->branch."%'";
            }
            else{
                $filter ='';
            }

            $week = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->week));
            $year = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->year));
            $flag = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->flag));
            $flag = preg_replace('/[^a-z-]/i', '', $flag);
            $start_date = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->start_date));
            $end_date = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->end_date));

            $option_date = "";
            if(!empty($request->start_date)){
                $option_date = " AND tanggal_claim BETWEEN '".$start_date."' AND '".$end_date."'";
            }

            $date = date('YmdHi');
            $filename = 'Output_boopati_' . $date . '.csv';
            $content  = (string) preg_replace('/[\/<>]/', '', 'Content-Disposition: attachment; filename="'.$filename.'"');

            header('Content-type: text/csv');
            header($content);

            // Get Records from the table
            switch($request->mode){
            case 'all':
                $option = "where flag = '".$flag."'";
                break;
            case 'sukses':
                $option = " where status_claim='sukses' and flag = '".$flag."'";
                break;
            case 'sudahaktivasi':
                $option = " where status_claim='sudahaktivasi' and flag = '".$flag."'";
                break;
            case 'tidakdiangkat':
                $option = " where status_claim='tidakdiangkat' and flag = '".$flag."'";
                break;
            case 'menolakaktivasi':
                $option = " where status_claim='menolakaktivasi' and flag = '".$flag."'";
                break;
            case 'menolakregistrasi':
                $option = " where status_claim='menolakaktivasi' and flag = '".$flag."'";
                break;
            }

            $query = DB::connection("mysqlChamber")->select("SELECT now() as date_update,a.username, a.msisdn, a.status_claim, a.tanggal_claim, a.datetime_claim, a.datetime_open_form from list_call_old a $option $option_date $filter
            union select now() as date_update,a.username, a.msisdn, a.status_claim, a.tanggal_claim, a.datetime_claim, a.datetime_open_form from list_call a $option $option_date $filter");

            $sql = $query;
            $no = 0;

            // create a file pointer connected to the output stream
            $file = fopen('php://output', 'w');

            // send the column headers
            fputcsv($file, array('No', 'Date Update', 'Username', 'msisdn', 'Status Claim', 'tanggal_claim', 'Datetime Submit Claim', 'Datetime Open Form'));

            // output each row of the data
            foreach ($sql as $result) {
                $no++;
                $data = array($no, $result->date_update, $result->username, $result->msisdn, $result->status_claim, $result->tanggal_claim, $result->datetime_claim, $result->datetime_open_form);
                fputcsv($file, $data);
            }

            fclose($file);

            exit();
        }
    }

    public function obc_per_cluster_achiev(Request $request)
    {
        $keyword_page   = $request->keyword_page;
        $regional       = $request->regional;

        if ($keyword_page == ''){
            return $this->core->setResponse('error', ['keyword_page' => "Keyword page tidak boleh kosong"]);
        }
        if ($regional == ''){
            return $this->core->setResponse('error', ['regional' => "Regional tidak boleh kosong"]);
        }

        $query = DB::connection("mysqlChamber")->select("SELECT cluster_name,branch_name, regional,
            count(id_tdc) as total_tdc,
            sum(remark_obc) as total_tdc_obc,
            ROUND(sum(remark_obc)/count(id_tdc)*100,2) as percentage,sum(jml_obc) as total_call
            from (
            select a.id_tdc, a.tdc, a.cluster_name, a.branch_name, a.regional,
            case when SUM(b.total_call) is null then 0 else SUM(b.total_call) end as jml_obc,
            case when SUM(b.total_call) is not null then 1 else 0 end as remark_obc
            from users_tdc a left join
            (
                select a.id_users, sum(a.tot_call)as total_call
                from boopati_achievement a
                where a.flag = '$keyword_page'
                group by a.id_users
            ) b
            on a.id_users=b.id_users
            group by a.id_tdc, a.cluster_name, a.branch_name, a.regional
            ) aaa
            where regional = '$regional'
            group by cluster_name, branch_name, regional
            order by total_call desc
        ");

        if (count($query) == 0) {
            return $this->core->setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function upload_file_wabranch(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $file       = $request->file('upload_file');
        $kondisi    = preg_replace('/[\/<>]/', '', htmlspecialchars($request->input('optradio')));

        if ($kondisi == 'wl') {
            DB::connection("mysqlChamber")->select("TRUNCATE TABLE wl_wabranch1");
            DB::connection("mysqlChamber")->select("TRUNCATE TABLE wl_wabranch2");
            DB::connection("mysqlChamber")->select("TRUNCATE TABLE wl_wabranch3");
            DB::connection("mysqlChamber")->select("TRUNCATE TABLE wl_wabranch4");

        } elseif ($kondisi == 'flag') {
            $isi = $request->input('multi_opt');

            $isi_list = implode(",", $isi);

            DB::connection("mysqlChamber")->select("DELETE FROM wl_wabranch1 where flag IN ('$isi_list')");
            DB::connection("mysqlChamber")->select("DELETE FROM wl_wabranch2 where flag IN ('$isi_list')");
            DB::connection("mysqlChamber")->select("DELETE FROM wl_wabranch3 where flag IN ('$isi_list')");
            DB::connection("mysqlChamber")->select("DELETE FROM wl_wabranch4 where flag IN ('$isi_list')");

        } elseif ($kondisi == 'no') {
            if (empty($file))
            {
                return $this->core->setResponse("error", ['file' => "File kosong, harap diinput"]);
            }

        } else {
            return $this->core->setResponse("error", ['optradio' => "optradio salah input. Pilihan: wl, flag, no"]);
        }

        if (!empty($file))
        {
            $file_oriname   = $file->getClientOriginalName();
            $extension      = pathinfo($file_oriname,PATHINFO_EXTENSION);
            $extension      = strtolower($extension);

            $allowed_extensions = array("xlsx");

            if(!in_array(strtolower($extension), $allowed_extensions)) {
                return $this->core->setResponse("success", ['type' => "Format file harus .xlxs"]);
            }

            $file->move(storage_path('file_xlxs'), $file_oriname);

            $spreadsheet = new Spreadsheet();
            $writer = new Xlsx($spreadsheet);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load(storage_path("/file_xlxs/$file_oriname"));

            $reader = IOFactory::createReader('Xlsx');
            $worksheetNames = $reader->listWorksheetNames(storage_path("/file_xlxs/$file_oriname"));

            if (count($worksheetNames) > '11') {
                unlink(storage_path("/file_xlxs/$file_oriname"));
                return $this->core->setResponse("error", ['cluster' => "Cluster kurang dari 11"]);
            }

            $spreadSheetAry = [];
            $i = 0;
            foreach ($worksheetNames as $worksheetName) {
                $excelSheet = $spreadsheet->getSheetByName($worksheetName);
                $spreadSheetAry[] = $excelSheet->toArray();
                unset($spreadSheetAry[$i][0]);

                $i++;
            }

            $sql1 = [];
            $sql2 = [];
            $sql3 = [];
            $sql4 = [];
            for ($i=0; $i < count($spreadSheetAry); $i++) {
                for ($j=1; $j <= count($spreadSheetAry[$i]); $j++) {
                    $region1 = ['NORTHERN JAKARTA', 'SOUTHERN JAKARTA'];
                    $region2 = ['BEKASI', 'BOGOR', 'KARAWANG'];
                    $region3 = ['BANDUNG', 'SOREANG', 'CIREBON', 'TASIKMALAYA'];
                    $region4 = ['SERANG', 'TANGERANG'];

                    $msisdn         = $spreadSheetAry[$i][$j][0];
                    $branch_lacci   = $spreadSheetAry[$i][$j][1];
                    $cluster_lacci  = $spreadSheetAry[$i][$j][2];
                    $flag           = $spreadSheetAry[$i][$j][3];
                    $service        = $spreadSheetAry[$i][$j][4];

                    $date_upload    = date("Y-m-d");
                    if (in_array($branch_lacci, $region1)) {
                        $table  = 'wl_wabranch1';
                        $region = 'CENTRAL JABOTABEK';
                        $sql1[] = "('$msisdn', '$region', '$branch_lacci', '$cluster_lacci', '$service', '$flag', '$date_upload')";
                    } elseif (in_array($branch_lacci, $region2)) {
                        $table  = 'wl_wabranch2';
                        $region = 'EASTERN JABOTABEK';
                        $sql2[] = "('$msisdn', '$region', '$branch_lacci', '$cluster_lacci', '$service', '$flag','$date_upload')";
                    } elseif (in_array($branch_lacci, $region3)) {
                        $table  = 'wl_wabranch3';
                        $region = 'JABAR';
                        $sql3[] = "('$msisdn', '$region', '$branch_lacci', '$cluster_lacci', '$service', '$flag','$date_upload')";
                    } elseif (in_array($branch_lacci, $region4)) {
                        $table  = 'wl_wabranch4';
                        $region = 'WESTERN JABOTABEK';
                        $sql4[] = "('$msisdn', '$region', '$branch_lacci', '$cluster_lacci', '$service', '$flag','$date_upload')";
                    }

                }
            }

            DB::connection("mysqlChamber")->select("INSERT into wl_wabranch1 (msisdn, region_lacci, branch_lacci, cluster_lacci, service, flag, date_upload) values ".implode(',', $sql1));
            DB::connection("mysqlChamber")->select("INSERT into wl_wabranch2 (msisdn, region_lacci, branch_lacci, cluster_lacci, service, flag, date_upload) values ".implode(',', $sql2));
            DB::connection("mysqlChamber")->select("INSERT into wl_wabranch3 (msisdn, region_lacci, branch_lacci, cluster_lacci, service, flag, date_upload) values ".implode(',', $sql3));
            DB::connection("mysqlChamber")->select("INSERT into wl_wabranch4 (msisdn, region_lacci, branch_lacci, cluster_lacci, service, flag, date_upload) values ".implode(',', $sql4));

            unlink(storage_path("/file_xlxs/$file_oriname"));

            $option = "";

            $flag =  DB::connection("mysqlChamber")->select('SELECT DISTINCT flag FROM wl_wabranch1');

            foreach ($flag as $value) {
                $option .= "<option value='".$value->flag."'>".$value->flag."</option>";
            }

            if ($kondisi == 'wl') {
                $ar = ['info' => "Semua region WL WABRANCH berhasil dihapus dan data file upload berhasil disimpan",  'option' => $option];
            } elseif ($kondisi == 'flag') {
                $ar = ['info' => "Flag $isi_list berhasil dihapus dan data file upload berhasil disimpan", 'option' => $option];
            } else {
                $ar = ['info' => "Data berhasil disimpan",  'option' => $option];
            }

            return $this->core->setResponse("success", $ar);

        } else {

            $option = "";

            $flag =  DB::connection("mysqlChamber")->select('SELECT DISTINCT flag FROM wl_wabranch1');

            foreach ($flag as $value) {
                $option .= "<option value='".$value->flag."'>".$value->flag."</option>";
            }

            if ($kondisi == 'wl') {
                $ar = ['info' => "Semua region WL WABRANCH berhasil dihapus", 'option' => $option];
            } elseif ($kondisi == 'flag') {
                $ar = ['info' => "Flag $isi_list berhasil dihapus", 'option' => $option];
            }

            return $this->core->setResponse("success", $ar);
        }

    }

    public function stock_wl_recap(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','2048M');

        date_default_timezone_set("Asia/Jakarta");

        $ftype = htmlentities($request->ftype);

        if ($ftype == '') {
            return $this->core->setResponse("error", ['ftype' => "Parameter ftype harus terisi"]);
        }

        $tmp = explode('_',$ftype);
        $flag = $tmp[1];
        $loc = $tmp[0];
        $flag_type = 'cluster';

        switch($loc){
            case "central":
                $region = '1';
            break;
            case "eastern":
                $region = '2';
            break;
            case "jabar":
                $region = '3';
            break;
            case "western":
                $region = '4';
            break;
            default:
                return $this->core->setResponse("not_found", ["region" => "Region tidak ada"]);
        }

        switch($flag)
        {
            case "isakobc" :
                $tabel = 'wl_internet_sakti_obc_region'.$region;
            break;
            case "isakwacluster" :
                $tabel = 'wl_isak_wacluster_region'.$region;
            break;
            case "combomax" :
                $tabel = 'wl_combomax_region'.$region;
            break;
            case "hvc" :
                $tabel = 'wl_75k_region'.$region;
            break;
            case "mbjj" :
                $tabel = 'wl_mbjj_region'.$region;
            break;
            case "comboul" :
                $tabel = 'wl_combo_ul_region'.$region;
            break;
            case "diamond" :
                $tabel = 'wl_hvc_priority_diamond_region'.$region;
            break;
            case "wacluster" :
                $tabel = 'wl_wacluster'.$region;
            break;
            case "wapoi" :
                $tabel = 'wl_wapoi'.$region;
            break;
            case "wabranch" :
                $tabel = 'wl_wabranch'.$region;
                $flag_type = 'branch';
            break;
            case "giganet" :
                $tabel = 'wl_giganet_region'.$region;
                $flag_type = 'giganet';
            break;
            case "wambjj" :
                $tabel = 'wl_wambjj_branch'.$region;
                $flag_type = 'branch';
            break;
            default:
                return $this->core->setResponse("not_found", ["flag" => "Flag tidak ada"]);

        }

        switch($flag_type){
            case "cluster":
                $q = DB::connection("mysqlChamber")->select("SELECT cluster_name, tdc, count(msisdn) as total_msisdn,
                    count(case when temp_user is null then 1 else null end) as total_notyet_call,
                    count(case when temp_user is not null then 1 else null end) as already_call
                    from (
                    select f.*,cluster_name, tdc from $tabel f, (
                    select a.*,b.kecamatan,c.kabupaten,d.cluster_name from tdc a, kecamatan b, kabupaten c, cluster d
                    where a.id_tdc=b.id_tdc and b.id_kabupaten=c.id_kabupaten and a.id_cluster= d.id_cluster) g
                    where f.kecamatan =g.kecamatan and f.kabupaten=g.kabupaten and f.cluster_lacci=g.cluster_name ) h
                    GROUP BY cluster_name, tdc");

                    $sql = $q;
                    $output = array();
                    $output["table"] = "";
                    //$output["sql"] = $q;
                    $index = 0;
                    foreach ($sql as $row)
                    {
                        $output["data"][$index] = htmlentities($row);
                        $output["datanya"][$index]= htmlentities($row);
                        $output["datanya"][$index]->cluster_name = htmlentities($row->cluster_name);
                        $output["datanya"][$index]->tdc = htmlentities($row->tdc);
                        $output["datanya"][$index]->total_msisdn = htmlentities($row->total_msisdn);
                        $output["datanya"][$index]->total_notyet_call = htmlentities($row->total_notyet_call);
                        $output["datanya"][$index]->already_call = htmlentities($row->already_call);
                        $index++;
                    }
                    if(!empty($output["datanya"]))
                    {
                        $no =0;
                        foreach($output["datanya"] as $key => $value)
                        {
                            $no++;
                            $output["table"] .= "<tr>";
                            $output["table"] .= "<td class=''>".htmlentities($no)."</td>";
                            $output["table"] .= "<td class=''>".htmlentities($value->cluster_name)."</td>";
                            $output["table"] .= "<td class=''>".htmlentities($value->tdc)."</td>";
                            $output["table"] .= "<td class=''>".htmlentities($value->total_msisdn)."</td>";
                            if($value->total_notyet_call==0){
                                $output["table"] .= "<td class='' style='background:red;'>".htmlentities($value->total_notyet_call)."</td>";
                            }else{
                                $output["table"] .= "<td class='' style='background:white;'>".htmlentities($value->total_notyet_call)."</td>";
                            }
                            $output["table"] .= "<td class=''>".htmlentities($value->already_call)."</td>";
                            $output["table"] .= "</tr>";
                        }
                    }
                    else{
                        $output["table"] .= "<tr>";
                        $output["table"] .= "<td class='align-center' colspan='5'>Data Kosong 1</td>";
                        $output["table"] .= "</tr>";
                    }
                break;

            case "branch":
                $q = DB::connection("mysqlChamber")->select("SELECT branch_name as cluster_name, count(msisdn) as total_msisdn,
                count(case when status_telepon IS null then 1 else null end) as total_notyet_call,
                count(case when temp_user is not null AND status_telepon IS NOT null then 1 else null end) as already_call
                from (
                select f.*,branch_name from $tabel f, (
                select d.branch_name from branch d) g
                where f.branch_lacci=g.branch_name ) h
                GROUP BY branch_name");

                $sql = $q;
                $output = array();
                $output["table"] = "";
                //$output["sql"] = $q;
                $index = 0;
                // while($row =mysqli_fetch_object($sql))
                foreach ($sql as $row)
                {
                    $output["data"][$index] = htmlentities($row);
                    $output["datanya"][$index]=htmlentities($row);
                    $output["datanya"][$index]->cluster_name = htmlentities($row->cluster_name);
                    $output["datanya"][$index]->total_msisdn = htmlentities($row->total_msisdn);
                    $output["datanya"][$index]->total_notyet_call = htmlentities($row->total_notyet_call);
                    $output["datanya"][$index]->already_call = htmlentities($row->already_call);
                    $index++;
                }
                if(!empty($output["datanya"])){
                    $no =0;
                    foreach($output["datanya"] as $key => $value){
                        $no++;
                        $output["table"] .= "<tr>";
                        $output["table"] .= "<td class=''>".htmlentities($no)."</td>";
                        $output["table"] .= "<td class=''>".htmlentities($value->cluster_name)."</td>";
                        $output["table"] .= "<td class=''>".htmlentities($value->total_msisdn)."</td>";
                        if($value->total_notyet_call==0){
                            $output["table"] .= "<td class='' style='background:red;'>".htmlentities($value->total_notyet_call)."</td>";
                        }else{
                            $output["table"] .= "<td class='' style='background:white;'>".htmlentities($value->total_notyet_call)."</td>";
                        }
                        $output["table"] .= "<td class=''>".htmlentities($value->already_call)."</td>";
                        $output["table"] .= "</tr>";
                        }
                    }
                    else{
                    $output["table"] .= "<tr>";
                    $output["table"] .= "<td class='align-center' colspan='5'>Data Kosong</td>";
                    $output["table"] .= "</tr>";
                    }
                break;

            case "giganet":
                $q = DB::connection("mysqlChamber")->select("SELECT cluster_name, count(msisdn) as total_msisdn,
                count(case when status_telepon IS null then 1 else null end) as total_notyet_call,
                count(case when temp_user is not null AND status_telepon IS NOT null then 1 else null end) as already_call
                from (
                select f.*,cluster_name from $tabel f, (
                select d.cluster_name from cluster d) g
                where f.cluster_lacci=g.cluster_name ) h
                GROUP BY cluster_name");

                $sql = $q;
                $output = array();
                $output["table"] = "";
                //$output["sql"] = $q;
                $index = 0;
                // while($row =mysqli_fetch_object($sql))
                foreach ($sql as $row)
                {
                    $output["data"][$index] = htmlentities($row);
                    $output["datanya"][$index]=htmlentities($row);
                    $output["datanya"][$index]->cluster_name = htmlentities($row->cluster_name);
                    $output["datanya"][$index]->total_msisdn = htmlentities($row->total_msisdn);
                    $output["datanya"][$index]->total_notyet_call = htmlentities($row->total_notyet_call);
                    $output["datanya"][$index]->already_call = htmlentities($row->already_call);
                    $index++;
                }
                if(!empty($output["datanya"])){
                    $no =0;
                    foreach($output["datanya"] as $key => $value){
                        $no++;
                        $output["table"] .= "<tr>";
                        $output["table"] .= "<td class=''>".htmlentities($no)."</td>";
                        $output["table"] .= "<td class=''>".htmlentities($value->cluster_name)."</td>";
                        $output["table"] .= "<td class=''>".htmlentities($value->total_msisdn)."</td>";
                        if($value->total_notyet_call==0){
                            $output["table"] .= "<td class='' style='background:red;'>".htmlentities($value->total_notyet_call)."</td>";
                        }else{
                            $output["table"] .= "<td class='' style='background:white;'>".htmlentities($value->total_notyet_call)."</td>";
                        }
                        $output["table"] .= "<td class=''>".htmlentities($value->already_call)."</td>";
                        $output["table"] .= "</tr>";
                        }
                    }
                    else{
                    $output["table"] .= "<tr>";
                    $output["table"] .= "<td class='align-center' colspan='5'>Data Kosong</td>";
                    $output["table"] .= "</tr>";
                    }
                break;

            default:
                return $this->core->setResponse("not_found", ["Flag type tidak ada"]);

        }

        $output["type"] =  preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlentities(htmlspecialchars($request->ftype)));

        return $this->core->setResponse("success", $output);
    }

    public function save_adm_menu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namaMenu'  => 'required',
            'iconMenu'  => 'required',
            'urlMenu'   => 'required',
            'levelMenu' => 'required',
            'parentMenu'=> 'required',
            'typeMenu'  => 'required',
            'orderMenu' => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'nama_menu'     => $request->input('namaMenu'),
                'icon_menu'     => $request->input('iconMenu'),
                'url_menu'      => $request->input('urlMenu'),
                'level_menu'    => $request->input('levelMenu'),
                'parent_menu'   => $request->input('parentMenu'),
                'type_menu'     => $request->input('typeMenu'),
                'target_menu'   => $request->input('targetMenu'),
                'order_menu'    => $request->input('orderMenu'),
                'color_menu'    => $request->input('colorMenu'),
                'authorized_roles'  => $request->input('authorized'),
                'status_menu'       => 'active'
            ];

            $query = DB::connection("mysqlChamber")->table('boopati_menu')->insert($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data Menu telah ditambahkan"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal ditambahkan"]);
            }

        }
    }

    public function update_adm_menu(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'namaMenu'  => 'required',
            'iconMenu'  => 'required',
            'urlMenu'   => 'required',
            'levelMenu' => 'required',
            'parentMenu'=> 'required',
            'typeMenu'  => 'required',
            'orderMenu' => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'nama_menu'     => $request->input('namaMenu'),
                'icon_menu'     => $request->input('iconMenu'),
                'url_menu'      => $request->input('urlMenu'),
                'level_menu'    => $request->input('levelMenu'),
                'parent_menu'   => $request->input('parentMenu'),
                'type_menu'     => $request->input('typeMenu'),
                'color_menu'    => $request->input('colorMenu'),
                'order_menu'    => $request->input('orderMenu'),
                'target_menu'   => $request->input('targetMenu'),
                'status_menu'   => $request->input('status_menu'),
                'authorized_roles' => $request->input('authorized')
            ];

            $query = DB::connection("mysqlChamber")->table("boopati_menu")
                        ->where("id_menu", $id)
                        ->update($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data Menu telah diupdate"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal diupdate"]);
            }

        }

    }

    public function delete_adm_menu($id)
    {
        $query =  DB::connection("mysqlChamber")->table('boopati_menu')->where('id_menu','=',$id)->delete();

        if ($query) {
            return $this->core->setResponse("success", ['info' => "Data berhasil dihapus"]);
        } else {
            return $this->core->setResponse("error", ['info' => "Data gagal dihapus"]);
        }
    }

    public function save_adm_loader(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namaLoader'    => 'required',
            'sessionLoader' => 'required',
            'permission'    => 'required',
            'pathLoader'    => 'required',
            'titleLoader'   => 'required',
            'username'      => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'nama_loader'   => $request->input('namaLoader'),
                'session_loader'=> $request->input('sessionLoader'),
                'permission'    => $request->input('permission'),
                'filepage'      => $request->input('pathLoader'),
                'title'         => $request->input('titleLoader'),
                'created_by'    => $request->input('username'),
                'created_date'  => Carbon::now()->timezone('Asia/Jakarta')
            ];

            $query = DB::connection("mysqlChamber")->table('boopati_loader')->insert($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data Loader telah ditambahkan"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal ditambahkan"]);
            }

        }
    }

    public function update_adm_loader(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'namaLoader'    => 'required',
            'sessionLoader' => 'required',
            'permission'    => 'required',
            'pathLoader'    => 'required',
            'titleLoader'   => 'required'
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'nama_loader'   => $request->input('namaLoader'),
                'session_loader'=> $request->input('sessionLoader'),
                'permission'    => $request->input('permission'),
                'filepage'      => $request->input('pathLoader'),
                'title'         => $request->input('titleLoader')
            ];

            $query = DB::connection("mysqlChamber")->table("boopati_loader")
                        ->where("id_loader", $id)
                        ->update($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data Loader telah diupdate"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal diupdate"]);
            }

        }

    }

    public function delete_adm_loader($id)
    {
        $query =  DB::connection("mysqlChamber")->table('boopati_loader')->where('id_loader','=',$id)->delete();

        if ($query) {
            return $this->core->setResponse("success", ['info' => "Data berhasil dihapus"]);
        } else {
            return $this->core->setResponse("error", ['info' => "Data gagal dihapus"]);
        }
    }

    public function boopati_loader()
    {
        $query = DB::connection("mysqlChamber")->table('boopati_loader')->get();

        if (count($query) == 0) {
            return $this->core->setResponse("not_found", ['info' => "Table Empty"]);
        } else {
            return $this->core->setResponse("success", $query);
        }
    }

    public function chamber_menu()
    {
        $query0 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=0 order by order_menu");
        $data0 = $query0;

        $txt = '';

        foreach($data0 as $val0){
            // $dt[] = showrowmenutable(0,'#8a959b');
            $kun = '';
            for($i=0;$i<0;$i++){
                $kun = $kun.'>';
            }

            $txt .= "<tr bgcolor=''#8a959b''>";
            $txt .= "  <td>".$val0->id_menu."</td>";
            $txt .= "  <td>".$val0->order_menu."</td>";
            $txt .= "  <td>$kun ".$val0->nama_menu."</td>";
            $txt .= "  <td>".$val0->icon_menu."</td>";
            $txt .= "  <td>".$val0->color_menu."</td>";
            $txt .= "  <td>".$val0->url_menu."</td>";
            $txt .= "  <td>".$val0->level_menu."</td>";
            $txt .= "  <td>".$val0->parent_menu."</td>";
            $txt .= "  <td>".$val0->type_menu."</td>";
            $txt .= "  <td>".$val0->target_menu."</td>";
            $txt .= "  <td>".$val0->status_menu."</td>";
            $nextlevel = $val0->level_menu + 1;
            $txt .= "  <td><a class='btn btn-primary btn-sm' onclick=\"addsub('".$val0->id_menu."','".$nextlevel."')\">add</a> | <a  class='btn btn-success btn-sm' onclick=\"editsub('".$val0->id_menu."','".$val0->nama_menu."','".$val0->icon_menu."','".$val0->url_menu."','".$val0->level_menu."','".$val0->parent_menu."','".$val0->type_menu."','".$val0->target_menu."','".$val0->color_menu."','".$val0->order_menu."','".$val0->authorized_roles."', '".$val0->status_menu."')\">edit</a> | <a  class='btn btn-danger btn-sm' onclick=\"deletesub('".$val0->id_menu."','".$val0->nama_menu."','".$val0->icon_menu."','".$val0->url_menu."','".$val0->level_menu."','".$val0->parent_menu."','".$val0->type_menu."','".$val0->target_menu."','".$val0->color_menu."','".$val0->order_menu."','".$val0->authorized_roles."', '".$val0->status_menu."')\">delete</a></td>";
            $txt .= "</tr>";
            $query1 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=1 and parent_menu=$val0->id_menu order by order_menu");
            $data1 = $query1;
            foreach($data1 as $val1){

                $kun = '';
                for($i=0;$i<1;$i++){
                    $kun = $kun.'>';
                }

                $txt .= "<tr bgcolor='#a5b2ba'>";
                $txt .= "  <td>".$val1->id_menu."</td>";
                $txt .= "  <td>".$val1->order_menu."</td>";
                $txt .= "  <td>$kun ".$val1->nama_menu."</td>";
                $txt .= "  <td>".$val1->icon_menu."</td>";
                $txt .= "  <td>".$val1->color_menu."</td>";
                $txt .= "  <td>".$val1->url_menu."</td>";
                $txt .= "  <td>".$val1->level_menu."</td>";
                $txt .= "  <td>".$val1->parent_menu."</td>";
                $txt .= "  <td>".$val1->type_menu."</td>";
                $txt .= "  <td>".$val1->target_menu."</td>";
                $txt .= "  <td>".$val1->status_menu."</td>";
                $nextlevel = $val1->level_menu + 1;
                $txt .= "  <td><a class='btn btn-primary btn-sm' onclick=\"addsub('".$val1->id_menu."','".$nextlevel."')\">add</a> | <a  class='btn btn-success btn-sm' onclick=\"editsub('".$val1->id_menu."','".$val1->nama_menu."','".$val1->icon_menu."','".$val1->url_menu."','".$val1->level_menu."','".$val1->parent_menu."','".$val1->type_menu."','".$val1->target_menu."','".$val1->color_menu."','".$val1->order_menu."','".$val1->authorized_roles."', '".$val1->status_menu."')\">edit</a> | <a  class='btn btn-danger btn-sm' onclick=\"deletesub('".$val1->id_menu."','".$val1->nama_menu."','".$val1->icon_menu."','".$val1->url_menu."','".$val1->level_menu."','".$val1->parent_menu."','".$val1->type_menu."','".$val1->target_menu."','".$val1->color_menu."','".$val1->order_menu."','".$val1->authorized_roles."', '".$val1->status_menu."')\">delete</a></td>";
                $txt .= "</tr>";

                $query2 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=2 and parent_menu=$val1->id_menu order by order_menu");
                $data2 = $query2;
                foreach($data2 as $val2){

                    $kun = '';
                    for($i=0;$i<2;$i++){
                        $kun = $kun.'>';
                    }

                    $txt .= "<tr bgcolor='#b3c1c9'>";
                    $txt .= "  <td>".$val2->id_menu."</td>";
                    $txt .= "  <td>".$val2->order_menu."</td>";
                    $txt .= "  <td>$kun ".$val2->nama_menu."</td>";
                    $txt .= "  <td>".$val2->icon_menu."</td>";
                    $txt .= "  <td>".$val2->color_menu."</td>";
                    $txt .= "  <td>".$val2->url_menu."</td>";
                    $txt .= "  <td>".$val2->level_menu."</td>";
                    $txt .= "  <td>".$val2->parent_menu."</td>";
                    $txt .= "  <td>".$val2->type_menu."</td>";
                    $txt .= "  <td>".$val2->target_menu."</td>";
                    $txt .= "  <td>".$val2->status_menu."</td>";
                    $nextlevel = $val2->level_menu + 1;
                    $txt .= "  <td><a class='btn btn-primary btn-sm' onclick=\"addsub('".$val2->id_menu."','".$nextlevel."')\">add</a> | <a  class='btn btn-success btn-sm' onclick=\"editsub('".$val2->id_menu."','".$val2->nama_menu."','".$val2->icon_menu."','".$val2->url_menu."','".$val2->level_menu."','".$val2->parent_menu."','".$val2->type_menu."','".$val2->target_menu."','".$val2->color_menu."','".$val2->order_menu."','".$val2->authorized_roles."', '".$val2->status_menu."')\">edit</a> | <a  class='btn btn-danger btn-sm' onclick=\"deletesub('".$val2->id_menu."','".$val2->nama_menu."','".$val2->icon_menu."','".$val2->url_menu."','".$val2->level_menu."','".$val2->parent_menu."','".$val2->type_menu."','".$val2->target_menu."','".$val2->color_menu."','".$val2->order_menu."','".$val2->authorized_roles."', '".$val2->status_menu."')\">delete</a></td>";
                    $txt .= "</tr>";
                    $query3 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=3 and parent_menu=$val2->id_menu order by order_menu");
                    $data3 = $query3;
                    foreach($data3 as $val3){

                        $kun = '';
                        for($i=0;$i<3;$i++){
                            $kun = $kun.'>';
                        }

                        $txt .= "<tr bgcolor='#cad6df'>";
                        $txt .= "  <td>".$val3->id_menu."</td>";
                        $txt .= "  <td>".$val3->order_menu."</td>";
                        $txt .= "  <td>$kun ".$val3->nama_menu."</td>";
                        $txt .= "  <td>".$val3->icon_menu."</td>";
                        $txt .= "  <td>".$val3->color_menu."</td>";
                        $txt .= "  <td>".$val3->url_menu."</td>";
                        $txt .= "  <td>".$val3->level_menu."</td>";
                        $txt .= "  <td>".$val3->parent_menu."</td>";
                        $txt .= "  <td>".$val3->type_menu."</td>";
                        $txt .= "  <td>".$val3->target_menu."</td>";
                        $txt .= "  <td>".$val3->status_menu."</td>";
                        $nextlevel = $val3->level_menu + 1;
                        $txt .= "  <td><a class='btn btn-primary btn-sm' onclick=\"addsub('".$val3->id_menu."','".$nextlevel."')\">add</a> | <a  class='btn btn-success btn-sm' onclick=\"editsub('".$val3->id_menu."','".$val3->nama_menu."','".$val3->icon_menu."','".$val3->url_menu."','".$val3->level_menu."','".$val3->parent_menu."','".$val3->type_menu."','".$val3->target_menu."','".$val3->color_menu."','".$val3->order_menu."','".$val3->authorized_roles."', '".$val3->status_menu."')\">edit</a> | <a  class='btn btn-danger btn-sm' onclick=\"deletesub('".$val3->id_menu."','".$val3->nama_menu."','".$val3->icon_menu."','".$val3->url_menu."','".$val3->level_menu."','".$val3->parent_menu."','".$val3->type_menu."','".$val3->target_menu."','".$val3->color_menu."','".$val3->order_menu."','".$val3->authorized_roles."', '".$val3->status_menu."')\">delete</a></td>";
                        $txt .= "</tr>";

                        $query4 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=4 and parent_menu=$val3->id_menu order by order_menu");
                        $data4 = $query4;
                        foreach($data4 as $val4){

                            $kun = '';
                            for($i=0;$i<4;$i++){
                                $kun = $kun.'>';
                            }

                            $txt .= "<tr bgcolor='#dbe4ee'>";
                            $txt .= "  <td>".$val4->id_menu."</td>";
                            $txt .= "  <td>".$val4->order_menu."</td>";
                            $txt .= "  <td>$kun ".$val4->nama_menu."</td>";
                            $txt .= "  <td>".$val4->icon_menu."</td>";
                            $txt .= "  <td>".$val4->color_menu."</td>";
                            $txt .= "  <td>".$val4->url_menu."</td>";
                            $txt .= "  <td>".$val4->level_menu."</td>";
                            $txt .= "  <td>".$val4->parent_menu."</td>";
                            $txt .= "  <td>".$val4->type_menu."</td>";
                            $txt .= "  <td>".$val4->target_menu."</td>";
                            $txt .= "  <td>".$val4->status_menu."</td>";
                            $nextlevel = $val4->level_menu + 1;
                            $txt .= "  <td><a class='btn btn-primary btn-sm' onclick=\"addsub('".$val4->id_menu."','".$nextlevel."')\">add</a> | <a  class='btn btn-success btn-sm' onclick=\"editsub('".$val4->id_menu."','".$val4->nama_menu."','".$val4->icon_menu."','".$val4->url_menu."','".$val4->level_menu."','".$val4->parent_menu."','".$val4->type_menu."','".$val4->target_menu."','".$val4->color_menu."','".$val4->order_menu."','".$val4->authorized_roles."', '".$val4->status_menu."')\">edit</a> | <a  class='btn btn-danger btn-sm' onclick=\"deletesub('".$val4->id_menu."','".$val4->nama_menu."','".$val4->icon_menu."','".$val4->url_menu."','".$val4->level_menu."','".$val4->parent_menu."','".$val4->type_menu."','".$val4->target_menu."','".$val4->color_menu."','".$val4->order_menu."','".$val4->authorized_roles."', '".$val4->status_menu."')\">delete</a></td>";
                            $txt .= "</tr>";

                            $query5 = DB::connection("mysqlChamber")->select("SELECT * from boopati_menu where level_menu=5 and parent_menu=$val4->id_menu order by order_menu");
                            $data5 = $query5;
                            foreach($data5 as $val5){
                                $kun = '';
                                for($i=0;$i<5;$i++){
                                    $kun = $kun.'>';
                                }

                                $txt .= "<tr bgcolor='#eef2f7'>";
                                $txt .= "  <td>".$val5->id_menu."</td>";
                                $txt .= "  <td>".$val5->order_menu."</td>";
                                $txt .= "  <td>$kun ".$val5->nama_menu."</td>";
                                $txt .= "  <td>".$val5->icon_menu."</td>";
                                $txt .= "  <td>".$val5->color_menu."</td>";
                                $txt .= "  <td>".$val5->url_menu."</td>";
                                $txt .= "  <td>".$val5->level_menu."</td>";
                                $txt .= "  <td>".$val5->parent_menu."</td>";
                                $txt .= "  <td>".$val5->type_menu."</td>";
                                $txt .= "  <td>".$val5->target_menu."</td>";
                                $txt .= "  <td>".$val5->status_menu."</td>";
                                $nextlevel = $val5->level_menu + 1;
                                $txt .= "  <td><a class='btn btn-primary btn-sm' onclick=\"addsub('".$val5->id_menu."','".$nextlevel."')\">add</a> | <a  class='btn btn-success btn-sm' onclick=\"editsub('".$val5->id_menu."','".$val5->nama_menu."','".$val5->icon_menu."','".$val5->url_menu."','".$val5->level_menu."','".$val5->parent_menu."','".$val5->type_menu."','".$val5->target_menu."','".$val5->color_menu."','".$val5->order_menu."','".$val5->authorized_roles."', '".$val5->status_menu."')\">edit</a> | <a  class='btn btn-danger btn-sm' onclick=\"deletesub('".$val5->id_menu."','".$val5->nama_menu."','".$val5->icon_menu."','".$val5->url_menu."','".$val5->level_menu."','".$val5->parent_menu."','".$val5->type_menu."','".$val5->target_menu."','".$val5->color_menu."','".$val5->order_menu."','".$val5->authorized_roles."', '".$val5->status_menu."')\">delete</a></td>";
                                $txt .= "</tr>";
                            }
                        }
                    }
                }
            }
        }

        return $this->core->setResponse("success", $txt);
    }

    public function history_login()
    {
        $query = DB::connection("mysqlChamber")->select("SELECT * from boopati_history where datetimelog >= now() - interval  7 day order by datetimelog desc");

        if (count($query) == 0) {
            return $this->core->setResponse("not_found", ['result' => "Data Empty"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function save_adm_userTDC(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usernametdc'   => 'required',
            'tdc_select'    => 'required',
            'rolestdc'      => 'required',
            'username'      => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'username'      => $request->input('usernametdc'),
                'password'      => "953e015fa874844b7af52f25c08d2255",
                'hint'          => "Telkomsel",
                'id_cluster'    => $request->input('tdc_select'),
                'roles'         => $request->input('rolestdc'),
                'created_date'  => Carbon::now()->timezone('Asia/Jakarta'),
                'created_by'    => $request->input('username'),
            ];

            $query = DB::connection("mysqlChamber")->table('users')->insert($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data User telah ditambahkan"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal ditambahkan"]);
            }

        }
    }

    public function update_adm_userTDC(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'usernametdc'   => 'required',
            'tdc_select'    => 'required',
            'rolestdc'      => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'username'      => $request->input('usernametdc'),
                'id_cluster'    => $request->input('tdc_select'),
                'roles'         => $request->input('rolestdc'),
            ];

            $query = DB::connection("mysqlChamber")->table("users")
                        ->where("id_users", $id)
                        ->update($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data User telah diupdate"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal diupdate"]);
            }

        }

    }

    public function delete_adm_userTDC($id)
    {
        $query =  DB::connection("mysqlChamber")->table('users')->where('id_users','=',$id)->delete();

        if ($query) {
            return $this->core->setResponse("success", ['info' => "Data berhasil dihapus"]);
        } else {
            return $this->core->setResponse("error", ['info' => "Data gagal dihapus"]);
        }
    }

    public function save_adm_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roles'         => 'required',
            'username'      => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            if ($request->input('roles') == 'su') {
                $data = [
                    'username'      => $request->input('username'),
                    'password'      => "953e015fa874844b7af52f25c08d2255",
                    'hint'          => "Telkomsel",
                    'roles'         => $request->input('rolestdc'),
                    'created_date'  => Carbon::now()->timezone('Asia/Jakarta'),
                    'created_by'    => $request->input('username'),
                ];
            } else {
                $data = [
                    'username'      => $request->input('username'),
                    'password'      => "953e015fa874844b7af52f25c08d2255",
                    'hint'          => "Telkomsel",
                    'id_cluster'    => $request->input('tdc_select'),
                    'roles'         => $request->input('rolestdc'),
                    'created_date'  => Carbon::now()->timezone('Asia/Jakarta'),
                    'created_by'    => $request->input('username'),
                ];
            }

            $query = DB::connection("mysqlChamber")->table('users')->insert($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data User telah ditambahkan"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal ditambahkan"]);
            }

        }
    }

    public function update_adm_user(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username'  => 'required',
            'roles'     => 'required',
            'id_cluster'=> 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'username'      => $request->input('username'),
                'roles'         => $request->input('roles'),
                'id_cluster'    => $request->input('id_cluster'),
            ];

            $query = DB::connection("mysqlChamber")->table("users")
                        ->where("id_users", $id)
                        ->update($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data User telah diupdate"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal diupdate"]);
            }

        }

    }

    public function delete_adm_user($id)
    {
        $query =  DB::connection("mysqlChamber")->table('users')->where('id_users','=',$id)->delete();

        if ($query) {
            return $this->core->setResponse("success", ['info' => "Data berhasil dihapus"]);
        } else {
            return $this->core->setResponse("error", ['info' => "Data gagal dihapus"]);
        }
    }

    public function change_password_user(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password'  => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $data = [
                'password'  => md5($request->input('password')),
                'hint'      => $request->input('password'),
            ];

            $query = DB::connection("mysqlChamber")->table("users")
                        ->where("id_users", $id)
                        ->update($data);

            if ($query) {
                return $this->core->setResponse("success", ['info' => "Data User telah diupdate"]);
            } else {
                return $this->core->setResponse("error", ['info' => "Data gagal diupdate"]);
            }

        }
    }

    public function list_user(Request $request)
    {
        $roles = $request->roles;

        if ($roles == '') {
            return $this->core->setResponse("error", ['roles' => "Parameter roles harus diisi!"]);
        }

        if ($roles == 'cluster') {
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM users a JOIN cluster b ON (a.id_cluster = b.id_cluster) WHERE roles = 'cluster'");
        } elseif ($roles == 'su' || $roles == 'branch') {
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM users WHERE roles = '$roles'");
        } else {
            return $this->core->setResponse("not_found", ['roles' => "Roles tidak ditemukan"]);
        }

        if (count($query) == 0) {
            return $this->core->setResponse("error", ['result' => "Data Empty!"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function list_user_tdc(Request $request)
    {
        $roles      = $request->roles;
        $id_cluster = $request->id_cluster;

        if ($roles == '') {
            return $this->core->setResponse("error", ['roles' => "Parameter roles harus diisi!"]);
        }

        if($roles == 'su'){
            $query = DB::connection("mysqlChamber")->select("SELECT a.id_users,a.username,a.roles,b.id_tdc ,b.tdc,b.id_cluster,c.cluster_name,c.id_branch,d.branch_name,a.created_by,a.created_date, a.hint
            from users a,tdc b,cluster c,branch d
            where a.roles='tdc' and a.id_cluster=b.id_tdc and b.id_cluster=c.id_cluster and c.id_branch=d.id_branch");

        } else {
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM users a LEFT JOIN tdc b ON (a.id_cluster = b.id_tdc) WHERE a.roles = 'tdc' AND b.id_cluster = '$id_cluster'");

        }

        if (count($query) == 0) {
            return $this->core->setResponse("error", ['result' => "Data Empty!"]);
        }

        return $this->core->setResponse("success", $query);
    }

    public function adm_get_branch(Request $request)
    {
        $id_region = $request->id_region;

        if($id_region == ''){
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM branch");
        } else {
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM branch WHERE id_region = '$id_region'");
        }

        if (count($query) == 0) {
            return $this->core->setResponse("error", ['result' => "Data Empty!"]);
        }

        $output = array();
        foreach ($query as $result) {
            $output['result'][] = array(
                'id_branch'     => htmlentities($result->id_branch),
                'branch_name'   => htmlentities($result->branch_name),
                'id_region'     => htmlentities($result->id_region)
            );
        }


        return $this->core->setResponse("success", $output);
    }

    public function adm_get_cluster(Request $request)
    {
        $id_branch = $request->id_branch;

        if($id_branch == ''){
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM cluster");
        } else {
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM cluster WHERE id_branch = '$id_branch'");
        }

        if (count($query) == 0) {
            return $this->core->setResponse("error", ['result' => "Data Empty!"]);
        }

        $output = array();
        foreach ($query as $result) {
            $output['result'][] = array(
                'id_cluster'    => htmlentities($result->id_cluster),
                'cluster_name'  => htmlentities($result->cluster_name),
                'id_branch'     => htmlentities($result->id_branch)
            );
        }

        return $this->core->setResponse("success", $output);
    }

    public function adm_get_tdc_id(Request $request)
    {
        $tmp_idtdc = $request->id_tdc;

        if($tmp_idtdc == ''){
            $query = "";

        } else {
            $query = DB::connection("mysqlChamber")->select("SELECT b.id_tdc,b.tdc,b.id_cluster,c.cluster_name,c.id_branch,d.branch_name,e.*
            from tdc b,cluster c,branch d,region e
            where b.id_cluster=c.id_cluster and c.id_branch=d.id_branch and d.id_region=e.id_region and b.id_tdc='$tmp_idtdc'");

        }

        $output = array();
        foreach ($query as $result) {
            $idregion   = htmlentities($result->id_region);
            $idbranch   = htmlentities($result->id_branch);
            $idcluster  = htmlentities($result->id_cluster);
            $idtdc      = htmlentities($result->id_tdc);
            $output['result'][] = $result;
        }

        //collect region select form base on tdc id
        $query = DB::connection("mysqlChamber")->select("SELECT * from region where id_region='$idregion'");

        $txtregion = '';
        foreach ($query as $result) {
            $txtregion .= '<option value="'.htmlentities($result->id_region).'">'.htmlentities($result->regional).'</option>';
        }
        //collect branch select form base on tdc id
        $query = DB::connection("mysqlChamber")->select("SELECT * from branch where id_region='$idregion'");

        $txtbranch = '';
        foreach ($query as $result) {
            if($result->id_branch==$idbranch){
                $sel = "selected";
            }else{
                $sel = "";
            }
            $txtbranch .= '<option value="'.htmlentities($result->id_branch).'" '.$sel.'>'.htmlentities($result->branch_name).'</option>';
        }
        //collect cluster select form base on tdc id
        $query = DB::connection("mysqlChamber")->select("SELECT * from cluster where id_branch='$idbranch'");

        $txtcluster = '';
        foreach ($query as $result) {
            if($result->id_cluster==$idcluster){
                $sel = "selected";
            }else{
                $sel = "";
            }
            $txtcluster .= '<option value="'.htmlentities($result->id_cluster).'" '.$sel.'>'.htmlentities($result->cluster_name).'</option>';
        }
        //collect tdc select form base on tdc id
        $query = DB::connection("mysqlChamber")->select("SELECT * from tdc where id_cluster='$idcluster'");

        $txttdc = '';
        foreach ($query as $result) {
            if($result->id_tdc==$idtdc){
                $sel = "selected";
            }else{
                $sel = "";
            }
            $txttdc .= '<option value="'.htmlentities($result->id_tdc).'" '.$sel.'>'.htmlentities($result->tdc).'</option>';
        }
        $output['result']['selectregion']=htmlentities($txtregion);
        $output['result']['selectbranch']=htmlentities($txtbranch);
        $output['result']['selectcluster']=htmlentities($txtcluster);
        $output['result']['selecttdc']=htmlentities($txttdc);

        return $this->core->setResponse("success", $output);
    }

    public function adm_get_tdc(Request $request)
    {
        $id_cluster = $request->id_cluster;

        if($id_cluster == ''){
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM tdc");
        } else {
            $query = DB::connection("mysqlChamber")->select("SELECT * FROM tdc WHERE id_cluster = '$id_cluster'");
        }

        if (count($query) == 0) {
            return $this->core->setResponse("error", ['result' => "Data Empty!"]);
        }

        $output = array();
        foreach ($query as $result) {
            $output['result'][] = $result;
        }

        return $this->core->setResponse("success", $output);
    }

    public function save_table_map(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $file           = $request->file('upload_file');
        $roles          = $request->roles;
        $table_name     = $request->table_name;
        $keyword_page   = $request->keyword_page;
        $flag_page      = $request->flag_page;
        $header_page    = $request->header_page;
        $status_table   = $request->status_table;
        $username       = $request->username;

        $file_name      = "";

        if (!empty($file))
        {
            $file_size  = $file->getSize();
            $max_size	= 500000000; //5mb
            if ($file_size > $max_size) {
                return $this->core->setResponse("error", ['info' => "File max size 5 mb"]);
            }

            $file_oriname   = $file->getClientOriginalName();
            $extension      = pathinfo($file_oriname,PATHINFO_EXTENSION);
            $extension      = strtolower($extension);

            $allowed_extensions = array("png, jpg, jpeg");

            if(!in_array(strtolower($extension), $allowed_extensions)) {
                return $this->core->setResponse("error", ['type' => "Format file harus png | jpg | jpeg"]);
            }

            $file->move(storage_path('img_chamber'), $file_oriname);

            $file_name	= preg_replace("/[^a-zA-Z0-9]+/", "", "img_" . preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', basename(htmlspecialchars($keyword_page))) . "." . $extension);
			$file_name	= str_replace("/", "", $file_name);
			$file_name	= str_replace("..", "", $file_name);
			$file_name	= str_replace("etc", "", $file_name);
			$file_name	= str_replace("00", "", $file_name);
        }

        DB::connection("mysqlChamber")->beginTransaction();

        try {

            $data = [
                'roles'         => $roles,
                'table_name'    => $table_name,
                'keyword_page'  => $keyword_page,
                'flag_page'     => $flag_page,
                'header_page'   => $header_page,
                'image_table'   => $file_name,
                'status_table'  => $status_table,
                'created_date'  => Carbon::now()->timezone('Asia/Jakarta'),
                'created_by'    => $username,
                ];
            $query = DB::connection("mysqlChamber")->table('boopati_table_mapped_by_region')->insert($data);

            $doquery = $query;
            $id =  DB::getPdo()->lastInsertId();

            $data2 = [
                'nama_loader'    => "main/listtarget/$keyword_page",
                'session_loader' => "roles",
                'permission'     => "su;users;tdc;cluster",
                'filepage'       => "./page/main/listmsisdntarget.php",
                'title'          => "Target Call $header_page",
                'created_by'     => $username,
                'created_date'   => Carbon::now()->timezone('Asia/Jakarta'),
            ];
            $query = DB::connection("mysqlChamber")->table('boopati_loader')->insert($data2);

            $data3 = [
                'nama_menu'      => "List $header_page",
                'icon_menu'      => "fa-phone",
                'url_menu'       => "main/listtarget/$keyword_page",
                'level_menu'     => "1",
                'parent_menu'    => "4",
                'type_menu'      => "link",
                'target_menu'    => "_self",
                'order_menu'     => "$id",
                'color_menu'     => "text-green",
                'authorized_roles' => "clusters;tdc",
                'status_menu'    => "active",
            ];
            $query = DB::connection("mysqlChamber")->table('boopati_menu')->insert($data3);

            $data4 = [
                'nama_loader'    => "achievement/$keyword_page",
                'session_loader' => "roles",
                'permission'     => "su",
                'filepage'       => "./page/achievement.php",
                'title'          => "Achievement $header_page",
                'created_by'     => $username,
                'created_date'   => Carbon::now()->timezone('Asia/Jakarta'),
            ];
            $query = DB::connection("mysqlChamber")->table('boopati_loader')->insert($data4);

            $data5 = [
                'nama_menu'      => "Ach All $header_page",
                'icon_menu'      => "fa-phone",
                'url_menu'       => "achievement/$keyword_page",
                'level_menu'     => "1",
                'parent_menu'    => "10",
                'type_menu'      => "link",
                'target_menu'    => "_self",
                'order_menu'     => "$id",
                'color_menu'     => "",
                'authorized_roles'=> "su",
                'status_menu'   => "active",
            ];
            $query = DB::connection("mysqlChamber")->table('boopati_menu')->insert($data5);

            if (count($query) == 0) {
                DB::connection("mysqlChamber")->commit();
                return $this->core->setResponse("success", ['info' => "Data Berhasil Ditambah"]);
            }


        } catch (\Throwable $th) {
            DB::connection("mysqlChamber")->rollback();

            return $this->core->setResponse("error", ['info' => "Gagal simpan ke database"]);
        }

    }

    public function update_table_map(Request $request, $id)
    {
        $file           = $request->file('upload_file');
        $roles          = $request->roles;
        $table_name     = $request->table_name;
        $keyword_page   = $request->keyword_page;
        $flag_page      = $request->flag_page;
        $header_page    = $request->header_page;
        $status_table   = $request->status_table;

        $file_name      = "";

        if (!empty($file))
        {
            $file_size  = $file->getSize();
            $max_size	= 500000000; //5mb
            if ($file_size > $max_size) {
                return $this->core->setResponse("error", ['info' => "File max size 5 mb"]);
            }

            $file_oriname   = $file->getClientOriginalName();
            $extension      = pathinfo($file_oriname,PATHINFO_EXTENSION);
            $extension      = strtolower($extension);

            $allowed_extensions = array("png, jpg, jpeg");

            if(!in_array(strtolower($extension), $allowed_extensions)) {
                return $this->core->setResponse("error", ['type' => "Format file harus png | jpg | jpeg"]);
            }

            $file->move(storage_path('img_chamber'), $file_oriname);

            $file_name	= preg_replace("/[^a-zA-Z0-9]+/", "", "img_" . preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', basename(htmlspecialchars($keyword_page))) . "." . $extension);
			$file_name	= str_replace("/", "", $file_name);
			$file_name	= str_replace("..", "", $file_name);
			$file_name	= str_replace("etc", "", $file_name);
			$file_name	= str_replace("00", "", $file_name);
        }

        $data = [
            'roles'         => $roles,
            'table_name'    => $table_name,
            'keyword_page'  => $keyword_page,
            'flag_page'     => $flag_page,
            'header_page'   => $header_page,
            'image_table'   => $file_name,
            'status_table'  => $status_table
            ];

        $query = DB::connection("mysqlChamber")->table("boopati_table_mapped_by_region")
                        ->where("id_boopati_table_mapped_by_region", $id)
                        ->update($data);

        if (count($query) == 0) {
            return $this->core->setResponse("success", ['result' => "Data Berhasil Ditambah"]);
        }

    }

    public function delete_table_map($id)
    {
        $query =  DB::connection("mysqlChamber")->table('boopati_table_mapped_by_region')->where('id_boopati_table_mapped_by_region','=',$id)->delete();

        if ($query) {
            return $this->core->setResponse("success", ['info' => "Data berhasil dihapus"]);
        } else {
            return $this->core->setResponse("error", ['info' => "Data gagal dihapus"]);
        }
    }

    public function list_table_map()
    {
        $query =  DB::connection("mysqlChamber")->table('boopati_table_mapped_by_region')->get();

        if (count($query) != 0) {
            return $this->core->setResponse("success", $query);
        } else {
            return $this->core->setResponse("not_found", ['info' => "Data Empty"]);
        }
    }

    public function list_flag()
    {
        $query =  DB::connection("mysqlChamber")->select('SELECT DISTINCT flag FROM wl_wabranch1');

        if (count($query) != 0) {
            return $this->core->setResponse("success", $query);
        } else {
            return $this->core->setResponse("not_found", ['info' => "Data Empty"]);
        }
    }

    public function count_msisdn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_obc_msisdn' => 'required',
            'branch_name'      => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            $table_obc_msisdn   = $request->table_obc_msisdn;
            $branch_name        = $request->branch_name;

            $query = DB::connection("mysqlChamber")->select("SELECT COUNT(*) AS count_msisdn FROM ".$table_obc_msisdn." WHERE branch_lacci = '".$branch_name."' AND temp_user IS NULL AND remark_claim IS NULL");

            if (count($query) == 0) {
                return $this->core->setResponse("not_found", ["result" => "Data Empty"]);
            } else {
                return $this->core->setResponse("success", $query);
            }

        }

    }

    public function hitung_retrieve_wb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_users'          => 'required',
            'table_obc_msisdn'  => 'required',
            'branch_name'       => 'required',
            'username'          => 'required',
        ]);

        if ($validator->fails()) {

            return $this->core->setResponse("error", ["info" => "Semua Kolom Wajib Diisi!"]);

        } else {

            DB::connection("mysqlChamber")->beginTransaction();

            try {

                $id_users 		    = $request->input('id_users');
                $username 		    = $request->input('username');
                $table_obc_msisdn   = $request->input('table_obc_msisdn');
                $branch_name        = $request->input('branch_name');
                $tanggal  		    = Carbon::now()->timezone("Asia/Jakarta")->format('Y-m-d');
                $datetime  		    = Carbon::now()->timezone("Asia/Jakarta");

                // insert history
                $query_history 	= DB::connection("mysqlChamber")->select("INSERT INTO boopati_history(username,activity,status,errorcode,datetimelog) VALUES (?,?,?,?,?)", [$username,
                "Retrieve Data for $id_users OBC",'Begin',' ',$datetime]);

                // print_r($query_history); exit;

                $kondisi =  "branch_lacci = '".$branch_name."' AND temp_user IS NULL AND remark_claim IS NULL LIMIT 50000";

                // update wl
                $j      = 0;
                $q2     = DB::connection("mysqlChamber")->select("SELECT * from ".$table_obc_msisdn." where $kondisi");
                $query2 = $q2;

                foreach($query2 as $query3){
                    $msisdn         = $query3->msisdn;
                    $query_update   = DB::connection("mysqlChamber")->select("UPDATE ".$table_obc_msisdn." set temp_user = '".$id_users."', temp_date='".$tanggal."' where msisdn='".$msisdn."'");
                    $j++;
                }

                $query_history = DB::connection("mysqlChamber")->select("INSERT into boopati_history(username,activity,status,errorcode,datetimelog) values (?,?,?,?,?)", [$username,"Retrieve Data with $j attempt OBC",'Sukses',' ', $datetime]);

                DB::connection("mysqlChamber")->commit();

                return $this->core->setResponse("success", "Sukses proses data");

            } catch (\Throwable $th) {
                DB::connection("mysqlChamber")->rollback();

                return $this->core->setResponse("error", "Gagal ambil data");
            }
        }

    }

    public function save_claim_wb(Request $request)
    {
        $id_users       = $request->input('id_users');
        $msisdnkirim    = $request->input('msisdnkirim');
        $hasil          = $request->input('hasil');
        $catatan        = $request->input('catatan');
        $flag           = $request->input('flag');
        $table_obc_msisdn   = $request->input('table_obc_msisdn');
        $brand          = $request->input('brand');
        $jamklik        = $request->input('jamklik');

        DB::connection("mysqlChamber")->beginTransaction();

        try {
            $query = DB::connection("mysqlChamber")->select("INSERT into boopati_whitelist_claim (id_users,msisdn,status_claim,keterangan,tanggal_claim,datetime_claim, flag, table_name, brand, datetime_open_form) values ('".$id_users."','".$msisdnkirim."','".$hasil."','".$catatan."',now(),now(), '".$flag."', '".$table_obc_msisdn."', '".$brand."', '".$jamklik."')");

            $query2 = DB::connection("mysqlChamber")->select("UPDATE ".$table_obc_msisdn." set remark_claim='".$id_users."', status_telepon='".$hasil."' where msisdn='".$msisdnkirim."'");

            DB::connection("mysqlChamber")->commit();

            return $this->core->setResponse("success", ['info' => "Data sukses diproses"]);

        } catch (\Throwable $th) {
            DB::connection("mysqlChamber")->rollback();

            return $this->core->setResponse("error", ['info' => "Gagal diproses"]);
        }

    }

    public function update_claim_wb(Request $request)
    {
        $id_users       = $request->input('id_users');
        $msisdnkirim    = $request->input('msisdnkirim');
        $hasil          = $request->input('hasil');
        $catatan        = $request->input('catatan');
        $jamklik        = $request->input('jamklik');

        $query = DB::connection("mysqlChamber")->select("UPDATE boopati_whitelist_claim set status_claim='".$hasil."',keterangan = '".$catatan."',tanggal_claim = now(), datetime_claim=now(), datetime_open_form = '".$jamklik."' where msisdn='".$msisdnkirim."' and id_users='".$id_users."'");

        return $this->core->setResponse("success", ['info' => "Data sukses diproses"]);
    }

    public function list_region()
    {
        $query =  DB::connection("mysqlChamber")->select('SELECT * FROM region');

        if (count($query) != 0) {
            return $this->core->setResponse("success", $query);
        } else {
            return $this->core->setResponse("not_found", ['info' => "Data Empty"]);
        }
    }

    public function list_cluster_all()
    {
        $query =  DB::connection("mysqlChamber")->select('SELECT * FROM cluster');

        if (count($query) != 0) {
            return $this->core->setResponse("success", $query);
        } else {
            return $this->core->setResponse("not_found", ['info' => "Data Empty"]);
        }
    }

    public function insert_upload_wl_wb(Request $request)
    {
        $file           = $request->file('file_csv');
        $flag           = $request->flag;
        $id_region      = $request->id_region;
        $tap_user       = $request->tap_user;
        $table_name     = $request->table_name;
        $username       = $request->username;

        if ($file != '') {

            $file_oriname   = $file->getClientOriginalName();
            $file_size      = $file->getSize();
            $fileMimeType   = $file->getClientMimeType();
            $filename       = pathinfo($file_oriname, PATHINFO_FILENAME);
            $extension      = pathinfo($file_oriname, PATHINFO_EXTENSION);

            $tanggal        = date('Y-m-d');
            $waktu          = strtotime(date('H:i:s'));
            $file_import 	= str_replace('/','', preg_replace('/[\/<>]/', '', htmlspecialchars($file_oriname)));
            $flag 			= str_replace('/','', htmlspecialchars($flag));
            $id_region 		= str_replace('/','', htmlspecialchars($id_region));
            $tap_user 		= str_replace('/','', htmlspecialchars($tap_user));

            $eror		    = false;
            $max_upload     = false;
            $pesan          = '';
            $folder		    = 'file_upload/';

            $allowed_file = array('application/vnd.ms-excel','text/plain','text/csv');
            $max_size	= 10 * 1024 * 1024; // 10MB

			$file_size	    = $file_size;
			$extensi	    = $extension;

			$tanggal        = date('Ymd');
			$waktu          = date('His');
			$types          = $fileMimeType;

			$file_name	= str_replace('/','',$table_name."_".$tanggal."_".$waktu.".".$extensi);
			// $file_loc   = $folder.$file_name;

            $ext = $extension;
            if($ext == "csv" && in_array($types, array('text/csv', 'application/vnd.ms-excel'))){
                if($file_size <= $max_size){
                    if($file->move(storage_path('file_csv'), $file_oriname)){

                        $query_file_import = DB::connection("mysqlChamber")->select("INSERT into boopati_file_import (file_import,flag,id_region,table_name,created_by,created_date,read_status) values(
                            '".$file_name."',
                            '".$flag."',
                            '".$id_region."',
                            '".$table_name."',
                            '".$username."',
                        now(), '1')");

                        $id_file_import = DB::connection("mysqlChamber")->getPdo()->lastInsertId();
                        $doquery        = $query_file_import;

                        $msg = "Data telah ditambahkan";

                        $file_loc   = storage_path("/file_csv/$file_oriname");
                        $file       = fopen($file_loc, "r");

                        $count_insert_uploaded  = 0;
                        $count_not_uploaded     = 0;
                        $count_insert_claimed   = 0;

                        $delimiter = $this->detectDelimiter($file_loc);

                        $data_import = array();

                        // while(!feof($file)){
                        // fgets($file);  // read one line for nothing (skip header)
                        // while (($val = fgetcsv($file, 10000, $delimiter)) !== FALSE) {
                        //     // $data_import[] = explode($delimiter, fgetcsv($file,2000,$delimiter));
                        //     $data_import[] = explode($delimiter, fgetcsv($file, 10000, $delimiter));
                        // }

                        // $fileHeader = $data_import[0];
                        // // unset($data_import[0]);
                        // $total_data   = count($data_import);
                        // $total_insert = 0;



                        // foreach($data_import as $val){
                        fgets($file);  // read one line for nothing (skip header)
                        while (($val = fgetcsv($file, 10000, $delimiter)) !== FALSE) {

                            $tmp = date("Y-m-d", strtotime($val[1]));

                            // validasi template menghitung dari header
                            if(count($val) != 3 or strlen($val[0]) < 11 or strpos($val[0],'E+') !== false or $tmp=='1970-01-01' or $tmp < date("Y-m-d", strtotime("-3 weeks")) or $tmp > date("Y-m-d", strtotime("+1 weeks"))){ // validasi template false
                                unlink($file_loc); // remove file jika validasi gagal

                                return $this->core->setResponse("error", ['file' => 'Data gagal diupload. Format file / template tidak sesuai', 'alert' => 'danger']);

                            } else {

                                $valDate        = date("Y-m-d", strtotime($val[1]));
                                $msisdn         = str_replace(['\r', '"'], '', $val[0]);
                                $date_remark    = htmlspecialchars(date("Y-m-d", strtotime($val[1])));

                                if($msisdn != '' and strpos($val[0],'E+') == false and $valDate!='1970-01-01' and $valDate>date("Y-m-d", strtotime("-3 weeks")) and $valDate<date("Y-m-d", strtotime("+1 weeks"))){

                                    $q_claim = DB::connection("mysqlChamber")->select("SELECT * FROM boopati_whitelist_claim
                                    WHERE msisdn = '".$msisdn."' AND tanggal_claim = '".$date_remark."' AND flag = '".$flag."'");

                                    $count_check_claim = count($q_claim);
                                    $query_insert = "";

                                    if($count_check_claim == 0){ // blm terclaim
                                        $status_telepon   = str_replace(['\r', '\n', '"'], '', htmlspecialchars($val[2]));
                                        $date_remark      = htmlspecialchars(date("Y-m-d", strtotime($val[1])));

                                        $query_insert = DB::connection("mysqlChamber")->select("UPDATE $table_name set remark_claim='$tap_user', status_telepon='$status_telepon', date_upload = NOW() where msisdn='$msisdn'");

                                        $query_insert2    = DB::connection("mysqlChamber")->select("INSERT INTO boopati_whitelist_claim (msisdn, status_claim, tanggal_claim, datetime_claim, flag, table_name, id_users, id_file_import)
                                        VALUES('$msisdn', '$status_telepon', '$date_remark', '$date_remark', '$flag', '$table_name', '$tap_user', '$id_file_import')");

                                        $count_insert_uploaded++;

                                    }
                                    else { // sudah terclaim
                                        $count_insert_claimed++;
                                    }
                                }
                            }

                        }
                        $fix_return = DB::connection("mysqlChamber")->select("UPDATE boopati_whitelist_claim SET status_claim = REPLACE(REPLACE(status_claim, '\r', ''), '\n', '') WHERE flag = 'wabranch'");

                        $import_summary = DB::connection("mysqlChamber")->select("INSERT INTO boopati_file_import_summary (id_file_import, claimed, uploaded, date_upload)
                            VALUES('$id_file_import', '$count_insert_claimed', '$count_insert_uploaded', NOW())");

                        fclose($file);

                        if(count($import_summary) == 0){

                            return $this->core->setResponse("success", ['info' => 'Data telah ditambahkan.', 'alert' => 'success']);

                        } else {
                            unlink($file_loc);

                            return $this->core->setResponse("error", ['info' => 'Data gagal ditambahkan.', 'alert' => 'danger']);
                        }

                    } else {
                        return $this->core->setResponse("error", ['file_upload' => 'File gagal diupload', 'alert' => 'danger']);
                    }

                } else {
                    return $this->core->setResponse("error", ['file_size' => 'Maksimal Upload 10 MB', 'alert' => 'danger']);
                }

            } else {
                return $this->core->setResponse("error", ['format_file' => 'Format file harus .CSV', 'alert' => 'danger']);
            }

        } else {
            return $this->core->setResponse("error", ['file' => 'File Import harus diisi!', 'alert' => 'danger']);
        }

    }

    public function loader_users(Request $request)
    {
        $nama_loader = $request->nama_loader;

        if ($nama_loader == '') {
            $nama_loader = 'home';
        }

        $query = DB::connection("mysqlChamber")->table('boopati_loader')
                    ->where("nama_loader", $nama_loader)
                    ->get();

        if (count($query) == 0) {
            return $this->core->setResponse("not_found", ["info" => "Data Empty"]);
        } else {
            return $this->core->setResponse("success", $query);
        }
    }

}
