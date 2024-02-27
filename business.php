<?php

require_once("datalayer.php");
require_once("classes.php");
require_once("control.php");

//checking login information from login requst 
function loginCheck($username , $password){
    $res = Checklogin($username , $password);
    return $res;
}

// rendering main page for the website e.g root screen
function showSinglePage(){
    $location = "main.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}
// rendering a route request for login , and $error message to check if login creditnals is correct or not 
function showLogin($errorMessage){
    $location = "login.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(['errorMessage' => $errorMessage]));
}

// once login success for the admin users , it will render an admin dashboard page
function showAdminPage(){
    $location = "admin.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}


    // this method is for testing purpose -> ignore it ill remove it later
function showDataPage(){
    $location = "data.html";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}
// to fethc One single user information and store it in session to save the state of the user between the pages
function fetchSingleUserInfo($username){
    $userObject = fetchSingleUser($username);
    return $userObject;
}
// an Admin requests all USers to get all the information of the users table in the database;
function fetchAllUsersInfo(){
    $result = fetchAllUsers();
    $location = "AllUsers.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["users"=>$result]));
}


    // this method is for testing purpose -> ill remove it later
function loadData(){
    $data = array("message" => "Data loaded successfully!");
    // Output the data as JSON
    header('Content-Type: application/json');
    echo json_encode($data);
    exit; // Exit to prevent further execution
}
// this to create a new student and user this method revices 2 Objects as it showing go to datalayer to get an insigt what is happening
function createStundet(Student $stundet , User $user){
    $res = createNewStudent($stundet,$user);

    // once its success it will redirect the admin to his dashboard after 3 seconds 
    if($res){
        echo "<div id='successMessage'>Success</div>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = '/admin';
                }, 3000);
              </script>";
    }else{
         // once its NOT success  it will redirect the admin to his dashboard after 3 seconds 
    echo "<div style'color='red' ' id='successMessage'>Not Success</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
          </script>";

    }
}