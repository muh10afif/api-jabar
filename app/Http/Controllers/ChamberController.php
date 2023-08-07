<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChamberController extends Controller
{
    public function cek_login(Request $request)
    {
        $username = $request->username;

        if ($username == ''){
            return Core::setResponse('error', ['username' => "username tidak boleh kosong"]);
        }

        $query_db = DB::connection("mysql")->select("SELECT *,
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
            return Core::setResponse('not_found', ['username' => "username tidak ditemukan"]);
        }

        $row = $query_db[0];

        if($row->roles == 'tdc'){
            $qi = DB::connection("mysql")->select("SELECT *
                FROM users a
                LEFT JOIN tdc b ON (a.id_cluster = b.id_tdc)
                LEFT JOIN cluster c ON (b.id_cluster = c.id_cluster)
                LEFT JOIN branch d ON (c.id_branch = d.id_branch)
                LEFT JOIN region e ON (d.id_region = e.id_region)
                WHERE a.username = '".$username."'
            ");

            $qtdc = DB::connection("mysql")->select("SELECT *
                FROM tdc a
                LEFT JOIN kecamatan b ON (a.id_tdc = b.id_tdc)
                LEFT JOIN kabupaten c ON (b.id_kabupaten = c.id_kabupaten)
                WHERE a.id_tdc = '".$row->id_cluster."'
            ");
            $data_tdc = $qtdc;

        } else {
            if($row->roles == 'mini_grapari'){
                $qi = DB::connection("mysql")->select("SELECT *
                    FROM users a
                    LEFT JOIN grapari_mini b ON (a.id_cluster = b.id_grapari)
                    WHERE a.username = '".$username."'");
            }
            elseif($row->roles == 'wareg'){
                $qi = DB::connection("mysql")->select("SELECT *
                    FROM users a
                    LEFT JOIN branch c ON (a.id_cluster = c.id_branch)
                    LEFT JOIN region d ON (c.id_region = d.id_region)
                    WHERE a.username = '".$username."'");
            }
            elseif($row->roles == 'branch'){
                $qi = DB::connection("mysql")->select("SELECT *
                    FROM users a
                    LEFT JOIN branch c ON (a.id_cluster = c.id_branch)
                    LEFT JOIN region d ON (c.id_region = d.id_region)
                    WHERE a.username = '".$username."'");
            }
            else {
                $qi = DB::connection("mysql")->select("SELECT *
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

        // $query_history = DB::connection("mysql")->select("insert into boopati_history(username,activity,status,errorcode,datetimelog,ip,ket) values ('".$username."','Login ".$username."','Sukses','',now(),'$hh','$ha')");

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

        return Core::setResponse("success", $output);

    }

    public function menu(Request $request)
    {
        $roles       = $request->roles;
        $menu_status = $request->menu_status;
        $level_menu  = $request->level_menu;
        $parent_menu = $request->parent_menu;

        if ($roles == ''){
            return Core::setResponse('error', ['roles' => "roles tidak boleh kosong"]);
        }
        if ($menu_status == ''){
            return Core::setResponse('error', ['menu_status' => "Parameter tidak boleh kosong. Pilihan: judul_menu, sub_menu"]);
        }

        $optionroles = "and status_menu = 'active' and (authorized_roles is null or authorized_roles ='' or authorized_roles LIKE '%".$roles."%')";

        if ($menu_status == 'judul_menu') {
            $query = DB::connection("mysql")->select("SELECT * from boopati_menu where level_menu=0 $optionroles order by order_menu");

            if (count($query) == 0) {
                return Core::setResponse('not_found', ['result' => "Level menu 0 kosong"]);
            }

        } else {
            if ($level_menu == ''){
                return Core::setResponse('error', ['level_menu' => "Parameter level menu tidak boleh kosong. Pilihan 1 s/d 5"]);
            }
            if ($parent_menu == ''){
                return Core::setResponse('error', ['parent_menu' => "Parameter parent menu tidak boleh kosong."]);
            }

            $query = DB::connection("mysql")->select("SELECT * from boopati_menu where level_menu=$level_menu and parent_menu=$parent_menu $optionroles order by order_menu");
        }

        return Core::setResponse("success", $query);

    }

    public function tableRegion(Request $request)
    {
        $keyword_page = $request->keyword_page;

        if ($keyword_page == ''){
            return Core::setResponse('error', ['keyword_page' => "keyword page tidak boleh kosong"]);
        }

        $row = DB::connection("mysql")->select("SELECT * FROM boopati_table_mapped_by_region WHERE keyword_page = '$keyword_page'");

        if (count($row) == 0) {
            return Core::setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return Core::setResponse("success", $row);
    }

    public function columnName(Request $request)
    {
        $table_obc_msisdn = $request->table_obc_msisdn;

        if ($table_obc_msisdn == ''){
            return Core::setResponse('error', ['table_obc_msisdn' => "table_obc_msisdn tidak boleh kosong"]);
        }

        $row = DB::connection("mysql")->select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_obc_msisdn'");

        if (count($row) == 0) {
            return Core::setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        return Core::setResponse("success", $row);
    }

    public function hitungRetrieve(Request $request)
    {
        $id_branch          = $request->id_branch;
        $id_users           = $request->id_users;
        $table_obc_msisdn   = $request->table_obc_msisdn;
        $aksi               = $request->aksi;
        $username           = $request->username;

        if ($id_branch == ''){
            return Core::setResponse('error', ['id_branch' => "id_branch tidak boleh kosong"]);
        }
        if ($table_obc_msisdn == ''){
            return Core::setResponse('error', ['table_obc_msisdn' => "table_obc_msisdn tidak boleh kosong."]);
        }

        $row = DB::connection("mysql")->select("SELECT * FROM cluster WHERE id_branch = '$id_branch'");

        if (count($row) == 0) {
            return Core::setResponse('not_found', ['result' => "Data tidak ada"]);
        }

        $l_mapping = array();
        foreach($row as $key => $row){
            $cl = $row->cluster_name;
            $l_mapping[] = "'$cl'";
        }

        $mapping = implode(',', $l_mapping);

        $row_blm_retr = DB::connection("mysql")->select("SELECT COUNT(*) AS count_msisdn FROM $table_obc_msisdn WHERE cluster_lacci IN($mapping) AND temp_user IS NULL AND remark_claim IS NULL");

        $row_sdh_retr = DB::connection("mysql")->select("SELECT COUNT(*) AS count_msisdn FROM $table_obc_msisdn WHERE cluster_lacci IN($mapping) AND temp_user = '$id_users' AND remark_claim IS NULL");

        if ($aksi == 'generate_retrive') {
            $tanggal        = date("Y-m-d");

            $kondisi = "AND cluster_lacci IN($mapping) LIMIT 50000";

            // insert history
            $query_history  = DB::connection("mysql")->select("INSERT into boopati_history(username,activity,status,errorcode,datetimelog) values ('".$username."','Retrieve Data for ".$id_users." OBC','Begin','',now())");

            // update wl
            $j = 0;
			$query2 = DB::connection("mysql")->select("SELECT * FROM $table_obc_msisdn WHERE temp_user IS NULL $kondisi");

			foreach($query2 as $key => $query3){
				$msisdn         = $query3->msisdn;
				$query_update   = DB::connection("mysql")->select("UPDATE ".$table_obc_msisdn." set temp_user = '".$id_users."', temp_date='".$tanggal."' where msisdn='".$msisdn."'");
                $j++;
            }

            $query_history = DB::connection("mysql")->select("INSERT into boopati_history(username,activity,status,errorcode,datetimelog) values ('".$username."','Retrieve Data with ".$j." attempt OBC','Sukses','',now())");

            return Core::setResponse("success", ['result' => "WL Berhasil di retrive"]);
        }

        return Core::setResponse("success", ['belum_retrive' => $row_blm_retr, 'sudah_retrive' => $row_sdh_retr]);
    }

    public function ajaxMsisdn(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','2048M');

        $type               = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->type,ENT_QUOTES));
        $table_obc_msisdn   = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->table_obc_msisdn,ENT_QUOTES));
        $id_users           = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->id_users,ENT_QUOTES));
        $flag               = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->flag,ENT_QUOTES));
        $status_claim       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->status_claim,ENT_QUOTES));
        $region             = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->region,ENT_QUOTES));
        $column             = $request->column;
        $id_branch          = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->id_branch,ENT_QUOTES));
        $start_date         = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->start_date,ENT_QUOTES));
        $end_date           = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->end_date,ENT_QUOTES));

        $output = array();

        switch ($type) {

            case "achievement":
                $query = DB::connection("mysql")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan FROM boopati_whitelist_claim a WHERE status_claim in (".$status_claim.") and a.flag = '".$flag."' and a.id_users = '".$id_users."'");

                break;

            case "non":

                if ($flag == 'family_plan'){
                    $field = "regional_channel as region, cluster_sales as cluster, kabupaten, kecamatan, msisdn, brand, temp_date";
                } else if ($flag == 'mini_grapari'){
                    $field = "grapari, status as status, jarak, msisdn, temp_date";
                } else {
                    $field = "region, cluster_lacci as cluster, kabupaten, kecamatan, msisdn, brand, temp_date";
                }

                $query_limit = DB::connection("mysql")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," .htmlspecialchars($params['length'],ENT_QUOTES) . "
                ");

                $query = DB::connection("mysql")->select("SELECT ".$field." FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."' and REMARK_CLAIM is null");
                break;

            case "non-combomax":

                $field = "region, cluster_lacci as cluster, kabupaten, kecamatan, msisdn, brand, temp_date";

                $query_limit = DB::connection("mysql")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " .htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                ");

                $query = DB::connection("mysql")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "non_wacluster":

                $field = implode(',', $column);
                // $field = "region_lacci as region, cluster_lacci as cluster, kecamatan, msisdn, segment_id, campaign_channel, keterangan";

                $query_limit = DB::connection("mysql")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " . mysqli_real_escape_string($database->connection, htmlspecialchars($params['start'],ENT_QUOTES)) . " ," . mysqli_real_escape_string($database->connection, htmlspecialchars($params['length'],ENT_QUOTES)) . "
                ");

                $query = DB::connection("mysql")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "uploaded_wacluster":

                $month     = date('m');

                $q_mapping = DB::connection("mysql")->select("SELECT DISTINCT(c.id_cluster), u.id_users FROM tdc t
                    JOIN cluster c ON c.id_cluster = t.id_cluster
                    JOIN users u ON u.id_cluster = c.id_cluster
                    WHERE c.id_branch = '$id_branch' AND u.roles = 'branch_cluster'
                    ORDER BY c.id_cluster");


                $l_mapping = array();
                foreach($rl_mapping as $key => $row){
                    $l_mapping[] = "'$row->id_users'";
                }
                $mapping = implode(',', $l_mapping);

                $query = DB::connection("mysql")->select("SELECT msisdn, status_claim, tanggal_claim FROM boopati_whitelist_claim
                    WHERE flag = '$flag' AND MONTH(tanggal_claim) = '$month' AND id_users IN ($mapping)");

                break;

            case "non_wabranch":

                $field = "cluster_lacci, msisdn, service, flag";

                $query_limit = DB::connection("mysql")->select("
                    select ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," .htmlspecialchars($params['length'],ENT_QUOTES) . "
                ");

                $query = DB::connection("mysql")->select("SELECT ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and (remark_claim IN ('') OR remark_claim IS NULL)
                ");
                break;

            case "achievement-gramin":

                $query_limit = DB::connection("mysql")->select("SELECT *
                    FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                ");

                $query = DB::connection("mysql")->select("SELECT *
                    FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "achievement-hvc-prior":
                $query_limit = DB::connection("mysql")->select("SELECT *
                    FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                ");

                $query = DB::connection("mysql")->select("SELECT *
                    FROM ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;

            case "achievement-wacluster":

                $query = DB::connection("mysql")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                    FROM boopati_whitelist_claim a
                    WHERE a.flag = '".$flag."' and a.id_users = '".$id_users."'
                ");
                break;

            case "achievement-list-wacluster":

                $query = DB::connection("mysql")->select("SELECT '' AS `no`, NOW() AS `date_update`, b.header_page AS `campaign`, u.username AS `cluster`, a.msisdn, a.status_claim, a.tanggal_claim
                    FROM boopati_whitelist_claim a
                    LEFT JOIN boopati_table_mapped_by_region b ON b.flag_page = a.flag
                    LEFT JOIN users_tdc u ON u.id_users = a.id_users
                    WHERE flag = '$flag' AND a.id_users = '$id_users'
                    AND (tanggal_claim BETWEEN '$start_date' AND '$end_date')");
                break;

            case "achievement-wabranch":

                $query = DB::connection("mysql")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                    FROM boopati_whitelist_claim a
                    WHERE a.flag = '".$flag."' and a.id_users = '".$id_users."'
                ");
                break;

            case "non-achievement-giganet":
                $field = "cluster_lacci AS cluster, segment_id, msisdn";

                $query = DB::connection("mysql")->select("SELECT $field
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

                $query_limit = DB::connection("mysql")->select("
                    select ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                    limit " . htmlspecialchars($params['start'],ENT_QUOTES) . " ," . htmlspecialchars($params['length'],ENT_QUOTES) . "
                ");

                $query = DB::connection("mysql")->select("
                    select ".$field."
                    from ".$table_obc_msisdn."
                    where temp_user='".$id_users."'
                    and REMARK_CLAIM is null
                ");
                break;
            case "achievement-wambjj":

                $query = DB::connection("mysql")->select("SELECT msisdn, brand, status_claim, tanggal_claim, datetime_claim, keterangan
                    FROM boopati_whitelist_claim a
                    WHERE a.flag = '".$flag."' and a.id_users = '".$id_users."' /* and table_name = '".$table_obc_msisdn."'
                ");
                break;

            default:
                return Core::setResponse("not_found", ['message' => "Type tidak ditemukan"]);

        }

        // $sql = mysqli_query($database->connection, htmlspecialchars($query));

        $totalRecords  = count($query);

        $counter = 0;
        $no      = 0;

        while($res = $query){
            $no++;
            $result[$counter] = $res;
            $result[$counter]->no = $no;
            $counter++;
        }

        $json_data = array(
            "draw"            => intval( htmlspecialchars($params['draw']) ),
            "recordsTotal"    => intval( $totalRecords  ),
            "recordsFiltered" => intval($totalRecords ),
            "data"            => $result   // total data array
            );

        $output['data'] = $result;

        return Core::setResponse("success", $output);
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

        $query = DB::connection("mysql")->select("INSERT into boopati_whitelist_claim (id_users,msisdn,status_claim,keterangan,tanggal_claim,datetime_claim, flag, table_name, brand, datetime_open_form) values ('".$id_users."','".$msisdnkirim."','".$hasil."','".$catatan."',now(),now(), '".$flag."', '".$table_obc_msisdn."', '".$brand."', '".$jamklik."')");

        $query2 = DB::connection("mysql")->select("UPDATE ".$table_obc_msisdn." set remark_claim='".$id_users."', status_telepon='".$hasil."' where msisdn='".$msisdnkirim."' AND temp_user = '$id_users'");

        return Core::setResponse("success", ['query' => count($query), 'query2' => count($query2)]);
    }

    public function updateListMsisdn(Request $request)
    {
        $id_users       = $request->id_users;
        $msisdnkirim    = $request->msisdnkirim;
        $hasil          = $request->hasil;
        $table_obc_msisdn = $request->table_obc_msisdn;
        $jamklik        = $request->jamklik;

        $query = DB::connection("mysql")->select("UPDATE ".$table_obc_msisdn." set remark_claim='".$id_users."', status_telepon='".$hasil."' where msisdn='".$msisdnkirim."' AND temp_user = '$id_users'");

        $query2 = DB::connection("mysql")->select("UPDATE boopati_whitelist_claim set status_claim='".$hasil."',keterangan = '".$catatan."',tanggal_claim = now(), datetime_claim=now(), datetime_open_form = '".$jamklik."' where msisdn='".$msisdnkirim."' and id_users='".$id_users."'");

        return Core::setResponse("success", ['query' => count($query), 'query2' => count($query2)]);

    }
}
