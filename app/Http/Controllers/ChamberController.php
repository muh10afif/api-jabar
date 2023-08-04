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
            return Core::setResponse('error', ['username' => "username tidak boleh kosong"],FALSE, 400);
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
            return Core::setResponse('error', ['username' => "username tidak ditemukan"],FALSE, 404);
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

        return Core::setResponse("success", $output, FALSE, 200);

    }
}
