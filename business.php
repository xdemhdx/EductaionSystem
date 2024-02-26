<?php

require_once("datalayer.php");
require_once("classes.php");
require_once("control.php");

function loginCheck($username , $password){
    $res = Checklogin($username , $password);
    return $res;
}


function showSinglePage(){
    $location = "main.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}

function showLogin($errorMessage){
    $location = "login.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(['errorMessage' => $errorMessage]));
}
function showAdminPage(){
    $location = "admin.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}


    // this method is for testing purpose
function showDataPage(){
    $location = "data.html";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render());
}

function fetchSingleUserInfo($username){
    $userObject = fetchSingleUser($username);
    return $userObject;
}

function fetchAllUsersInfo(){
    $result = fetchAllUsers();
    $location = "AllUsers.html.twig";
    $twig = theTwig();
    $template = $twig->load($location);
    print_r($template->render(["users"=>$result]));
}


    // this method is for testing purpose
function loadData(){
    $data = array("message" => "Data loaded successfully!");
    // Output the data as JSON
    header('Content-Type: application/json');
    echo json_encode($data);
    exit; // Exit to prevent further execution
}

function createStundet(Student $stundet , User $user){
    $res = createNewStudent($stundet,$user);
    if($res){
        echo "<div id='successMessage'>Success</div>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = '/admin';
                }, 3000);
              </script>";
    }else{
    echo "<div style'color='red' ' id='successMessage'>Not Success</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = '/admin';
            }, 3000);
          </script>";

    }
}