<?php
require_once("classes.php");

function dabs(){
    $db = new PDO('mysql:host=localhost;dbname=education','root','');
    $db ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    return $db;
}
function Checklogin($username , $password){
    $db = dabs();
    $x = new User;
    $hashedPassword = hash('sha256', $password);
    $getCredintails = $db ->prepare(" select count(*) from Users where Username=:user and PasswordHash=:pass ;");
    $getCredintails->bindParam("user",$username);
    $getCredintails->bindParam(":pass",$hashedPassword);
    $getCredintails->execute();
    $res = $getCredintails->fetch(PDO::FETCH_COLUMN);
    return $res;

}

function fetchSingleUser($username){
    $db = dabs();

    $userObject = new User;
    $getUser = $db->prepare("select * from users where Username=:user");
    $getUser ->bindParam("user",$username);
    $getUser ->execute();
    $resUser = $getUser ->fetchAll(PDO::FETCH_OBJ);
    foreach($resUser as $k => $i){
    // $x->room_id = $i->room_id;
    // $x->building = $i->building;
    // $x->name = $i->name;
    $userObject->Userid=$i->UserID;
    $userObject->Username=$i->Username;
    $userObject->Role=$i->Role;
    }

    return $userObject;


}

function fetchAllUsers(){
    $db = dabs();

    $getAllUsers=$db->prepare("select * from Users");
    $getAllUsers->execute();
    $resultAllUsers =$getAllUsers->fetchAll(PDO::FETCH_OBJ);
    $arrayOfUsers=[];
    foreach($resultAllUsers as $k =>$i){
        $userObject = New User;
        $userObject->Userid=$i->UserID;
        $userObject->Username=$i->Username;
        $userObject->Email=$i->Email;
        $userObject->Role=$i->Role;
        array_push($arrayOfUsers,$userObject);
    }

    return $arrayOfUsers;

}

function createNewStudent(Student $student , User $user): bool{
    $flag = false;
    try{
        $db = dabs();
        $addUser = $db->prepare("INSERT INTO users (Username, PasswordHash, Email, Role) VALUES (:username, :password, :email, :role)");
        $addUser->bindParam(':username', $user->Username);
        $addUser->bindParam(':password', $user->PasswordHash);
        $addUser->bindParam(':email', $user->Email);
        $addUser->bindParam(':role', $user->Role);
        $addUser->execute();
        $userid = $db->lastInsertId();
        $addStudent = $db->prepare("INSERT INTO Students (UserID, FirstName, LastName, Nationality, RegistrationDate) VALUES (:userid, :firstName, :lastName, :nationality, :registrationDate)");
        $addStudent->bindParam(':userid', $userid);
        $addStudent->bindParam(':firstName', $student->firstName);
        $addStudent->bindParam(':lastName', $student->lastName);
        $addStudent->bindParam(':nationality', $student->nationality);
        $addStudent->bindParam(':registrationDate', $student->registrationdate);
        $addStudent->execute();
        $flag=true;
    
    }catch(PDOException $e){
        echo "Error: " . $e->getMessage();

    }

    return $flag;



}