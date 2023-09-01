<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class AnselController extends Controller
{
    public function cek_login(Request $request)
    {
        $username = $request->username;

        if ($username == '') {
            return Core::setResponse("error", "Username harus terisi");
        }

        $query = DB::connection("mysqlAnsel")->select("select * from tbl_user u inner join tbl_roles r on u.id_roles = r.id_roles where username = '".$username."' and block = '0'");

        if(empty($query)){
            return Core::setResponse("not_found", "Data Empty");
        }else{
            return Core::setResponse("success", $query);
        }
    }

    public function delete_project($id)
    {
        $id_project 	= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $id);

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            DB::connection("mysqlAnsel")->statement("DELETE FROM project WHERE id_project = ?", [$id_project]);
            DB::connection("mysqlAnsel")->statement("DELETE FROM configure WHERE id_project = ?", [$id_project]);
            DB::connection("mysqlAnsel")->statement("DELETE FROM hadiah WHERE id_project = ?", [$id_project]);
            DB::connection("mysqlAnsel")->statement("DELETE FROM list_pemenang WHERE id_project = ?", [$id_project]);
            DB::connection("mysqlAnsel")->statement("DROP table if exists peserta_".htmlspecialchars($id_project));

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", "Data berhasil dihapus");
        } catch (\Throwable $th) {

            DB::connection("mysqlAnsel")->rollback();
            return Core::setResponse("error", "Data gagal dihapus");
        }

    }

    public function list_master()
    {
        $query = DB::connection("mysqlAnsel")->select("SELECT username,id_project,name_project,`description`,c.date_created
        FROM project c
		INNER JOIN tbl_user p ON p.id_user = c.user_created
        ORDER BY c.id_project DESC");

        if(empty($query)){
            return Core::setResponse("not_found", "Data Empty");
        }else{
            return Core::setResponse("success", $query);
        }
    }

    public function list_configure(Request $request)
    {
        $nama_roles = $request->input('nama_roles');
        $id         = $request->input('id');

        $validator = Validator::make($request->all(), [
            'nama_roles' => 'required',
            'id'         => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        if($nama_roles != 'administrator'){
            return Core::setResponse("error", "access Denied");
        }

        $id = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($id));

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            $data = DB::connection("mysqlAnsel")->select("SELECT project.name_project, configure.* FROM configure
            JOIN project ON project.id_project = configure.id_project
            WHERE project.id_project = ? LIMIT 1",[$id]);

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", $data);
        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }

    }

    public function list_hadiah(Request $request)
    {
        $nama_roles = $request->input('nama_roles');
        $id         = $request->input('id');

        $validator = Validator::make($request->all(), [
            'nama_roles' => 'required',
            'id'         => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        if($nama_roles != 'administrator'){
            return Core::setResponse("error", "access Denied");
        }

        $id = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($id));

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            $data = DB::connection("mysqlAnsel")->select("SELECT project.name_project, hadiah.* FROM hadiah
            JOIN project ON project.id_project = hadiah.id_project
            WHERE project.id_project = ?",[$id]);

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", $data);
        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }

    }

    public function list_peserta(Request $request)
    {
        $nama_roles = $request->input('nama_roles');
        $id         = $request->input('id');

        $validator = Validator::make($request->all(), [
            'nama_roles' => 'required',
            'id'         => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        if($nama_roles != 'administrator'){
            return Core::setResponse("error", "access Denied");
        }

        $id = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($id));

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $id = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($id));

            $configure = DB::connection("mysqlAnsel")->select("SELECT field1, field2, field3 FROM configure
                JOIN project ON project.id_project = configure.id_project
                WHERE project.id_project = ? LIMIT 1", [$id]);

            $data_peserta = DB::connection("mysqlAnsel")->select("SELECT * FROM peserta_$id");

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", ['configure' => $configure, 'data_peserta' => $data_peserta]);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }

    }

    public function project_exist(Request $request)
    {
        $nama_roles = $request->input('nama_roles');
        $project    = $request->input('project');
        $type       = $request->input('type');
        $id         = $request->input('id');

        $validator = Validator::make($request->all(), [
            'project'   => 'required',
            'type'      => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        if($nama_roles != 'administrator'){
            return Core::setResponse("error", "access Denied");
        }

        $project    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($project));
        $type       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($type));
        $id         = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($id));

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            if ($type == 'change') {
                $query = DB::connection("mysqlAnsel")->select("SELECT * FROM project WHERE name_project = ? AND id_project != ?", [$project,$id]);
            } else {
                $query = DB::connection("mysqlAnsel")->select("SELECT * FROM project WHERE name_project = ?", [$project]);
            }

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", ['project_exists' => (count($query) > 0 ? 1 : 0)]);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }

    }

    public function list_project_edit($id)
    {
        $id_project = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($id));

        if ($id_project == '') {
            return Core::setResponse("error", "Id project harus diisi");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            $project    = DB::connection("mysqlAnsel")->select("SELECT * from project where id_project = ?", [$id_project]);
            $configure  = DB::connection("mysqlAnsel")->select("SELECT * from configure where id_project = ?", [$id_project]);
            $hadiah		= DB::connection("mysqlAnsel")->select("SELECT * from hadiah where id_project = ?", [$id_project]);
            $peserta	= DB::connection("mysqlAnsel")->select("SELECT * from peserta_$id_project");

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", ['project' => $project, 'configure' => $configure, 'hadiah' => $hadiah, 'peserta' => $peserta]);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "Data gagal diproses");
        }

    }

    public function import_csv($seperator, $new_name_file, $project)
    {
        $file_loc       = storage_path("/file_csv/$new_name_file");
        $new_name_file  = htmlspecialchars(str_replace("/", "", $new_name_file));
        //$new_name_file = htmlspecialchars(str_replace("\", "", $new_name_file));

        $file = fopen($file_loc,"r");

        $data_import = array();
        $data_hasil = array();

        $data_hasil['berhasil'] = NULL;
        $data_hasil['gagal'] = NULL;
        $dept = NULL;

        while(! feof($file))
          {
          $data_import[] = fgetcsv($file,1000,$seperator);
          }
        if(!empty($data_import)){
            unset($data_import[0]);
        }

        DB::connection("mysqlAnsel")->select("DROP TABLE IF EXISTS peserta_".$project."");
        DB::connection("mysqlAnsel")->select("
            CREATE TABLE peserta_".$project." (
                id_peserta INT PRIMARY KEY AUTO_INCREMENT,
                fielda VARCHAR(255),
                fieldb VARCHAR(255),
                fieldc VARCHAR(255),
                status INT
            )
        ");

        $i = 1;
        foreach(array_filter($data_import) as $index_import => $value_import){

            if($value_import[0]!=''){
                for($i = 0; $i <= $value_import[2]; $i++){
                    $fielda = $value_import[0];
                    $fieldb = $value_import[1];
                    $fieldc = $value_import[2];
                    $insert = DB::connection("mysqlAnsel")->select("INSERT into peserta_".$project."(fielda,fieldb,fieldc, status) values ('".$fielda."','".$fieldb."','".$fieldc."','0') ");

                    if(count($insert) == 0){
                        $data_hasil['berhasil'][] = $value_import;
                    }else{
                        $data_hasil['gagal'][] = "error";
                    }
                }

            }

            $i++;
        }
        fclose($file);

        unlink($file_loc);
        return $data_hasil;
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

    public function save_update_project(Request $request, $id = null)
    {
        $input = $request->input();

        $user       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('user_created')));
        $project    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('name_project')));
        $description= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('description')));
        $field_a    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('field_a')));
        $field_b    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('field_b')));
        $field_c    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('field_c')));
        $hadiah     = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->input('hadiah'));
        $file       = $request->file('file');
        // $file       = $input['file'];
        $aksi       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('aksi')));

        $validator = Validator::make($request->all(), [
            'aksi'          => 'required',
            // 'user_created'  => 'required',
            // 'name_project'  => 'required',
            // 'description'   => 'required',
            // 'field_a'       => 'required',
            // 'field_b'       => 'required',
            // 'field_c'       => 'required',
            // 'hadiah'        => 'required',
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            if($aksi == 'add'){
                $project_query 	= DB::connection("mysqlAnsel")->select("INSERT into project(name_project, `description`, user_created, date_created) values(?, ?, ?, '".date('Y-m-d H:i:s')."')", [$project, $description, $user]);

                $project_id 	= DB::connection("mysqlAnsel")->getPdo()->lastInsertId();
            } else {
                $project_id = $id;

		        $project_query 	= DB::connection("mysqlAnsel")->select("UPDATE project set name_project = ?, `description` = ?, user_created = ? where id_project = ?", [$project,$description,$user,$project_id]);
            }

            if($aksi == 'add'){
                $configure_query = DB::connection("mysqlAnsel")->select("INSERT into configure(id_project, field1, field2, field3) values(?, ?, ?, ?)", [$project_id,$field_a,$field_b,$field_c]);
            } else {
                $configure_query = DB::connection("mysqlAnsel")->select("update configure set field1 = ?, field2 = ?, field3 = ? where id_project = ?", [$field_a,$field_b,$field_c,$project_id]);
            }

            if($aksi == 'change'){
                DB::connection("mysqlAnsel")->select("DELETE from hadiah where id_project = ?", [$project_id]);
            }

            foreach($hadiah as $data){
                $hadiah_query = DB::connection("mysqlAnsel")->select("INSERT into hadiah(id_project, name_hadiah) values(?, ?)", [$project_id,"$data"]);
            }

            if($file != ''){

                $file_oriname   = $file->getClientOriginalName();
                $file_size      = $file->getSize();
                $fileMimeType   = $file->getClientMimeType();
                // $file_oriname   = $file['name'];
                // $file_size      = $file['size'];
                $filename       = pathinfo($file_oriname, PATHINFO_FILENAME);
                $extensi        = pathinfo($file_oriname, PATHINFO_EXTENSION);

                //file peserta
                $tanggal= date('Y-m-d');
                $waktu  = strtotime(date('H:i:s'));
                // $file   = htmlspecialchars(str_replace("/", "", $file_oriname));

                $eror		= false;
                $file_type	= array('csv');
                $max_size	= 500000000; // 5MB

                //ubah nama file
                $file_name	= "import-".$tanggal."-".$waktu.".".$extensi;

                if($file->move(storage_path('file_csv'), $file_name)){
                // if(move_uploaded_file($file, storage_path("/file_csv/$file_name"))){

                    $file_loc   = storage_path("/file_csv/$file_name");
                    $delimiter  = $this->detectDelimiter($file_loc);
                    $hasil      = $this->import_csv($delimiter, $file_name, htmlspecialchars($project_id));
                }

            }

            DB::connection("mysqlAnsel")->commit();

            if ($aksi == 'add') {
                $st = "ditambahkan";
            } else {
                $st = "diubah";
            }

            return Core::setResponse("success", "Sukses $st");

        } catch (\Throwable $th) {

            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "Gagal diproses $th");

        }

    }

    public function isNumeric($numeric) {
        return preg_match("/^[0-9]+$/", $numeric);
    }

    public function list_user_dropdown($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus numeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            $query = DB::connection("mysqlAnsel")->select("SELECT id_user, username from tbl_user where id_user != '1' AND id_user != '$id'");

            DB::connection("mysqlAnsel")->commit();

            if (count($query) == 0) {
                return Core::setResponse("not_found", "Data Empty");
            }

            return Core::setResponse("success", $query);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }

    }

    public function add_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required',
            'username'  => 'required',
            'fullname'  => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $user_id  = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('user_id')));
            $username = strtolower(str_replace(' ', '', htmlspecialchars(preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('username'))))));
            $fullname = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('fullname')));
            $password = md5('admin');

            $query = DB::connection('mysqlAnsel')->select("INSERT into tbl_user (username, fullname, password, id_roles) values (?, ?, ?, '2')", [$username,$fullname,$password]);

            if(count($query) == 0){

                $user = DB::connection('mysqlAnsel')->select("SELECT id_user, username from tbl_user where id_user != '1' AND id_user != ?", [$user_id]);

                DB::connection("mysqlAnsel")->commit();
                return Core::setResponse("success", array('success' => 1, 'user' => $user));
            }


        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function list_undian($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus umeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $query = DB::connection("mysqlAnsel")->select("SELECT * FROM project WHERE user_created = '$id' order by date_created DESC");

            DB::connection("mysqlAnsel")->commit();

            if (count($query) == 0) {
                return Core::setResponse("not_found", "Data Empty");
            }

            return Core::setResponse("success", $query);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function valid_project(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_project'    => 'required',
            'id_user'       => 'required',
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $id_project = $request->id_project;
            $id_user    = $request->id_user;

            $query = DB::connection("mysqlAnsel")->select("SELECT * FROM project where id_project = ? AND user_created = ?", [$id_project,$id_user]);

            DB::connection("mysqlAnsel")->commit();

            if (count($query) == 0) {
                return Core::setResponse("not_found", "Data Empty");
            }

            return Core::setResponse("success", $query);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function list_hadiah_undi($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus umeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $query = DB::connection("mysqlAnsel")->select("SELECT * from hadiah where id_project = ?", [$id]);

            DB::connection("mysqlAnsel")->commit();

            if (count($query) == 0) {
                return Core::setResponse("not_found", "Data Empty");
            }

            return Core::setResponse("success", $query);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function angka_jumlah($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus umeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $q_pemenang = DB::connection("mysqlAnsel")->select("SELECT *
            FROM
            hadiah
            INNER JOIN list_pemenang ON list_pemenang.id_hadiah = hadiah.id_hadiah
            INNER JOIN peserta_".$id." ON peserta_".$id.".id_peserta = list_pemenang.id_peserta
            INNER JOIN project ON project.id_project = hadiah.id_project
            where hadiah.id_project = ?", [$id]);

            $q_peserta = DB::connection("mysqlAnsel")->select("SELECT 'semua' as title, count(*) as jumlah From peserta_".$id."
            UNION
            SELECT 'unik' as title, count(*) FROM (SELECT 'unik' as title, count(*) as jumlah From peserta_".$id." GROUP BY fielda) as jml");

            $q_field = DB::connection("mysqlAnsel")->select("SELECT * from configure where id_project = ?", [$id]);

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", ['pemenang' => count($q_pemenang), 'peserta' => $q_peserta, 'field' => $q_field]);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function undi_acak_peserta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'list' 			=>'required',
			'id_hadiah' 	=>'required',
			'periode' 		=>'required',
			'id_project' 	=>'required',
			'nama_roles'    =>'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        $id_hadiah  = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', "", htmlspecialchars($request->input('id_hadiah')));
        $periode    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', "", htmlspecialchars($request->input('periode')));
        $id_project = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', "", htmlspecialchars($request->input('id_project')));
        // $list       = $request->input('list');
        $nama_roles = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', "", htmlspecialchars($request->input('nama_roles')));
        $id_user    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', "", htmlspecialchars($request->input('id_user')));

        if($nama_roles != 'user'){
            return Core::setResponse("error", "access Denied");
        }

        // $id_peserta = 4;

        // $query = DB::connection("mysqlAnsel")->insert("INSERT INTO list_pemenang (id_peserta, id_hadiah, periode, id_project) VALUES (?, ?, ?, ?)", [$id_peserta, $id_hadiah, "$periode", $id_project]);

        // $query1 = DB::connection("mysqlAnsel")->update("UPDATE peserta_$id_project set `status` = '1' WHERE id_peserta = ?", [$id_peserta]);

        // return "ok";

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $q = "SELECT * from peserta_".$id_project." where `status` = '0'";
            $sql = DB::connection("mysqlAnsel")->select($q);

            if (count($sql) == 0) {
                return Core::setResponse("not_found", "Semua peserta sudah menang!");
            }

            $list = array();
            $no = 1;
            foreach ($sql as $row) {
                $list["a".$no] = array(
                    "id_peserta"    => htmlentities($row->id_peserta),
                    "fielda"        => htmlentities($row->fielda),
                    "fieldb"        => htmlentities($row->fieldb),
                    "fieldc"        => htmlentities($row->fieldc),
                    "status"        => htmlentities($row->status)
                );
                $no++;
            }

            shuffle($list);
            $hasil = array_shift($list);

            $id_peserta = $hasil['id_peserta'];

            $cek = DB::connection("mysqlAnsel")->select("SELECT * from project where user_created = '$id_user' and id_project IN ($id_project)");

            if(count($cek) != 0){

                $query = DB::connection("mysqlAnsel")->insert("INSERT INTO list_pemenang (id_peserta, id_hadiah, periode, id_project) VALUES (?, ?, ?, ?)", [$id_peserta,$id_hadiah, $periode, $id_project]);

                $query1 = DB::connection("mysqlAnsel")->update("UPDATE peserta_$id_project set `status` = '1' WHERE id_peserta = ?", [$id_peserta]);

                // return $query;

                if($query){
                    $data['status'] = 'simpan';
                    $data['pm'] = urldecode(base64_encode($hasil['fielda']));
                    // $data['pm'] = urldecode(base64_encode("0812847577"));

                    DB::connection("mysqlAnsel")->commit();

                    return Core::setResponse("success", $data);
                }else{

                    DB::connection("mysqlAnsel")->rollback();
                    return Core::setResponse("not_found", "Data Empty");
                }

            }else{
                return Core::setResponse("not_found", "Data Empty");
            }

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses! $th");
        }
    }

    public function undi_get_pemenang(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nama_roles'=> 'required',
            'project'   => 'required',
            'kategori'  => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        $pjk        = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('project')));
        $nama_roles = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('nama_roles')));
        $kategori   = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('kategori')));

        if($nama_roles != 'user'){
            return Core::setResponse("error", "access Denied");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            switch ($kategori) {
                case 'manual':
                    $query = "SELECT *
                                FROM
                                hadiah
                                INNER JOIN list_pemenang ON list_pemenang.id_hadiah = hadiah.id_hadiah
                                INNER JOIN peserta_".$pjk." ON peserta_".$pjk.".id_peserta = list_pemenang.id_peserta
                                INNER JOIN project ON project.id_project = hadiah.id_project
                                where hadiah.id_project = ?";

                    $data_pemenang = DB::connection("mysqlAnsel")->select($query, [$pjk]);

                    $output['data'] = $data_pemenang;
                    $output['jumlah_pemenang'] = count($data_pemenang);

                    $list_pemenang = '';
                    $list_pemenang .= '<table align="center">';

                    $query2 = "SELECT 'No' as no, `field1`, `field2`, `field3`, 'Hadiah' as hadiah, 'Periode' as periode from configure where id_project = ?";
                    $data_field = DB::connection("mysqlAnsel")->select($query2, [$pjk]);

                    $list_pemenang .=  '<thead>';
                    foreach ($data_field as $value) {
                        $list_pemenang .=  '<tr>';
                        $list_pemenang .=  '<td>'.htmlentities($value->no).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->field1).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->field2).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->field3).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->hadiah).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->periode).'</td>';
                        $list_pemenang .=  '</tr>';
                    }
                    $list_pemenang .=  '</thead>';
                    $no = 1;
                    foreach ($data_pemenang as $value) {
                        $list_pemenang .=  '<tr>';
                        $list_pemenang .=  '<td>'.htmlentities($no).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->fielda).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->fieldb).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->fieldc).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->name_hadiah).'</td>';
                        $list_pemenang .=  '<td>'.htmlentities($value->periode).'</td>';
                        $list_pemenang .=  '</tr>';
                        $no++;
                    }
                    $list_pemenang .= '</table>';
                    $output['list_pemenang'] = $list_pemenang;
                    break;

                case 'list-peserta':
                    $query = "SELECT count(*) as jumlah FROM
                                (SELECT *
                                FROM
                                peserta_".$pjk."
                                where status = '0'
                                group by fielda) as tbl_all";
                    $data_peserta = DB::connection("mysqlAnsel")->select($query);
                    $data_pesertaa = $data_peserta[0];
                    $output['jumlah'] = intval($data_pesertaa->jumlah);

                    $q = "SELECT * from peserta_".$pjk." where `status` = '0'";
                    $sql = DB::connection("mysqlAnsel")->select($q);

                    $data = array();
                    $no = 1;
                    foreach ($sql as $row) {
                        $data[htmlentities("a".$no)] = array(
                            "id_peserta"    => htmlentities($row->id_peserta),
                            "fielda"        => htmlentities($row->fielda),
                            "fieldb"        => htmlentities($row->fieldb),
                            "fieldc"        => htmlentities($row->fieldc),
                            "status"        => htmlentities($row->status)
                        );
                        $no++;
                    }
                    $output['data']=$data;
                    break;
                default:
                    return Core::setResponse("not_found", "Kategori tidak ditemukan");
                    break;
            }

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", $output);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses! $th");
        }
    }

    public function undi_get_peserta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project'       => 'required',
            'nama_roles'    => 'required',
            'id_user'       => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        $pjk        = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('project')));
        $nama_roles = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('nama_roles')));
        $id_user    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->input('id_user')));

        if($nama_roles != 'user'){
            return Core::setResponse("error", "access Denied");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $que = "SELECT * FROM project WHERE id_project = ? AND user_created = ?";
            $rsl = DB::connection("mysqlAnsel")->select($que, [$pjk,$id_user]);

            if (count($rsl) == 1) {

                $q = "SELECT * from peserta_".$pjk." where `status` = '0'";
                $sql = DB::connection("mysqlAnsel")->select($q);

                $data = array();
                $no = 1;
                foreach ($sql as $row) {
                    $data["a".$no] = array(
                        "id_peserta"    => htmlentities($row->id_peserta),
                        "fielda"        => htmlentities($row->fielda),
                        "fieldb"        => htmlentities($row->fieldb),
                        "fieldc"        => htmlentities($row->fieldc),
                        "status"        => htmlentities($row->status)
                    );
                    $no++;
                }

                DB::connection("mysqlAnsel")->commit();

                return Core::setResponse("success", $data);

            } else{
                return Core::setResponse("not_found", "Project Not Found");
            }


        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function pemenang_delete_all($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus umeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $del = DB::connection("mysqlAnsel")->table('list_pemenang')->where('id_project','=',$id)->delete();

            if($del){
                DB::connection("mysqlAnsel")->select("UPDATE peserta_".$id." SET status = '0'");

                DB::connection("mysqlAnsel")->commit();

                return Core::setResponse("success", "Berhasil dihapus");
            }else{
                return Core::setResponse("error", "gagal dihapus!");
            }

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function pemenang_delete_satu(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_list_pemenang'=> 'required',
            'id_peserta'      => 'required',
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            $id_list_pemenang   = $request->input('id_list_pemenang');
            $id_peserta         = htmlspecialchars(urldecode(base64_decode($request->input('id_peserta'))));

            $cek = DB::connection("mysqlAnsel")->select("select * from list_pemenang where id_list_pemenang = ? AND id_peserta = ? LIMIT 1", [$id_list_pemenang,$id_peserta]);

			$row   = $cek[0];

            if ($row->id_project == $id) {

                $del = DB::connection("mysqlAnsel")->table('list_pemenang')->where('id_list_pemenang','=',$id_list_pemenang)->delete();

                if ($del) {
                    DB::connection("mysqlAnsel")->select("UPDATE peserta_".$id." SET status = '0' where id_peserta = ?", [$id_peserta]);

                    DB::connection("mysqlAnsel")->commit();
                    return Core::setResponse("success", "Berhasil dihapus");
                } else {
                    DB::connection("mysqlAnsel")->rollback();
                    return Core::setResponse("error", "gagal dihapus!");
                }

            } else {
                return Core::setResponse("error", "denied!");
            }


        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }
    }

    public function field_list_pemenang($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus numeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            // Field Pemenang
            $query = DB::connection("mysqlAnsel")->select("SELECT `field1`,`field2`, `field3` from configure where id_project = '$id'");

            $field1 = (!empty(htmlentities($query[0]->field1))) ? htmlentities($query[0]->field1) : '';
			$field2 = (!empty(htmlentities($query[0]->field2))) ? htmlentities($query[0]->field2) : '';
			$field3 = (!empty(htmlentities($query[0]->field3))) ? htmlentities($query[0]->field3) : '';

            // List Pemenang
			$data = DB::connection("mysqlAnsel")->select("SELECT *
            FROM hadiah
            INNER JOIN list_pemenang ON list_pemenang.id_hadiah = hadiah.id_hadiah
            INNER JOIN peserta_".$id." ON peserta_".$id.".id_peserta = list_pemenang.id_peserta
            INNER JOIN project ON project.id_project = hadiah.id_project
            where list_pemenang.id_project = '".$id."'
            ORDER BY hadiah.id_hadiah");

            // List Peserta
		    $ps = DB::connection("mysqlAnsel")->select("SELECT * FROM peserta_".$id." ORDER BY id_peserta");

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", ['field' => [$field1, $field2, $field3], 'pemenang' => $data, 'peserta' => $ps]);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }

    }

    public function export_pemenang($id)
    {
        if (!$this->isNumeric($id)) {
            return Core::setResponse("error", "Parameter ID harus terisi atau harus numeric");
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {
            $query = DB::connection("mysqlAnsel")->select("SELECT 'No' as no, `field1`, `field2`, `field3`, 'Hadiah' as hadiah, 'Periode' as periode, project.name_project from configure inner join project ON project.id_project = configure.id_project where project.id_project = '".$id."'");

            $data_field = $query[0];

            $output[] = array();
            $output[0]['judul'] = 'List Pemenang "'.$data_field->name_project.'"';
            $output[1]['no'] = strtoupper($data_field->no);
            if(!empty($data_field->field1)) $output[1]['fielda'] = strtoupper($data_field->field1);
            if(!empty($data_field->field2)) $output[1]['fieldb'] = strtoupper($data_field->field2);
            if(!empty($data_field->field3)) $output[1]['fieldc'] = strtoupper($data_field->field3);
            $output[1]['hadiah'] = strtoupper($data_field->hadiah);
            $output[1]['periode'] = strtoupper($data_field->periode);

            $check_project = DB::connection("mysqlAnsel")->select("SELECT *
                            FROM hadiah
                            INNER JOIN list_pemenang ON list_pemenang.id_hadiah = hadiah.id_hadiah
                            INNER JOIN peserta_".$id." ON peserta_".$id.".id_peserta = list_pemenang.id_peserta
                            INNER JOIN project ON project.id_project = hadiah.id_project
                            where hadiah.id_project = '".$id."'");
            $no = 2;
            foreach ($check_project as $value) {
                $output[$no]['no'] = $no-1;
                if(!empty($data_field->field1)) $output[$no]['fielda'] = $value->fielda;
                if(!empty($data_field->field2)) $output[$no]['fieldb'] = $value->fieldb;
                if(!empty($data_field->field3)) $output[$no]['fieldc'] = $value->fieldc;
                $output[$no]['hadiah'] = $value->name_hadiah;
                $output[$no]['periode'] = $value->periode;
                $no++;
            }

            DB::connection("mysqlAnsel")->commit();

            return Core::setResponse("success", $output);

        } catch (\Throwable $th) {
            DB::connection("mysqlAnsel")->rollback();

            return Core::setResponse("error", "gagal diproses!");
        }


    }

}
