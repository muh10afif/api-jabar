<?php

namespace App\Http\Controllers;

use app\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            DB::connection("mysqlAnsel")->statement("DELETE FROM project WHERE id_project = ?", $id_project);
            DB::connection("mysqlAnsel")->statement("DELETE FROM configure WHERE id_project = ?", $id_project);
            DB::connection("mysqlAnsel")->statement("DELETE FROM hadiah WHERE id_project = ?", $id_project);
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
		INNER JOIN tbl_user p ON p.id_user = c.user_created");

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
            'type'      => 'required',
            'id'        => 'required'
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

        foreach($data_import as $index_import => $value_import){

            if($value_import[0]!=''){
                for($i = 0; $i <= $value_import[2]; $i++){
                    $fielda = $value_import[0];
                    $fieldb = $value_import[1];
                    $fieldc = $value_import[2];
                    $insert = DB::connection("mysqlAnsel")->select("INSERT into peserta_".$project."(fielda,fieldb,fieldc, status) values ('".$fielda."','".$fieldb."','".$fieldc."','0') ");

                    if(mysqli_query($database->connection, $insert)){
                        $data_hasil['berhasil'][] = $value_import;
                    }else{
                        $data_hasil['gagal'][] = mysqli_connect_error();
                    }
                }

            }
        }
        fclose($file);

        unlink('./file/import/'.$new_name_file);
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

    public function save_update_project(Request $request, $id)
    {
        $user       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->user_created));
        $project    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->name_project));
        $description= preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->description));
        $field_a    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->field_a));
        $field_b    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->field_b));
        $field_c    = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->field_c));
        $hadiah     = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', $request->hadiah);
        $file       = $request->file('file');
        $aksi       = preg_replace('~[\\\\/:*?!@#$%^&;:()"<>|]~', '', htmlspecialchars($request->aksi));

        $validator = Validator::make($request->all(), [
            'user_created'  => 'required',
            'name_project'  => 'required',
            'description'   => 'required',
            'field_a'       => 'required',
            'field_b'       => 'required',
            'field_c'       => 'required',
            'hadiah'        => 'required',
            'file'          => 'required'
        ]);

        if ($validator->fails()) {
            return Core::setResponse("error", $validator->errors());
        }

        DB::connection("mysqlAnsel")->beginTransaction();

        try {

            if($aksi == 'add'){
                $project_query 	= DB::connection("mysqlAnsel")->select("INSERT into project(name_project, `description`, user_created, date_created) values(?, ?, ?, '".date('Y-m-d H:i:s')."')", [$project, $description, $user]);

                $project_id 	= DB::getPdo()->lastInsertId();
            } else {
                $project_id = $id;

		        $project_query 	= DB::connection("mysqlAnsel")->select("UPDATE project set name_project = ?, `description` = ?, user_created = ? where id_project = ?", [$project,$description,$user,$project_id]);
            }

            if($aksi == 'add'){
                $configure_query = DB::connection("mysqlAnsel")->select("INSERT into configure(id_project, field1, field2, field3) values(?, ?, ?, ?)", [$project_id,$field_a,$field_b,$field_c]);
            } else {
                $configure_query = DB::connection("mysqlAnsel")->select("update configure set field1 = ?, field2 = ?, field3 = ? where id_project = ?", [$field_a,$field_b,$field_c,$id_project]);
            }

            if($aksi == 'change'){
                DB::connection("mysqlAnsel")->select("DELETE from hadiah where id_project = ?", 'i', $project_id);
            }

            foreach($hadiah as $data){
                $hadiah_query = DB::connection("mysqlAnsel")->select("INSERT into hadiah(id_project, name_hadiah) values(?, ?)", [$project_id,"$data"]);
            }

            if($file != ''){

                $file_oriname   = $file->getClientOriginalName();
                $file_size      = $file->getSize();
                $fileMimeType   = $file->getClientMimeType();
                $filename       = pathinfo($file_oriname, PATHINFO_FILENAME);
                $extensi        = pathinfo($file_oriname, PATHINFO_EXTENSION);

                //file peserta
                $tanggal= date('Y-m-d');
                $waktu  = strtotime(date('H:i:s'));
                $file   = htmlspecialchars(str_replace("/", "", $file_oriname));

                $eror		= false;
                $file_type	= array('csv');
                $max_size	= 500000000; // 5MB

                //ubah nama file
                $file_name	= "import -".$tanggal."-".$waktu.".".$extensi;

                if($file->move(storage_path('file_csv'), $file_name)){

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

            return Core::setResponse("error", "Gagal diproses");

        }

    }

}
