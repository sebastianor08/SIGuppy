<?php

    include_once '../lib/conf/connection.php';

    class MasterModel extends Connection{
        public function insert($sql){
            $result = mysqli_query($this->getConnect(),$sql);

            return $result;
        }

        public function select($sql){
            $result = mysqli_query($this->getConnect(),$sql);

            return $result;
        }

        public function update($sql){
            $result = mysqli_query($this->getConnect(),$sql);

            return $result;
        }

        public function delete($sql){
            $result = mysqli_query($this->getConnect(),$sql);

            return $result;
        }

    }

?>