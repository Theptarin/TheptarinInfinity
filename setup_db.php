<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of setup_db
 * เพื่อใช้ทดสอบการเชื่อมต่อฐานข้อมูล
 * @author suchart.orr@gmail.com
 */
class setup_db {

    private $conn = null;

    /**
     * 
     * @param type $dsn
     * @param type $username
     * @param type $password
     */
    public function __construct($dsn, $username, $password) {
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (Exception $ex) {
            echo "Could not connect to database : " . $ex->getMessage();
            exit();
        }
    }

    /**
     * 
     */
    public function create_table() {
        // sql to create table
        $sql = "CREATE TABLE MyGuests (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    firstname VARCHAR(30) NOT NULL,
    lastname VARCHAR(30) NOT NULL,
    email VARCHAR(50),
    reg_date TIMESTAMP
    )";
        try {
            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // use exec() because no results are returned
            $this->conn->exec($sql);
            echo "Table MyGuests created successfully";
        } catch (Exception $ex) {
            echo "Could not create table : " . $ex->getMessage();
            exit();
        }
    }

    /**
     * 
     */
    public function insert_data() {
        $sql = "INSERT INTO MyGuests (firstname, lastname, email)
    VALUES (:firstname, :lastname, :email)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array(':firstname' => 'สุชาติ', ':lastname' => 'บุญหชัยรัตน์', ':email' => 'suchart.orr@gmail.com'));
            echo 'New record id : ' . $this->conn->lastInsertId();
        } catch (Exception $ex) {
            echo "Could not create table : " . $ex->getMessage();
        }
    }

}

$dsn = 'mysql:host=localhost;dbname=orr-code';
$username = 'orr-code';
$password = '';
$my = new setup_db($dsn, $username, $password);
$my->create_table();
$my->insert_data();
