<?php
require_once("classes.php");
require_once("business.php");
$url = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];
$url = explode("/",$url);
session_start();

if($url[1]==""){
    showSinglePage();

}else if($url[1]=="login"){
    if($method == "POST"){
        $username = filter_input(INPUT_POST,"username");
        $password = filter_input(INPUT_POST,"password");
        $res = Checklogin($username,$password);
        if(!$res==1){
            $errorMessage = "Incorrect username or password.";
            showLogin($errorMessage);
            die(0);
        }
        session_regenerate_id(true);
        $uesrObjectResult = fetchSingleUserInfo($username);
        $_SESSION['username']= $uesrObjectResult->Username;
        $_SESSION['Role']= $uesrObjectResult->Role;
        $_SESSION['Userid']= $uesrObjectResult->Userid;
        if($_SESSION['Role']==0){
            print_r("Working");
            header("Location:/admin");
            die(0);
        }elseif($_SESSION['Role']==2){
            print_r("Student!!!");
            die(0);
        }

    }elseif($method=="GET"){
        session_destroy();
        showLogin(null);
        die(0);

    }


}elseif($url[1]=="admin"){
    if(!isset($_SESSION["username"])){
        session_destroy();
        header("Location:/login");
        
    }else{
        if($_SESSION["Role"]!="0"){
            session_destroy();
            header("Location:/login");
        }else{
            showAdminPage();
            die(0);
        }
    }
}elseif($url[1]=="logout"){
    session_destroy();
    header("Location:/login");
    // Below method to add student 
} elseif ($url[1] == "add_student") {
    // Check if the user is logged in and has the appropriate role
    if (!isset($_SESSION["username"]) || $_SESSION["Role"] != 0) {
        // Redirect unauthorized users to the admin panel
        header("Location: /admin");
        die();
    }
    $successMessage = "";

    // Process the "add_student" request
    if ($method == "POST") {
        $username = filter_input(INPUT_POST,"username");
        $password = filter_input(INPUT_POST,"password");
        $email = filter_input(INPUT_POST,"email");
        $role = filter_input(INPUT_POST,"role");
        $first_name = filter_input(INPUT_POST,"firstName");
        $last_name = filter_input(INPUT_POST,"lastName");
        $nationality = filter_input(INPUT_POST,"nationality");
        $studentObject = new Student;
        $userObject = new User;
        $userObject->Username=$username;
        $userObject->PasswordHash=hash('sha256', $password);
        $userObject->Email=$email;
        $userObject->Role=$role;
        $studentObject->firstName=$first_name;
        $studentObject->lastName=$last_name;
        $studentObject->nationality=$nationality;
        $studentObject->registrationdate = date("Y-m-d");
        createStundet($studentObject,$userObject);
        die();
    } elseif ($method == "GET") {
        // Handle GET requests
        http_response_code(405); // Method Not Allowed
        echo "GET Method not allowed";
        die();
    }

    // this method is for testing purpose
}elseif ($url[1] == "AllUsers") {

     if(!isset($_SESSION["username"])){
         session_destroy();
         header("Location:/login");
         die(0);
        
     }else{
         if($_SESSION["Role"]!="0"){
             session_destroy();
             print("Not allowed For reguler Users");
             die(0);
         }else{
             fetchAllUsersInfo();
             die(0);
         }
     }
}
