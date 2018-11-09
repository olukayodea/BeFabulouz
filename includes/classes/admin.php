<?php
    class admin extends common {
        function authenticate($username) {
            global $db;
            try {
                $sql = $db->prepare("SELECT  `admin_id` FROM `admin` WHERE `username`=:username AND is_api = 1");
                $sql->execute(
                    array(':username' => $username)
                );
            } catch(PDOException $ex) {
                echo "An Error occured! ".$ex->getMessage(); 
            }
            if ($sql) {
                if ($sql->rowCount() == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
?>