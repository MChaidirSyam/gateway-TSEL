<?php
require_once "config.php";
require_once "EnvayaSMS.php";
require_once "../mainconfig.php";
$request = EnvayaSMS::get_request();
header("Content-Type: {$request->get_response_type()}");
if (!$request->is_validated($PASSWORD))
{
    header("HTTP/1.1 403 Forbidden");
    error_log("Invalid password");    
    echo $request->render_error_response("Invalid password");
    return;
}
$action = $request->get_action();
switch ($action->type)
{
    case EnvayaSMS::ACTION_INCOMING:    
        
        // Send an auto-reply for each incoming message.
    
        $type = strtoupper($action->message_type);
        $isi_pesan = $action->message;
        $file = fopen("test.txt","w");
echo fwrite($file,$isi_pesan);
fclose($file);
     if($action->from == '168' AND preg_match("/Anda menerima Pulsa dari/i", $isi_pesan)) {
         $pesan_isi = $action->message;
         $insert_order = mysqli_query($db, "INSERT INTO pesan_tsel (isi, status, date) VALUES ('$pesan_isi', 'UNREAD', '$date')");
         $check_history_topup = mysqli_query($db, "SELECT * FROM deposits WHERE payment = 'pulsa' AND type = 'auto' AND status = 'Pending' AND method_name = 'Pulsa XL-1433'");
         if (mysqli_num_rows($check_history_topup) == 0) {
                error_log("History TopUp Not Found .");
         } else {
             while($data_history_topup = mysqli_fetch_assoc($check_history_topup)) {
                        $id_history = $data_history_topup['id'];
                        $no_pegirim = $data_history_topup['phone'];
                        $username_user = $data_history_topup['user_id'];
                        $amount = $data_history_topup['amount'];
                        
                        $format = $data_history_topup['created_at'];
                        $pisah = explode(" ", $format);
                        $date_transfer = $pisah[0];
                        
                        $date_type = $data_history_topup['type'];
                        $jumlah_transfer = $data_history_topup['post_amount'];
                        
                        $cekpesan = preg_match("/Anda menerima Pulsa dari $sender sebesar Rp$jumlah_transfer/i", $isi_pesan);
                        if($cekpesan == true) {
                           
                           
                            $update_history_topup = mysqli_query($db, "UPDATE deposits SET status = 'Success' WHERE id = '$id_history'");
                            
                            
                            $update_history_topup = mysqli_query($db, "INSERT INTO balance_logs (user_id, type, amount, note, created_at) VALUES ('".$username_user."', 'plus', '".$amount."', 'Deposit saldo otomatis. ID Deposit: ".$id_history."', '".date('Y-m-d H:i:s')."')");
                           
                                
                           
                            if($update_history_topup == TRUE) {
                                error_log("User ID $username_user Telah Ditambahkan Sebesar $amount");
                                $update_history_topup = mysqli_query($db, "UPDATE users SET balance = balance+$amount WHERE id = '$username_user'");
                            } else {
                                error_log("System Error");
                            }
                        } else {
                            error_log("data Transfer Pulsa Tidak Ada");
                        }
                }
         }
     } else {
        error_log("Received $type from {$action->from}");
        error_log(" message: {$action->message}");
     }                     
        
        return;
}